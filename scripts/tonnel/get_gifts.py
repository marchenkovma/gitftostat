import json
import time
from typing import List, Dict, Set, Optional
from curl_cffi import requests
from config.database import Database
from config.logger import logger
import random

class TonnelGiftFetcher:
    """
    Класс для получения подарков с API Tonnel
    """
    def __init__(self, test_mode: bool = False, max_pages: Optional[int] = None):
        """
        Инициализация класса
        :param test_mode: режим тестирования (True/False)
        :param max_pages: максимальное количество страниц для обработки
        """
        # URL API для получения подарков
        self.base_url = 'https://gifts3.tonnel.network/api'
        
        # Создаем подключение к базе данных
        self.db = Database()
        
        # Множество для хранения уже обработанных подарков
        # Используем множество, так как оно быстрее списка для проверки наличия элементов
        self.processed_gifts: Set[str] = set()
        
        # Сохраняем параметры
        self.test_mode = test_mode
        self.max_pages = max_pages
        
        # Запоминаем время начала работы
        self.start_time = time.time()
        
        # Настройки для запросов
        self.timeout = 60  # таймаут в секундах
        self.max_retries = 10  # максимальное количество попыток
        self.retry_delay = 5  # начальная задержка между попытками
        
        # Выводим сообщение о начале работы
        self._log("Starting Tonnel API client")

    def _log(self, message: str, level: str = "info") -> None:
        """
        Метод для вывода сообщений в консоль и запись в лог
        :param message: текст сообщения
        :param level: уровень сообщения (info/warning/error)
        """
        if level == "error":
            logger.error(message)
        elif level == "warning":
            logger.warning(message)
        else:
            logger.info(message)

    def fetch_page(self, page: int, limit: int = 30) -> List[Dict]:
        """
        Получение одной страницы подарков с API
        :param page: номер страницы
        :param limit: количество подарков на странице
        :return: список подарков
        """
        # Данные для отправки в API
        json_data = {
            'page': page,
            'limit': limit,
            'sort': '',
            'filter': '',
            'price_range': None,
            'user_auth': ''
        }

        current_retry = 0  # текущая попытка

        # Пробуем получить данные, пока не достигнем максимального числа попыток
        while current_retry < self.max_retries:
            try:
                # Добавляем случайную задержку перед запросом
                delay = 2 + (current_retry * 0.5)  # увеличиваем задержку с каждой попыткой
                time.sleep(delay)
                
                self._log(f"Fetching page {page}, attempt {current_retry + 1}/{self.max_retries}")
                
                # Отправляем запрос к API
                response = requests.post(
                    f'{self.base_url}/pageGifts',
                    json=json_data,
                    impersonate="chrome",
                    timeout=self.timeout
                )

                # Если получили успешный ответ
                if response.status_code == 200:
                    return response.json()
                
                # Если превысили лимит запросов
                elif response.status_code == 429:
                    wait_time = self.retry_delay * (current_retry + 1)
                    self._log(f"Rate limit exceeded. Waiting {wait_time} seconds...", "warning")
                    time.sleep(wait_time)
                    current_retry += 1
                
                # Если сервер недоступен
                elif response.status_code == 502:
                    wait_time = self.retry_delay * (current_retry + 1)
                    self._log(f"Server unavailable (502). Waiting {wait_time} seconds...", "warning")
                    time.sleep(wait_time)
                    current_retry += 1
                
                # Если произошла другая ошибка
                else:
                    wait_time = self.retry_delay * (current_retry + 1)
                    self._log(f"Error fetching data: {response.status_code}. Retrying in {wait_time} seconds...", "error")
                    time.sleep(wait_time)
                    current_retry += 1

            except requests.exceptions.Timeout:
                self._log(f"Request timed out after {self.timeout} seconds", "error")
                if current_retry < self.max_retries - 1:
                    time.sleep(self.retry_delay)
                    current_retry += 1
                else:
                    return []
                    
            except Exception as e:
                self._log(f"Error in fetch_page: {e}", "error")
                if current_retry < self.max_retries - 1:
                    time.sleep(self.retry_delay)
                    current_retry += 1
                else:
                    return []

        self._log("Max retries reached. Could not fetch data.", "error")
        return []

    def process_gift(self, gift: Dict) -> None:
        """
        Обработка одного подарка
        :param gift: данные подарка
        """
        # Получаем имя и модель подарка
        name = gift.get('name')
        model = gift.get('model')
        
        # Проверяем, что имя и модель существуют
        if name and model:
            # Создаем уникальный ключ для подарка
            gift_key = f"{name}|{model}"
            
            # Если такого подарка еще нет в базе
            if gift_key not in self.processed_gifts:
                # Добавляем в множество обработанных
                self.processed_gifts.add(gift_key)
                # Сохраняем в базу данных
                self.db.save_unique_gift(name, model)
                # Выводим сообщение о новом подарке
                self._log(f"Found new gift: {name} ({model})")

    def fetch_all_gifts(self) -> None:
        """
        Получение всех подарков
        """
        # Подключаемся к базе данных
        if not self.db.connect():
            self._log("Failed to connect to database", "error")
            return

        page = 1  # начинаем с первой страницы
        try:
            self._log("Starting gift fetching process...")

            # Бесконечный цикл, пока не получим все подарки
            while True:
                # Проверяем, не превысили ли лимит страниц
                if self.max_pages and page > self.max_pages:
                    self._log(f"Reached page limit ({self.max_pages})")
                    break

                # Получаем страницу подарков
                gifts = self.fetch_page(page)
                
                # Если страница пустая, значит это конец
                if not gifts:
                    self._log("Received empty response, stopping")
                    break

                # Обрабатываем каждый подарок на странице
                for gift in gifts:
                    self.process_gift(gift)

                # Выводим статистику
                self._log(f"Processed page {page}, total unique gifts: {len(self.processed_gifts)}")

                # Переходим к следующей странице
                page += 1
                # Ждем 2-4 секунды перед следующим запросом
                time.sleep(2 + random.random() * 2)

        except Exception as e:
            self._log(f"Error processing page {page}: {e}", "error")
        finally:
            # Закрываем соединение с базой данных
            self.db.close()
            # Вычисляем время работы
            execution_time = time.time() - self.start_time
            # Выводим итоговую статистику
            self._log(f"Gift fetching completed. Total unique gifts: {len(self.processed_gifts)}")
            self._log(f"Execution time: {execution_time:.2f} seconds")

