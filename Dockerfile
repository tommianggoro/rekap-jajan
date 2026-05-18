FROM php:8.2-apache

# 1. Install ekstensi pdo dan mysqli untuk PHP native
RUN docker-php-ext-install pdo pdo_mysql mysqli

# 2. Copy seluruh file bot PHP Anda ke dalam server
COPY . /var/www/html/

# 3. Berikan izin akses folder
RUN chown -R www-data:www-data /var/www/html

# 4. Jalankan Apache
CMD ["apache2-foreground"]