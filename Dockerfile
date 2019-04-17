FROM php:7.2-cli

WORKDIR /usr/src/myapp

RUN docker-php-ext-install mbstring

CMD [ "php", "./server.php" ]