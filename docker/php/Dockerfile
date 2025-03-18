# Użyj oficjalnego obrazu PHP z Apache
FROM php:8.2-apache

# Ustaw katalog roboczy
WORKDIR /var/www/html

# Skopiuj pliki aplikacji do kontenera
COPY . .

# Włącz mod_rewrite (jeśli jest to wymagane dla Symfony)
RUN a2enmod rewrite

# Zainstaluj wymagane zależności PHP (np. dla bazy danych)
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd

# Ustaw domyślny port 80 w kontenerze
EXPOSE 80
