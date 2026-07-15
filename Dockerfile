# Force Render rebuild trigger
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

# Copy project files (includes conf/nginx/nginx-site.conf which richarvey/nginx-php-fpm
# auto-detects at startup to configure proper try_files for Laravel routing)
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

# Run composer install during image build (utilizing higher RAM constraints on Render's build servers)
ENV APP_KEY base64:hJ6vF1yId3Pi8HfYThadryS8bfDI0ZtLYH/DMsPTsc0=
RUN composer install --no-dev --optimize-autoloader

# Skip composer install on container startup
ENV SKIP_COMPOSER 1

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
