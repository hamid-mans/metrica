#!/bin/sh
set -e

echo "> En attente de la BDD..."
until mysql -h db -uhm -phamid123 --ssl-mode=DISABLED -e "SELECT 1;" >/dev/null 2>&1; do
    echo "Erreur connexion BDD (entrypoint)..."
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
    php bin/console assets:install public --no-interaction
    php bin/console asset-map:compile

    echo "> Cache prod..."
    php bin/console cache:clear --env=prod
    php bin/console cache:warmup --env=prod
fi

echo "✓ Application prête !"

# Lancement du CMD par défaut (php-fpm)
exec "$@"