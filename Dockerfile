FROM vesica/php72:latest

# Copy files
RUN cd ../ && rm -rf /var/www/html
COPY . /var/www/
COPY /etc/apache2/mods-enabled/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

# Run Composer
RUN cd /var/www && composer install --no-dev

# Delete stuff we do not need
RUN rm -rf /var/www/db

RUN chown -R www-data:www-data /var/www/
ENV MYSQL_USER "aladhan"
ENV MYSQL_PASSWORD "aladhan"
ENV MYSQL_DATABASE "aladhan_locations"
ENV MYSQL_HOST "db1"
ENV MYSQL_SLAVE_USER "aladhan"
ENV MYSQL_SLAVE_PASSWORD "aladhan"
ENV MYSQL_SLAVE_DATABASE "aladhan_locations"
ENV MYSQL_SLAVE_HOST "db2"
ENV MEMCACHED_HOST "host"
ENV MEMCACHED_PORT "port"
ENV GOOGLE_API_KEY "key"
ENV ASKGEO_ACCOUNT_ID "account"
ENV ASKGEO_API_KEY "key"
