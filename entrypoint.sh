#!/bin/sh
set -e

echo "> Attente MySQL..."
until mysqladmin ping -h db -P 3306 --silent; do
  sleep 2
done
echo "✓ MySQL prêt"

echo "> Création DB si absente..."
php bin/console doctrine:database:create --if-not-exists --no-interaction

echo "> Migration DB..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

if [ "$APP_ENV" = "prod" ]; then
    echo "> Cache prod..."
    php bin/console cache:clear --env=prod
    php bin/console cache:warmup --env=prod
fi

echo "✓ Symfony prêt"

exec "$@"