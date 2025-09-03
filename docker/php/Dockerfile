FROM php:8.3-fpm

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip curl git \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install \
       pdo_mysql \
       mbstring \
       exif \
       pcntl \
       bcmath \
       gd \
       zip \
       intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Копируем composer файлы сначала (для кеша)
COPY composer.json composer.lock ./

# Устанавливаем зависимости (без dev на билде образа)
RUN composer install --no-dev --no-scripts --prefer-dist --no-progress --no-interaction

# Теперь копируем весь проект
COPY . .

# Повторный composer install с автозагрузкой
RUN composer install --optimize-autoloader
