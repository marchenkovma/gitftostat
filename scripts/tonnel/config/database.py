import psycopg2
from psycopg2 import Error
from typing import Optional
from datetime import datetime
from .config import DB_CONFIG
from .logger import logger

class Database:
    def __init__(self):
        self.connection = None
        self.cursor = None
        self.config = DB_CONFIG

    def connect(self) -> bool:
        try:
            self.connection = psycopg2.connect(
                host=self.config['host'],
                port=self.config['port'],
                database=self.config['database'],
                user=self.config['user'],
                password=self.config['password']
            )
            self.cursor = self.connection.cursor()
            logger.info("Successfully connected to database")
            return True
        except Error as e:
            logger.error(f"Error connecting to database: {e}")
            return False

    def close(self) -> None:
        if self.cursor:
            self.cursor.close()
        if self.connection:
            self.connection.close()
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
            self.connection.commit()
        except Error as e:
            logger.error(f"Error saving gift to database: {e}")
            self.connection.rollback() 