# --- Stage 1: Build Frontend Assets ---
FROM node:20-alpine AS assets-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# --- Stage 2: Web Server Runtime ---
FROM richarvey/nginx-php-fpm:3.1.6
WORKDIR /var/www/html

# Copy project files
COPY . .

# Copy built CSS/JS assets from Node build stage
COPY --from=assets-builder /app/public/build ./public/build

# Image Environment Variables configured for Laravel
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr
ENV COMPOSER_ALLOW_SUPERUSER 1

# Automatically run composer install on startup (which also optimizes autoloader in production)
ENV SKIP_COMPOSER 0

# Create the startup deploy script inside the Linux build container to avoid CRLF line-ending / shell errors on Windows host.
RUN mkdir -p /var/www/html/scripts && \
    echo '#!/usr/bin/env bash' > /var/www/html/scripts/00-laravel-deploy.sh && \
    echo 'echo "Caching configuration..."' >> /var/www/html/scripts/00-laravel-deploy.sh && \
    echo 'php artisan config:cache' >> /var/www/html/scripts/00-laravel-deploy.sh && \
    echo 'echo "Caching routes..."' >> /var/www/html/scripts/00-laravel-deploy.sh && \
    echo 'php artisan route:cache' >> /var/www/html/scripts/00-laravel-deploy.sh && \
    echo 'echo "Caching views..."' >> /var/www/html/scripts/00-laravel-deploy.sh && \
    echo 'php artisan view:cache' >> /var/www/html/scripts/00-laravel-deploy.sh && \
    echo 'echo "Running migrations..."' >> /var/www/html/scripts/00-laravel-deploy.sh && \
    echo 'php artisan migrate --force' >> /var/www/html/scripts/00-laravel-deploy.sh && \
    chmod +x /var/www/html/scripts/00-laravel-deploy.sh

CMD ["/start.sh"]
