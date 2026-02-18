# ---------- 1) Build Vite ----------
FROM node:20-alpine AS build
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# ---------- 2) Apache + PHP ----------
FROM php:8.2-apache

RUN a2enmod headers rewrite

# Install MySQL extension
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Install MongoDB extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy built frontend
COPY --from=build /app/dist /var/www/html

# Copy backend
COPY backend /var/www/html/backend

# Expose API under /api
RUN ln -s /var/www/html/backend/api /var/www/html/api

# Install PHP dependencies if composer.json exists
WORKDIR /var/www/html
COPY composer.json composer.lock* /var/www/html/
RUN if [ -f composer.json ]; then composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader; fi

# Make Apache listen on Render PORT
EXPOSE 10000
CMD ["sh", "-c", "sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf && sed -i 's/<VirtualHost \\*:80>/<VirtualHost \\*:${PORT}>/' /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
