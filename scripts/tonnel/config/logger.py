import logging.config
from .config import LOGGING

# Настраиваем логирование
logging.config.dictConfig(LOGGING)

# Создаем логгер
logger = logging.getLogger(__name__)

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
    """Форматтер для вывода логов в стиле Laravel"""
    
    def format(self, record):
        # Форматируем сообщение в стиле Laravel
        record.msg = f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] {record.levelname}: {record.msg}"
        return super().format(record)

def setup_logger(name: str, level: str = 'INFO', log_file: Optional[str] = None) -> logging.Logger:
    """Настройка логгера в стиле Laravel"""
    
    logger = logging.getLogger(name)
    logger.setLevel(getattr(logging, level.upper()))

    # Очищаем существующие обработчики
    logger.handlers = []

    # Создаем форматтер
    formatter = LaravelFormatter('%(message)s')

    # Добавляем обработчик для вывода в консоль
    console_handler = logging.StreamHandler(sys.stdout)
    console_handler.setFormatter(formatter)
    logger.addHandler(console_handler)

    # Если указан файл для логов, добавляем файловый обработчик
    if log_file:
        file_handler = logging.FileHandler(log_file, mode='a', encoding='utf-8')
        file_handler.setFormatter(formatter)
        logger.addHandler(file_handler)

    return logger

# Создаем глобальный логгер
logger = setup_logger('tonnel', level=os.getenv('TONNEL_LOG_LEVEL', 'INFO'), log_file=LOG_FILE) 