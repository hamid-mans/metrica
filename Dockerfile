FROM php:8.4-fpm

# Dépendances système + extensions PHP
RUN apt-get update && apt-get install -y \
    git unzip \
    libicu-dev libzip-dev libpq-dev libonig-dev libxml2-dev \
    mariadb-client \
    netcat-openbsd \
    curl \
    && docker-php-ext-install intl zip pdo pdo_mysql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Installation Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin \
    --filename=composer

# Copie uniquement les fichiers Composer pour profiter du cache Docker
COPY composer.json composer.lock ./

# Installation des dépendances (production safe)
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-progress \
    --no-interaction \
    --optimize-autoloader

# Copie du reste du projet
COPY . .

# Permissions Symfony (cache/logs)
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var

# Entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]

CMD ["php-fpm"]