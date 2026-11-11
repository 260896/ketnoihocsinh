# Bản chạy thử công khai của KẾT NỐI HỌC SINH (PHP thuần + JSON, không cơ sở dữ liệu).
FROM php:8.3-apache

# Cho phép .htaccess chặn data/, includes/, nguon/… và gửi header noindex
RUN a2enmod headers \
 && sed -ri 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
 && echo 'ServerName localhost' > /etc/apache2/conf-enabled/servername.conf

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-ketnoihocsinh.ini
COPY . /var/www/html/

# Dữ liệu chạy được tạo lại từ includes/seed.php ở lượt truy cập đầu tiên
RUN rm -rf /var/www/html/docker \
 && mkdir -p /var/www/html/data \
 && chown -R www-data:www-data /var/www/html/data

# Render cấp cổng qua biến PORT
CMD sed -i "s/^Listen .*/Listen ${PORT:-80}/" /etc/apache2/ports.conf \
 && sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT:-80}>/" /etc/apache2/sites-enabled/000-default.conf \
 && exec apache2-foreground
