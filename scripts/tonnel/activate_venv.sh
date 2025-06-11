#!/bin/bash

# Запоминаем время начала выполнения
START_TIME=$(date +%s)

# Путь к виртуальному окружению
VENV_PATH="$(dirname "$0")/venv"

# Если виртуальное окружение не существует, создаем его
if [ ! -d "$VENV_PATH" ]; then
    echo "Creating virtual environment..."
    python3.11 -m venv "$VENV_PATH"
    source "$VENV_PATH/bin/activate"
    
    # Обновляем pip
    pip install --upgrade pip
    
    # Устанавливаем зависимости
    echo "Installing dependencies..."
    pip install -r "$(dirname "$0")/requirements.txt"
else
    source "$VENV_PATH/bin/activate"
    
    # Проверяем и обновляем зависимости
    echo "Updating dependencies..."
    pip install -r "$(dirname "$0")/requirements.txt" --upgrade
fi

# Устанавливаем PYTHONPATH
export PYTHONPATH="$(dirname "$0"):$PYTHONPATH"

# Определяем, какой скрипт запускать
if [ "$1" = "check_gift_price.py" ]; then
    # Запускаем скрипт проверки цен
    python "$(dirname "$0")/check_gift_price.py" "${@:2}"
elif [ "$1" = "download_gift_images.py" ]; then
    # Запускаем скрипт загрузки изображений
    python "$(dirname "$0")/download_gift_images.py" "${@:2}"
elif [ "$1" = "get_gifts.py" ]; then
    # Запускаем скрипт получения подарков
    python "$(dirname "$0")/get_gifts.py" "${@:2}"
else
    # Запускаем старый скрипт получения всех подарков
    python "$(dirname "$0")/fetch_all_gifts.py" "$@"
fi

# Записываем время выполнения
END_TIME=$(date +%s)
EXECUTION_TIME=$((END_TIME - START_TIME))
LOG_FILE="${TONNEL_LOG_FILE:-storage/logs/tonnel.log}"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] INFO: Total execution time: ${EXECUTION_TIME} seconds" >> "$LOG_FILE" 