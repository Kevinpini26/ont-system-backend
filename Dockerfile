# Image de développement local — pas destinée telle quelle à la production
# (pas de multi-stage, pas d'opcache précompilé, exécution via
# `artisan serve`). Voir SECURITY.md pour les recommandations de déploiement.
FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        postgresql-client \
    && docker-php-ext-install pdo_pgsql pgsql zip intl bcmath \
    && rm -rf /var/lib/apt/lists/*
# Volontairement PAS d'extension gd/imagick : le serveur de production visé
# n'en dispose pas non plus, d'où le choix du rendu QR code en SVG (voir
# Modules/Stagiaires/app/Support/DompdfAttestationGenerator.php). Une image
# de dev qui aurait gd fausserait ce test de parité.

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
