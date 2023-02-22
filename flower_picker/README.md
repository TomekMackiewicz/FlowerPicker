# Flower picker

## Requirements
- PHP >=8.1

## Installation

Demo db credentials:
```
DATABASE_USER=user
DATABASE_PASSWORD=pass
DATABASE_NAME=db
DATABASE_HOST=database
```
docker-compose --env-file ./flower_picker/.env.local up --build

## Usage
```
docker-compose --env-file ./flower_picker/.env.local up -d
php bin/console app:flower-picker 
```

## Tests
```
php bin/phpunit
```