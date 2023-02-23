# Flower picker

## Requirements
1. PHP >=8.1
2. pgsql driver (if you want to run outside of docker container)

## Installation
1. Clone this repository
2. Add demo db credentials to **.env.local**:
```
APP_ENV=dev
DATABASE_USER=user
DATABASE_PASSWORD=pass
DATABASE_NAME=db
DATABASE_HOST=database
```
3. Run ```composer install```

4. Build docker containers
```
docker-compose --env-file ./flower_picker/.env.local up --build
```
5. Make migrations
```
php bin/console doctrine:migrations:migrate
```
6. Run app
```
docker-compose --env-file ./flower_picker/.env.local up -d
```

## Usage
Import flowers by running:
```
php bin/console app:flower-picker 
```

## Tests
```
php bin/phpunit
```