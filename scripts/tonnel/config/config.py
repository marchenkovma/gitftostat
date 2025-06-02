import os
from dotenv import load_dotenv
from pathlib import Path

# Загружаем переменные окружения из .env файла
load_dotenv()

# Базовые пути
BASE_DIR = Path(__file__).resolve().parent.parent
ROOT_DIR = BASE_DIR.parent.parent

# Пути к файлам
LOGS_DIR = os.path.join(ROOT_DIR, 'storage', 'logs')
LOG_FILE = os.path.join(LOGS_DIR, 'tonnel.log')

# Создаем директорию для логов, если она не существует
os.makedirs(LOGS_DIR, exist_ok=True)

# Настройки логирования
LOGGING = {
    'version': 1,
    'disable_existing_loggers': False,
    'formatters': {
        'standard': {
            'format': '[{asctime}] {levelname}: {message}',
            'datefmt': '%Y-%m-%d %H:%M:%S',
            'style': '{'
        },
    },
    'handlers': {
        'console': {
            'class': 'logging.StreamHandler',
            'formatter': 'standard',
            'level': 'INFO',
        },
        'file': {
            'class': 'logging.FileHandler',
            'formatter': 'standard',
            'filename': LOG_FILE,
            'level': 'INFO',
        },
    },
    'loggers': {
        '': {
            'handlers': ['console', 'file'],
            'level': 'INFO',
            'propagate': True
        }
    }
}

# Конфигурация базы данных из Laravel
DB_CONFIG = {
    'host': os.getenv('DB_HOST', '127.0.0.1'),
    'port': os.getenv('DB_PORT', '5432'),
    'database': os.getenv('DB_DATABASE', 'gifts'),
    'user': os.getenv('DB_USERNAME', 'testuser'),
    'password': os.getenv('DB_PASSWORD', '')
} 