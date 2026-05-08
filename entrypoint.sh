#!/bin/sh

cd /valori

echo "> En attente de la BDD..."
until mysqladmin ping -h db -u root --password=roothamid123 --silent; do
    echo "BDD pas encore prête..."
    sleep 2
done
echo "✓ BDD OK !"

# On lance les commandes une par une, sans 'set -e' pour que le script continue quoi qu'il arrive
echo "> Commandes Symfony..."
php bin/console doctrine:schema:update --force --no-interaction
php bin/console assets:install public --no-interaction
php bin/console cache:clear

echo "✓ Tentative de démarrage final..."
exec "$@"