#!/usr/bin/env bash
#
# Déploiement d'une nouvelle version sur le serveur.
# À lancer depuis le dossier de l'application, sur le serveur :
#
#   ./deploy.sh
#
# Le script s'arrête à la première erreur et remet le site en ligne même
# s'il échoue, pour ne jamais laisser la page de maintenance bloquée.

set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR"

remettre_en_ligne() {
    php artisan up || true
}
trap remettre_en_ligne EXIT

echo "→ Mise en maintenance"
php artisan down --render="errors::503" --retry=15

echo "→ Récupération du code"
git pull --ff-only

echo "→ Dépendances PHP (sans les outils de développement)"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "→ Migrations"
php artisan migrate --force

echo "→ Compilation des assets"
npm ci
npm run build

echo "→ Mise en cache de la configuration"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "→ Remise en ligne"
# Le trap s'en charge, mais on le fait explicitement pour le message.
php artisan up
trap - EXIT

echo "✓ Déploiement terminé"
