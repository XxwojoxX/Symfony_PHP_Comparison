FROM php:8.2-cli
WORKDIR /var/www/symfony
COPY . .
RUN apt-get update && apt-get install -y unzip
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
