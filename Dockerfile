FROM php:8.3-apache
RUN a2enmod headers \
 && sed -ri 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
 && echo 'ServerName localhost' > /etc/apache2/conf-enabled/servername.conf \
 && printf 'upload_max_filesize=12M\npost_max_size=64M\nmax_file_uploads=10\nmemory_limit=256M\ndate.timezone=Asia/Ho_Chi_Minh\nexpose_php=Off\n' > /usr/local/etc/php/conf.d/zz-app.ini
COPY . /var/www/html/
RUN rm -rf /var/www/html/.git /var/www/html/tests \
 && mkdir -p /var/www/html/data \
 && chown -R www-data:www-data /var/www/html/data
CMD sed -i "s/^Listen .*/Listen ${PORT:-80}/" /etc/apache2/ports.conf \
 && sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT:-80}>/" /etc/apache2/sites-enabled/000-default.conf \
 && exec apache2-foreground
