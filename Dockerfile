FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git unzip \
    libicu-dev libzip-dev libpq-dev libonig-dev libxml2-dev \
    mariadb-client \
    netcat-openbsd \
    && docker-php-ext-install intl zip pdo pdo_mysql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*


WORKDIR /app

COPY . /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]

CMD ["php-fpm"]