import os
import sys
import json
from curl_cffi import requests
import time
from typing import Dict, Optional
from config.database import Database
from config.logger import logger
from datetime import datetime
import random
import time

class TonnelPriceChecker:
    def __init__(self):
        self.base_url = 'https://gifts2.tonnel.network/api'
        self.db = Database()
        self.start_time = time.time()
        logger.info("Tonnel price checker initialized")

    def check_gift_price(self, gift_name: str, model: str) -> Optional[Dict]:
        json_data = {
            'page': 1,
            'limit': 1,
            'sort': json.dumps({'price': 1}),
            'filter': json.dumps({
                "price": {"$exists": True},
                "refunded": {"$ne": True},
                "buyer": {"$exists": False},
                "export_at": {"$exists": True},
                "gift_name": gift_name,
                "model": model,
                "asset": "TON"
            }),
            'price_range': None,
            'user_auth': ''
        }

        try:
            # Добавляем рандомную задержку перед запросом
            delay = random.uniform(1, 2)
            time.sleep(delay)
            #asyncio.sleep(0.30)
            
            response = requests.post(
                f'{self.base_url}/pageGifts',
                json=json_data,
                impersonate="chrome"
            )

        if response.status_code == 429:  # Ошибка "Too Many Requests"
            retry_after = response.headers.get("Retry-After")
            if retry_after:
                wait_time = int(retry_after)
                logger.warning(f"Сервер требует подождать {wait_time} секунд...")
                print(f"Сервер требует подождать {wait_time} секунд...")
                time.sleep(wait_time)
            else:
                logger.warning("Сервер не указал время ожидания, ставим паузу 5 сек.")
                print("Сервер не указал время ожидания, ставим паузу 5 сек.")
                time.sleep(5)  # Ожидание по умолчанию

            return self.check_gift_price(gift_name, model)  # Повторный запрос после паузы

        if response.status_code != 200:
            logger.error(f"Error fetching data: {response.status_code}")
            return None

            data = response.json()
            if data and len(data) > 0:
                return data[0]
            return None

        except Exception as e:
            logger.error(f"Error in check_gift_price: {e}")
            return None

    def process_gift(self, gift_id: int, gift_name: str, model: str) -> None:
        gift_data = self.check_gift_price(gift_name, model)
        
        if gift_data:
            price = gift_data.get('price')
            if price:
                if self.db.save_gift_price(gift_id, price):
                    logger.info(f"Updated price for {gift_name} ({model}): {price} TON")
                else:
                    logger.error(f"Failed to save price for {gift_name} ({model})")
            else:
                logger.warning(f"No price found for {gift_name} ({model})")
        else:
            logger.warning(f"No data found for {gift_name} ({model})")

    def check_all_gifts(self) -> None:
        if not self.db.connect():
            logger.error("Failed to connect to database")
            return

        try:
            gifts = self.db.get_all_gifts()
            total_gifts = len(gifts)
            logger.info(f"Starting price check for {total_gifts} gifts")

            for i, gift in enumerate(gifts, 1):
                self.process_gift(gift['id'], gift['name'], gift['model'])
                logger.info(f"Processed {i}/{total_gifts} gifts")
                time.sleep(1)  # Задержка между запросами

        except Exception as e:
            logger.error(f"Error processing gifts: {e}")
        finally:
            self.db.close()
            execution_time = time.time() - self.start_time
            logger.info(f"Price check completed. Execution time: {execution_time:.2f} seconds")

def main():
    checker = TonnelPriceChecker()
    checker.check_all_gifts()

if __name__ == "__main__":
    main() 
