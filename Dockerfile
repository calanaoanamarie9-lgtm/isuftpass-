FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_pgsql gd intl bcmath mbstring zip opcache \
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://nodejs.org/dist/v22.12.0/node-v22.12.0-linux-x64.tar.gz | tar -xz -C /usr/local --strip-components=1

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader --no-scripts

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .

RUN cp .env.example .env \
    && php artisan key:generate --force \
    && composer run-script post-autoload-dump \
    && npm run build \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data \
               storage/framework/testing storage/logs storage/app/public bootstrap/cache \
    && php artisan storage:link \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

# Migrations run here, not in a Render preDeploy step: this service was
# created by hand, so render.yaml - where preDeployCommand lives - is not
# applied to it, and nothing else in the image touches the schema. Running
# them before serve means the code that starts is the code that matches the
# database; a migration that fails stops the container instead of shipping a
# half-updated schema (the failure shows up in Render -> Logs).
CMD ["sh", "-c", "php artisan config:cache && php artisan view:cache && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
