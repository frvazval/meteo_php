# Versión de la imagen que vamos a utilizar
FROM php:latest
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
CMD [ "php", "./index.php" ]