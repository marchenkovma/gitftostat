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

class LaravelFormatter(logging.Formatter):
    """Форматтер для вывода логов в стиле Laravel"""
    
    def format(self, record):
        # Добавляем временную метку в формате Laravel
        record.timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        
        # Форматируем сообщение в стиле Laravel
        if hasattr(record, 'message'):
            record.msg = f"[{record.timestamp}] {record.levelname}: {record.msg}"
        
        return super().format(record)

def setup_logger(name: str, level: str = 'INFO', log_file: Optional[str] = None) -> logging.Logger:
    """Настройка логгера в стиле Laravel"""
    
    logger = logging.getLogger(name)
    logger.setLevel(getattr(logging, level.upper()))

    # Создаем форматтер
    formatter = LaravelFormatter('%(message)s')

    # Добавляем обработчик для вывода в консоль
    console_handler = logging.StreamHandler(sys.stdout)
    console_handler.setFormatter(formatter)
    logger.addHandler(console_handler)

    # Если указан файл для логов, добавляем файловый обработчик
    if log_file:
        file_handler = logging.FileHandler(log_file)
        file_handler.setFormatter(formatter)
        logger.addHandler(file_handler)

    return logger

# Создаем глобальный логгер
logger = setup_logger('tonnel', level='INFO', log_file='tonnel.log') 