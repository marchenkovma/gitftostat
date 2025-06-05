import os
import sys
import json
from curl_cffi import requests
import time
from typing import List, Dict, Set, Optional
from config.database import Database
from config.config import DB_CONFIG
from config.logger import logger
from datetime import datetime
import random
import time

class Tonnel:
    def __init__(self, test_mode: bool = False, test_pages: int = 2, search_name: Optional[str] = None):
        self.base_url = 'https://gifts2.tonnel.network/api'
        self.db = Database()
        self.processed_gifts: Set[str] = set()
        self.test_mode = test_mode
        self.test_pages = test_pages
        self.search_name = search_name.lower() if search_name else None
        self.start_time = time.time()
        logger.info("Tonnel client initialized")

    def fetch_page(self, page: int, limit: int = 30) -> List[Dict]:
        json_data = {
            'page': page,
            'limit': limit,
            'sort': '',
            'filter': '',
            'price_range': None,
            'user_auth': ''
        }

        try:
            # Добавляем рандомную задержку перед запросом
            delay = random.uniform(1, 2)
            time.sleep(delay)
            
            response = requests.post(
                f'{self.base_url}/pageGifts',
                json=json_data,
                impersonate="chrome"
            )

            if response.status_code != 200:
                logger.error(f"Error fetching data: {response.status_code}")
                return []

            return response.json()
        except Exception as e:
            logger.error(f"Error in fetch_page: {e}")
            return []

    def process_gift(self, gift: Dict) -> None:
        name = gift.get('name')
        model = gift.get('model')
        
        if name and model:
            # Если указано имя для поиска, проверяем совпадение
            if self.search_name and self.search_name not in name.lower():
                return

            gift_key = f"{name}|{model}"
            if gift_key not in self.processed_gifts:
                self.processed_gifts.add(gift_key)
                self.db.save_unique_gift(name, model)
                logger.info(f"Processed new gift: {name} ({model})")

    def fetch_all_gifts(self) -> None:
        if not self.db.connect():
            logger.error("Failed to connect to database")
            return

        page = 1
        empty_responses = 0
        max_empty_responses = 3

        try:
            logger.info("Starting gift fetching process")
            while True:
                if self.test_mode and page > self.test_pages:
                    logger.info(f"Test mode: reached page limit ({self.test_pages})")
                    break

                gifts = self.fetch_page(page)
                
                if not gifts:
                    empty_responses += 1
                    if empty_responses >= max_empty_responses:
                        logger.warning("Received multiple empty responses, stopping")
                        break
                else:
                    empty_responses = 0
                    for gift in gifts:
                        self.process_gift(gift)

                logger.info(f"Processed page {page}, total unique gifts: {len(self.processed_gifts)}")

                if len(gifts) < 30:
                    logger.info("Reached end of gift list")
                    break

                page += 1
                time.sleep(1)

        except Exception as e:
            logger.error(f"Error processing page {page}: {e}")
        finally:
            self.db.close()
            execution_time = time.time() - self.start_time
            logger.info(f"Gift fetching completed. Total unique gifts: {len(self.processed_gifts)}")
            logger.info(f"Execution time: {execution_time:.2f} seconds")

def main():
    import argparse
    parser = argparse.ArgumentParser(description='Fetch gifts from Tonnel API')
    parser.add_argument('--test', action='store_true', help='Run in test mode')
    parser.add_argument('--pages', type=int, default=2, help='Number of pages to process in test mode')
    parser.add_argument('--name', type=str, help='Search for gifts by name')
    args = parser.parse_args()

    start_time = time.time()
    tonnel = Tonnel(test_mode=args.test, test_pages=args.pages, search_name=args.name)
    tonnel.fetch_all_gifts()
    total_time = time.time() - start_time
    logger.info(f"Total script execution time: {total_time:.2f} seconds")

if __name__ == "__main__":
    main() 
