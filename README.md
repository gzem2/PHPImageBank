# PHPImageBank

Upload and view images in a gallery

## Features

- Upload images
- View your image with a link
- Browse all uploaded images in a list or by tags
- Create users and comments

## Run with SQLite

```sh
composer du
php App\Seeder.php
run.bat
```

## Run with built-in server

```sh
php -S localhost:8080 public\index.php
```

## Create tables and seed database with test data

```sh
php App\Seeder.php
```

## Create tables without test data

```sh
php App\Seeder.php --noseed
```

## Run with MySQL

Edit config/db.php
```php
db = "mysql://localhost:3306/phpimagebank"
```

## Run tests

Install phpunit and php-webdriver:

```sh
composer install
```

Run tests:

```sh
composer test
```

## Running selenium tests:

Download selenium standalone server from [selenium website](https://www.selenium.dev/downloads/).

Start selenium server:
```sh
java -jar selenium-server-standalone.jar
```

Start PHP server:
```sh
run.bat
```

Run selenium tests:
```sh
composer test-selenium
```

Run non-selenium tests together with selenium tests:
```sh
composer test-all
```

## Generate documentation

```sh
doxygen doxygen.conf
```