FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    unzip git libpq-dev \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/symfony
COPY ./backend-symfony /var/www/symfony

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
EXPOSE 8000
