FROM php:8.2-apache

# 1. Aktifkan modul rewrite Apache (penting untuk routing)
RUN a2enmod rewrite

# 2. Copy semua file project ke folder root server Apache
COPY . /var/www/html/

# Pastikan juga folder storage yang dipakai PHP (tanpa database) punya izin tulis
RUN mkdir -p /var/www/html/public/app/storage && chown -R www-data:www-data /var/www/html/public/app/storage && chmod -R 775 /var/www/html/public/app/storage


# 3. Berikan izin akses baca-tulis penuh agar file JSON bisa dimuat/ditulis
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 4. Pastikan Apache mengizinkan pembacaan file .htaccess untuk routing jika ada
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

