A MySQL class, that implements prepared statements to increase efficiency and security.

## MySQL Class in PHP

Solution for easy mysql implementations, still needs some work. Has evolved over the years, up until the point where it handles prepared statements.

### Configuration

The only (optional) setup to bare in mind, is to set the constants DB_HOST, DB_USER, DB_PASSWORD and DB_NAME before using the class. The values can be also passed directly when instancing the class.

Example:



```php

  define('DB_HOST', 'localhost');
  define('DB_USER', 'localuser');
  define('DB_PASSWORD', 'localpassword');
  define('DB_NAME', 'localdatabase');

```


# Installation

## By [Hand](https://github.com/joseporto/RestServer)

```
cd <your project>
mkdir -p vendor/joseporto/mysql-class
cd vendor/joseporto/mysql-class
git clone https://github.com/joseporto/mysql-class.git .
composer install
```

## By [Packagist](https://packagist.org/packages/joseporto/mysql-class)

```
cd <your project>
composer require 'joseporto/mysql-class'
```
