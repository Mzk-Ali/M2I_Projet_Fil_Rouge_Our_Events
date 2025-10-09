#!/bin/bash
set -e

# Attendre que MySQL soit prêt
until mysqladmin ping -h"$DB_HOST" --silent; do
    echo "Waiting for database..."
    sleep 2
done

# Installer les dépendances
composer install --no-interaction

# Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Charger les fixtures
php bin/console doctrine:fixtures:load --no-interaction

# Lancer PHP-FPM
php-fpm

