#!/bin/bash

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

# Запускаем Python скрипт с переданными аргументами
python3 "$(dirname "$0")/fetch_all_gifts.py" "$@" 