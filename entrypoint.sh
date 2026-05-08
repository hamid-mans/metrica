#!/bin/sh
set -e

cd /valori

echo "> En attente de la BDD..."
until mysqladmin ping -h db -u root --password=roothamid123 --silent; do
    sleep 2
done
echo "✓ BDD OK !"

# 1. Installer les dépendances PHP si besoin
if [ ! -d vendor ]; then
    composer install --no-interaction --optimize-autoloader
fi

# 2. INSTALLER LES ASSETS (La correction est ici)
echo "> Installation des assets importmap..."
php bin/console importmap:install

echo "> Creation schema de BDD..."
php bin/console doctrine:schema:update --force

echo "> Build assets..."
php bin/console assets:install public --no-interaction
php bin/console asset-map:compile
php bin/console cache:clear

echo "✓ Application prete !"
exec "$@"