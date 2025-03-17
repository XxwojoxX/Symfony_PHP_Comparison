FROM php:8.2-fpm

# Instalowanie wymaganych pakietów
RUN apt-get update && apt-get install -y \
    unzip git libpq-dev \
    && docker-php-ext-install pdo pdo_mysql

# Ustawienie katalogu roboczego
WORKDIR /var/www/symfony

# Skopiowanie plików aplikacji Symfony
COPY ./backend-symfony /var/www/symfony

# Skopiowanie pliku .env.example do .env
COPY ./backend-symfony/.env.example /var/www/symfony/.env

# Instalacja Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalacja zależności
RUN composer install

# Uruchomienie PHP-FPM
CMD ["php-fpm"]
EXPOSE 9000
