import os
import psycopg2
from psycopg2 import Error
from psycopg2.extras import DictCursor
from typing import Optional, List, Dict
from datetime import datetime
from .config import DB_CONFIG
from .logger import logger

class Database:
    """
    Класс для работы с базой данных PostgreSQL
    """
    def __init__(self):
        """
        Инициализация класса
        """
        self.conn = None
        self.cursor = None
        self.config = DB_CONFIG

    def connect(self) -> bool:
        """
        Подключение к базе данных
        :return: True если подключение успешно, False в случае ошибки
        """
        try:
            self.conn = psycopg2.connect(
                host=os.getenv('DB_HOST'),
                port=os.getenv('DB_PORT'),
                database=os.getenv('DB_DATABASE'),
                user=os.getenv('DB_USERNAME'),
                password=os.getenv('DB_PASSWORD')
            )
            self.cursor = self.conn.cursor(cursor_factory=DictCursor)
            logger.info("Successfully connected to database")
            return True
            
        except Exception as e:
            logger.error(f"Database connection error: {e}")
            return False

    def close(self) -> None:
        """
        Закрытие соединения с базой данных
        """
        if self.cursor:
            self.cursor.close()
        if self.conn:
            self.conn.close()
            logger.info("Database connection closed")

    def save_unique_gift(self, name: str, model: str) -> None:
        """
        Сохранение уникального подарка в базу данных
        :param name: название подарка
        :param model: модель подарка
        """
        try:
            now = datetime.now()
            self.cursor.execute(
                """
                INSERT INTO gifts (name, model, created_at, updated_at) 
                VALUES (%s, %s, %s, %s) 
                ON CONFLICT (name, model) DO NOTHING
                """,
                (name, model, now, now)
            )
            self.conn.commit()
        except Error as e:
            logger.error(f"Error saving gift to database: {e}")
            self.conn.rollback()

    def get_all_gifts(self) -> List[Dict]:
        """
        Получение всех подарков из базы данных
        :return: список подарков
        """
        try:
            self.cursor.execute("SELECT id, name, model FROM gifts")
            return [dict(row) for row in self.cursor.fetchall()]
        except Exception as e:
            logger.error(f"Error getting gifts: {e}")
            return []

    def save_gift_price(self, name: str, model: str, price: float) -> bool:
        """
        Save gift price to the database.
        
        Args:
            name: Gift name
            model: Gift model
            price: Gift price
            
        Returns:
            bool: True if price was saved successfully, False otherwise
        """
        try:
            # Получаем ID подарка по имени и модели
            self.cursor.execute(
                "SELECT id FROM gifts WHERE name = %s AND model = %s",
                (name, model)
            )
            result = self.cursor.fetchone()
            
            if not result:
                logger.warning(f"Gift not found: {name} ({model})")
                return False
                
            gift_id = result[0]
            
            # Сохраняем цену
            self.cursor.execute(
                """
                INSERT INTO gift_prices (gift_id, price, created_at)
                VALUES (%s, %s, NOW())
                """,
                (gift_id, price)
            )
            self.conn.commit()
            
            logger.info(f"Saved price {price} for gift {name} ({model})")
            return True
            
        except Exception as e:
            self.conn.rollback()
            logger.error(f"Failed to save gift price: {str(e)}")
            return False

    def update_gift_image(self, gift_id: int, image_path: str) -> bool:
        """
        Обновление пути к изображению подарка
        :param gift_id: ID подарка
        :param image_path: путь к изображению
        :return: True если обновление успешно, False в случае ошибки
        """
        try:
            with self.conn.cursor() as cursor:
                cursor.execute(
                    "UPDATE gifts SET image = %s WHERE id = %s",
                    (image_path, gift_id)
                )
                self.conn.commit()
                return True
        except Exception as e:
            logger.error(f"Error updating gift image: {e}")
            return False 