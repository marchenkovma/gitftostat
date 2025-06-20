import logging
import sys
from datetime import datetime
from typing import Optional
import os
from pathlib import Path

# Определяем путь к файлу логов
LOG_FILE = os.getenv('TONNEL_LOG_FILE', os.path.join(Path(__file__).resolve().parent.parent.parent.parent, 'storage', 'logs', 'tonnel.log'))

# Создаем директорию для логов, если она не существует
os.makedirs(os.path.dirname(LOG_FILE), exist_ok=True)

class LaravelFormatter(logging.Formatter):
    """
    Форматтер для вывода логов в стиле Laravel
    """
    
    def format(self, record):
        # Форматируем сообщение в стиле Laravel
        timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        return f"[{timestamp}] {record.levelname}: {record.getMessage()}"

class FileFilter(logging.Filter):
    """
    Фильтр для файла логов - пропускает только важные сообщения
    """
    
    def filter(self, record):
        # Пропускаем только сообщения уровня INFO и выше, исключая сообщения о подарках
        if record.levelno >= logging.INFO:
            message = record.getMessage()
            # Исключаем сообщения о подарках и статистику по страницам
            if not any([
                "Found new gift:" in message,
                "Processed page" in message and "total unique gifts" in message
            ]):
                return True
        return False

def setup_logger(name: str, level: str = 'INFO', log_file: Optional[str] = None) -> logging.Logger:
    """
    Настройка логгера в стиле Laravel
    :param name: имя логгера
    :param level: уровень логирования
    :param log_file: путь к файлу логов
    :return: настроенный логгер
    """
    
    logger = logging.getLogger(name)
    logger.setLevel(getattr(logging, level.upper()))

    # Очищаем существующие обработчики
    logger.handlers = []

    # Создаем форматтер
    formatter = LaravelFormatter()

    # Добавляем обработчик для вывода в консоль (все сообщения)
    console_handler = logging.StreamHandler(sys.stdout)
    console_handler.setFormatter(formatter)
    logger.addHandler(console_handler)

    # Если указан файл для логов, добавляем файловый обработчик (только важные сообщения)
    if log_file:
        file_handler = logging.FileHandler(log_file, mode='a', encoding='utf-8')
        file_handler.setFormatter(formatter)
        file_handler.addFilter(FileFilter())  # Добавляем фильтр
        logger.addHandler(file_handler)

    return logger

# Создаем глобальный логгер
logger = setup_logger('tonnel', level=os.getenv('TONNEL_LOG_LEVEL', 'INFO'), log_file=LOG_FILE) 