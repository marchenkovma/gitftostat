#!/bin/bash

# Запоминаем время начала выполнения
START_TIME=$(date +%s)

# Путь к виртуальному окружению
VENV_PATH="$(dirname "$0")/venv"

# Если виртуальное окружение не существует, создаем его
if [ ! -d "$VENV_PATH" ]; then
    echo "Creating virtual environment..."
    python3 -m venv "$VENV_PATH"
    source "$VENV_PATH/bin/activate"
    
    # Обновляем pip
    pip3 install --upgrade pip
    
    # Устанавливаем зависимости
    echo "Installing dependencies..."
    pip3 install -r "$(dirname "$0")/requirements.txt"
else
    source "$VENV_PATH/bin/activate"
    
    # Проверяем и обновляем зависимости
    echo "Updating dependencies..."
    pip3 install -r "$(dirname "$0")/requirements.txt" --upgrade
fi

# Устанавливаем PYTHONPATH
export PYTHONPATH="$(dirname "$0"):$PYTHONPATH"

# Определяем, какой скрипт запускать
if [ "$1" = "check_gift_price.py" ]; then
    # Запускаем скрипт проверки цен
    python3 "$(dirname "$0")/check_gift_price.py" "${@:2}"
else
    # Запускаем скрипт получения всех подарков
    python3 "$(dirname "$0")/fetch_all_gifts.py" "$@"
fi

# Записываем время выполнения
END_TIME=$(date +%s)
EXECUTION_TIME=$((END_TIME - START_TIME))
LOG_FILE="${TONNEL_LOG_FILE:-storage/logs/tonnel.log}"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] INFO: Total execution time: ${EXECUTION_TIME} seconds" >> "$LOG_FILE" 