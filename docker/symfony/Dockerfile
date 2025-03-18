FROM php:8.2-cli

WORKDIR /var/www/symfony

# Kopiowanie tylko plików z folderu symfony (w tym composer.json) do kontenera
COPY symfony/ /var/www/symfony/

# Instalacja niezbędnych pakietów, jak unzip
RUN apt-get update && apt-get install -y unzip

# Instalacja Composer'a
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Instalacja zależności projektu Symfony
RUN composer install

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
