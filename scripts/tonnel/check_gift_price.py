import os
import sys
import json
import time
import random
from typing import Dict, Optional, List
from curl_cffi import requests
from config.database import Database
from config.logger import logger
from datetime import datetime

class TonnelPriceChecker:
    def __init__(self, test_mode: bool = False, max_pages: int = None):
        """
        Initialize Tonnel price checker.
        
        Args:
            test_mode: If True, run in test mode with limited pages
            max_pages: Maximum number of pages to process
        """
        self.base_url = 'https://gifts2.tonnel.network/api'
        self.db = Database()
        self.test_mode = test_mode
        self.max_pages = max_pages
        self.start_time = time.time()
        
        if test_mode:
            logger.info("Running in test mode (2 pages)")
            self.max_pages = 2
        elif max_pages:
            logger.info(f"Running with {max_pages} pages limit")
            
        logger.info("Starting Tonnel price checker")

    def check_gift_price(self, gift_name: str, model: str) -> Optional[Dict]:
        """
        Check price for a specific gift.
        
        Args:
            gift_name: Name of the gift
            model: Model of the gift
            
        Returns:
            Optional[Dict]: Gift data with price if found, None otherwise
        """
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
            # Add random delay
            delay = random.uniform(0.25, 0.3)
            time.sleep(delay)
        
            response = requests.post(
                f'{self.base_url}/pageGifts',
                json=json_data,
                impersonate="chrome"
            )
        
            # Handle 502 error
            if response.status_code == 502:
                logger.warning(f"Server unavailable (502), skipping {gift_name} ({model})...")
                return None
    
            # Handle rate limit
            if response.status_code == 429:
                retry_after = response.headers.get("Retry-After")
                wait_time = int(retry_after) if retry_after else 5
                logger.warning(f"Rate limit reached, waiting {wait_time} seconds...")
                time.sleep(wait_time)
                return self.check_gift_price(gift_name, model)
    
            if response.status_code != 200:
                logger.error(f"Request error: {response.status_code}")
                return None
        
            data = response.json()
            return data[0] if data else None
        
        except Exception as e:
            logger.error(f"Error in check_gift_price: {e}")
            return None

    def process_gift(self, gift_id: int, gift_name: str, model: str) -> None:
        """
        Process a single gift and update its price.
        
        Args:
            gift_id: ID of the gift in database
            gift_name: Name of the gift
            model: Model of the gift
        """
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
        """
        Check prices for all gifts in the database.
        """
        while True:  # Бесконечный цикл
            try:
                if not self.db.connect():
                    logger.error("Failed to connect to database")
                    time.sleep(60)  # Ждем минуту перед повторной попыткой
                    continue

                gifts = self.db.get_all_gifts()
                total_gifts = len(gifts)
                logger.info(f"Starting price check for {total_gifts} gifts")

                for i, gift in enumerate(gifts, 1):
                    self.process_gift(gift['id'], gift['name'], gift['model'])
                    logger.info(f"Processed {i}/{total_gifts} gifts")
                    time.sleep(1)  # Delay between requests

                # Закрываем соединение с базой данных
                self.db.close()
                
                # Вычисляем время выполнения
                execution_time = time.time() - self.start_time
                logger.info(f"Price check completed. Execution time: {execution_time:.2f} seconds")
                
                # Ждем 10 минут перед следующей проверкой
                logger.info("Waiting 10 minutes before next check...")
                time.sleep(600)  # 600 секунд = 10 минут
                
            except Exception as e:
                logger.error(f"Error processing gifts: {e}")
                time.sleep(60)  # При ошибке ждем минуту перед повторной попыткой
                continue

def main():
    """
    Main function to run the price checker.
    """
    import argparse
    
    parser = argparse.ArgumentParser(description='Check Tonnel gift prices')
    parser.add_argument('--test', action='store_true', help='Run in test mode (2 pages)')
    parser.add_argument('--pages', type=int, help='Maximum number of pages to process')
    
    args = parser.parse_args()
    
    try:
        checker = TonnelPriceChecker(test_mode=args.test, max_pages=args.pages)
        logger.info("Starting infinite price checking loop...")
        checker.check_all_gifts()
    except KeyboardInterrupt:
        logger.info("Script stopped by user")
    except Exception as e:
        logger.error(f"Unexpected error: {e}")

if __name__ == "__main__":
    main() 
