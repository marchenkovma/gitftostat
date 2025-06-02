import time
import os
from datetime import datetime

def log_execution_time(start_time):
    log_file = os.getenv('TONNEL_LOG_FILE', 'storage/logs/tonnel.log')
    execution_time = time.time() - start_time
    
    # Создаем директорию для логов, если она не существует
    os.makedirs(os.path.dirname(log_file), exist_ok=True)
    
    # Форматируем время выполнения
    timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    log_message = f"[{timestamp}] INFO: Script execution time: {execution_time:.2f} seconds\n"
    
    # Записываем в файл
    with open(log_file, 'a', encoding='utf-8') as f:
        f.write(log_message) 