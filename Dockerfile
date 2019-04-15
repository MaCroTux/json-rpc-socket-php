FROM php:7.2-cli

WORKDIR /usr/src/myapp

CMD [ "php", "./server.php" ]