def main():
    """
    Главная функция программы
    """
    import argparse
    # Создаем парсер аргументов командной строки
    parser = argparse.ArgumentParser(description='Fetch gifts from Tonnel API')
    parser.add_argument('--test', action='store_true', help='Run in test mode (2 pages)')
    parser.add_argument('--pages', type=int, help='Number of pages to process')
    args = parser.parse_args()

    # Запоминаем время начала
    start_time = time.time()
    
    # Определяем режим работы
    if args.test:
        # Тестовый режим - всегда 2 страницы
        fetcher = TonnelGiftFetcher(test_mode=True, max_pages=2)
        logger.info("Running in test mode (2 pages)")
        print("Running in test mode (2 pages)")
    elif args.pages:
        # Режим с указанием количества страниц
        fetcher = TonnelGiftFetcher(test_mode=False, max_pages=args.pages)
        logger.info(f"Running with {args.pages} pages limit")
        print(f"Running with {args.pages} pages limit")
    else:
        # Обычный режим - без ограничений
        fetcher = TonnelGiftFetcher(test_mode=False, max_pages=None)
        logger.info("Running in normal mode (no page limit)")
        print("Running in normal mode (no page limit)")
    
    fetcher.fetch_all_gifts()
    
    # Вычисляем общее время работы
    total_time = time.time() - start_time
    logger.info(f"Total script execution time: {total_time:.2f} seconds")
    print(f"Total script execution time: {total_time:.2f} seconds")

# Запускаем программу, если файл запущен напрямую
if __name__ == "__main__":
    main() 