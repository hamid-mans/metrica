#!/bin/sh
set -e
cd /valori
echo "> En attente de la BDD..."
until mysqladmin ping -h db -u root --password=roothamid123 --silent; do
    echo "Erreur connexion BDD (entrypoint)..."
    sleep 2
done
echo "✓ BDD OK !"

# Installer les dépendances si besoin
if [ ! -d vendor ]; then
    composer install --no-dev --optimize-autoloader
fi

echo "> Creation schema de BDD..."
php bin/console doctrine:schema:update --force --complete

echo "> Build assets..."
php bin/console assets:install public --no-interaction
php bin/console asset-map:compile
php bin/console cache:clear --env=prod

echo "✓ Application prete !"
exec "$@"