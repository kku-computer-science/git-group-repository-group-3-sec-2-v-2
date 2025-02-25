# ใช้ Base Image PHP 8 พร้อม Apache
FROM php:8.0-apache

# ติดตั้ง PHP Extensions และแพ็คเกจที่จำเป็น
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev \
    zip unzip curl git \
    && docker-php-ext-configure gd \
    && docker-php-ext-install -j$(nproc) gd mbstring exif pcntl bcmath mysqli pdo_mysql zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*


# เปิดใช้งาน mod_rewrite ของ Apache
RUN a2enmod rewrite

ENV TZ=Asia/Bangkok
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone


# ตั้งค่า ServerName ใน Apache เพื่อหลีกเลี่ยงข้อความเตือน
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# ติดตั้ง Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# ติดตั้ง Laravel Installer โดยใช้ Composer
RUN composer global require laravel/installer
ENV PATH="$PATH:/root/.composer/vendor/bin"

# กำหนด Directory การทำงาน
WORKDIR /var/www/html/InitialProject/src

# คัดลอกโค้ดโปรเจกต์ Laravel เข้าไปใน Docker
COPY . /var/www/html

# ตั้งค่า Permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# เปิดพอร์ต 80
EXPOSE 80

# Healthcheck
HEALTHCHECK --interval=30s --timeout=5s \
    CMD curl -f http://localhost/ || exit 1

# คำสั่งเริ่มต้น
CMD ["apache2-foreground"]
