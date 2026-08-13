#!/bin/sh
set -e

if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] Installation des dépendances PHP (composer install)…"
    composer install --no-interaction --prefer-dist
fi

# .env est monté directement depuis docker/.env.docker (voir
# docker-compose.yml) : entièrement pré-rempli pour le réseau Docker, donc
# rien à générer ni à fusionner ici. Ce fichier est distinct de
# backend/.env (utilisé par un lancement natif hors Docker) — l'un ne
# touche jamais l'autre.

if [ -n "$DB_HOST" ]; then
    echo "[entrypoint] Attente de PostgreSQL (${DB_HOST}:${DB_PORT:-5432})…"
    until pg_isready -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-postgres}" >/dev/null 2>&1; do
        sleep 1
    done
    echo "[entrypoint] PostgreSQL disponible."
fi

exec "$@"
