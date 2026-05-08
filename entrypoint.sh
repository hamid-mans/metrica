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

# Assets - On tente l'install et le build, mais on ne coupe pas tout si ça échoue
echo "> Préparation des assets..."
php bin/console importmap:install || echo "⚠️ Warning: importmap install failed"
php bin/console assets:install public --no-interaction || echo "⚠️ Warning: assets install failed"

# Supprime la ligne asset-map:compile pour le moment,
# elle n'est pas indispensable en développement local.

echo "✓ Symfony prêt (Démarrage PHP-FPM)"
exec "$@"