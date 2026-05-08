#!/bin/sh
set -e

cd /valori

echo "> En attente de la BDD..."
until mysqladmin ping -h db -u root --password=roothamid123 --silent; do
    sleep 2
done
echo "✓ BDD OK !"

# Update Database
echo "> Mise à jour BDD..."
php bin/console doctrine:schema:update --force --no-interaction

# Assets
echo "> Préparation des assets..."
php bin/console assets:install public --no-interaction

# --- ON COMMENTE LA LIGNE SUIVANTE ---
# php bin/console asset-map:compile
# -------------------------------------

echo "✓ Symfony prêt ! Démarrage du serveur..."
exec "$@"