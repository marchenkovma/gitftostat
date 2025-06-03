import os
import psycopg2
from psycopg2 import Error
from psycopg2.extras import DictCursor
from typing import Optional, List, Dict
from datetime import datetime
from .config import DB_CONFIG
from .logger import logger

class Database:
    def __init__(self):
        self.conn = None
        self.cursor = None
        self.config = DB_CONFIG

    def connect(self) -> bool:
        try:
            self.conn = psycopg2.connect(
                host=os.getenv('DB_HOST'),
                port=os.getenv('DB_PORT'),
                database=os.getenv('DB_DATABASE'),
                user=os.getenv('DB_USERNAME'),
                password=os.getenv('DB_PASSWORD')
            )
            self.cursor = self.conn.cursor(cursor_factory=DictCursor)
            
            # Устанавливаем часовой пояс для сессии
            self.cursor.execute("SET timezone = 'Europe/Moscow'")
            self.conn.commit()
            
            logger.info("Successfully connected to database")
            return True
        except Exception as e:
            logger.error(f"Database connection error: {e}")
            return False

    def close(self) -> None:
        if self.cursor:
            self.cursor.close()
        if self.conn:
            self.conn.close()
            logger.info("Database connection closed")

    def save_unique_gift(self, name: str, model: str) -> None:
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
        """Получить все подарки из базы данных"""
        try:
            self.cursor.execute("SELECT id, name, model FROM gifts")
            return [dict(row) for row in self.cursor.fetchall()]
        except Exception as e:
            logger.error(f"Error getting gifts: {e}")
            return []

    def save_gift_price(self, gift_id: int, price: float) -> bool:
        """Сохранить цену подарка"""
        try:
            now = datetime.utcnow()
            self.cursor.execute(
                """
                INSERT INTO gift_prices (gift_id, price, checked_at, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s)
                """,
                (gift_id, price, now, now, now)
            )
            self.conn.commit()
            return True
        except Exception as e:
            logger.error(f"Error saving gift price: {e}")
            self.conn.rollback()
            return False 