#!/bin/sh
set -e

echo "> En attente de la BDD..."
until mysqladmin ping -h db -P 3306 --silent; do
  echo "MySQL pas encore prêt..."
  sleep 2
done
echo "✓ BDD OK !"

# Installer les dépendances si vendor est absent
if [ ! -d vendor ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Seulement en prod, on clear et warmup le cache
if [ "$APP_ENV" = "prod" ]; then
    echo "> Creation schema de BDD..."
    php bin/console doctrine:schema:update --force

    echo "> Build assets..."
    composer require symfony/ux-stimulus --dev
    php bin/console assets:install public --no-interaction
    php bin/console asset-map:compile

    echo "> Cache prod..."
    php bin/console cache:clear --env=prod
    php bin/console cache:warmup --env=prod
fi

echo "✓ Application prête !"

# Lancement du CMD par défaut (php-fpm)
exec "$@"