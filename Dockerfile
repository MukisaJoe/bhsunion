FROM php:8.2-apache

RUN a2enmod rewrite

COPY api/ /var/www/html/api/

RUN chown -R www-data:www-data /var/www/html/api \
    && sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]
