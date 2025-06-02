#!/bin/bash

# Путь к .env файлу Laravel
ENV_FILE="../.env"

# Проверяем существование .env файла
if [ ! -f "$ENV_FILE" ]; then
    echo "Error: .env file not found at $ENV_FILE"
    exit 1
fi

# Функция для получения значения из .env файла
get_env_value() {
    local key=$1
    local value=$(grep "^$key=" "$ENV_FILE" | cut -d '=' -f2- | tr -d '"' | tr -d "'")
    echo "$value"
}

# Получаем данные для подключения из .env
DB_HOST=$(get_env_value "DB_HOST")
DB_PORT=$(get_env_value "DB_PORT")
DB_DATABASE=$(get_env_value "DB_DATABASE")
DB_USERNAME=$(get_env_value "DB_USERNAME")
DB_PASSWORD=$(get_env_value "DB_PASSWORD")

# Проверяем наличие всех необходимых переменных
if [ -z "$DB_HOST" ] || [ -z "$DB_PORT" ] || [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ] || [ -z "$DB_PASSWORD" ]; then
    echo "Error: Missing required database configuration in .env file"
    exit 1
fi

echo "Creating database $DB_DATABASE..."

# Создаем базу данных
PGPASSWORD=$DB_PASSWORD psql -h $DB_HOST -p $DB_PORT -U $DB_USERNAME -d postgres << EOF
CREATE DATABASE $DB_DATABASE;
EOF

if [ $? -eq 0 ]; then
    echo "Database $DB_DATABASE created successfully!"
    echo "Now you can run Laravel migrations to create tables."
else
    echo "Error creating database"
    exit 1
fi 