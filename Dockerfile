FROM php:8.4-apache

# 1. Instalar dependencias del sistema y extensiones de PHP necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd

# 2. Habilitar mod_rewrite de Apache para rutas limpias de Laravel
RUN a2enmod rewrite

# 3. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Establecer directorio de trabajo y copiar el código fuente
WORKDIR /var/www/html
COPY . .

# 5. Crear el archivo de base de datos SQLite si no existe
RUN mkdir -p database && touch database/database.sqlite

# 6. Otorgar permisos de lectura y escritura al usuario Apache (www-data)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# 7. Apuntar el servidor Apache a la carpeta public/ de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 8. Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# 9. Ejecutar migraciones e inicio de Apache
EXPOSE 80
CMD ["sh", "-c", "php artisan migrate --force && apache2-foreground"]