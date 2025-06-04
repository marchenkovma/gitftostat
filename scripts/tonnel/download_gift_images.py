import os
import sys
import requests
import re
from typing import Dict, Optional
from config.database import Database
from config.logger import logger
import time

class GiftImageDownloader:
    def __init__(self):
        self.base_url = 'https://gifts.coffin.meme'
        self.db = Database()
        self.start_time = time.time()
        self.images_dir = 'public/images/gifts'
        logger.info("Gift image downloader initialized")

    def clean_filename(self, gift_name: str, model: str) -> str:
        # Убираем скобки и проценты из модели
        clean_model = re.sub(r'\s*\([^)]*\)', '', model)
        # Объединяем имя и модель, заменяем пробелы на _, нижний регистр
        filename = f'{gift_name}_{clean_model}'.replace(' ', '_').lower()
        return filename + '.png'

    def download_image(self, gift_name: str, model: str) -> Optional[str]:
        try:
            # Для URL используем оригинальную модель
            encoded_name = gift_name.lower().replace(' ', '%20')
            clean_model = model.split(' (')[0]
            encoded_model = clean_model.replace(' ', '%20')
            url = f"{self.base_url}/{encoded_name}/{encoded_model}.png"
            logger.info(f"Attempting to download image from URL: {url}")
            
            os.makedirs(self.images_dir, exist_ok=True)
            # Для имени файла используем очищенную модель
            filename = self.clean_filename(gift_name, model)
            filepath = os.path.join(self.images_dir, filename)
            
            response = requests.get(url)
            logger.info(f"Response status code: {response.status_code}")
            
            if response.status_code == 200:
                with open(filepath, 'wb') as f:
                    f.write(response.content)
                logger.info(f"Downloaded image for {gift_name} ({model}) to {filepath}")
                return filename  # Возвращаем только имя файла без пути
            else:
                logger.warning(f"Failed to download image for {gift_name} ({model}): {response.status_code}")
                logger.warning(f"Response content: {response.text[:200]}")
                return None
        except Exception as e:
            logger.error(f"Error downloading image for {gift_name} ({model}): {str(e)}")
            return None

    def process_gift(self, gift_id: int, gift_name: str, model: str) -> None:
        logger.info(f"Processing gift: {gift_name} ({model})")
        image_name = self.download_image(gift_name, model)
        if image_name:
            self.db.update_gift_image(gift_id, image_name)
            logger.info(f"Updated image path for {gift_name} ({model}) to {image_name}")
        else:
            logger.warning(f"No image downloaded for {gift_name} ({model})")

    def download_all_gifts(self) -> None:
        if not self.db.connect():
            logger.error("Failed to connect to database")
            return
        try:
            gifts = self.db.get_all_gifts()
            total_gifts = len(gifts)
            logger.info(f"Starting image download for {total_gifts} gifts")
            for i, gift in enumerate(gifts, 1):
                self.process_gift(gift['id'], gift['name'], gift['model'])
                logger.info(f"Processed {i}/{total_gifts} gifts")
                time.sleep(1)
        except Exception as e:
            logger.error(f"Error processing gifts: {str(e)}")
        finally:
            self.db.close()
            execution_time = time.time() - self.start_time
            logger.info(f"Image download completed. Execution time: {execution_time:.2f} seconds")

def main():
    downloader = GiftImageDownloader()
    downloader.download_all_gifts()

if __name__ == "__main__":
    main() 