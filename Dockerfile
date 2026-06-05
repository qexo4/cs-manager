FROM php:8.2-apache

# instalacja rozszerzeń do PDO + MySQL
RUN docker-php-ext-install pdo pdo_mysql

# kopiowanie plików do serwera
COPY . /var/www/html/

# ustawienia Apache
RUN chown -R www-data:www-data /var/www/html
