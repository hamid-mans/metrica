#!/bin/sh
set -e

echo "> En attente de la BDD..."
until mysql -h db -uhm -phamid123 --ssl-verify-server-cert=false -e "SELECT 1;" >/dev/null 2>&1; do
    echo "Erreur connexion BDD (entrypoint)..."
    echo "V2"
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
