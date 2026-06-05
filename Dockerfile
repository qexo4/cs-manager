FROM php:8.2-apache

# Instalacja rozszerzeń systemowych oraz sterowników pdo_pgsql dla PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Kopiowanie plików aplikacji do katalogu serwera
COPY . /var/www/html/

# Ustawienie uprawnień (Z DUŻYM -R)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
