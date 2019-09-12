FROM quay.io/vesica/php72:latest

# Copy files
RUN cd ../ && rm -rf /var/www/html
COPY . /var/www/
COPY etc/apache2/mods-enabled/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

# Run Composer
RUN cd /var/www && composer install --no-dev

# Delete stuff we do not need
RUN rm -rf /var/www/db
RUN rm -rf /var/www/.git
RUN rm -rf /var/www/.gitignore
RUN rm -rf /var/www/build.sh
RUN rm -rf /var/www/.idea

RUN chown -R www-data:www-data /var/www/
ENV MYSQL_USER "aladhan"
ENV MYSQL_PASSWORD "aladhan"
ENV MYSQL_DATABASE "aladhan_locations"
ENV MYSQL_HOST_1 "db1"
ENV MYSQL_HOST_2 "db2"
ENV MYSQL_HOST_3 "db3"
ENV MEMCACHED_HOST "host"
ENV MEMCACHED_PORT "port"
ENV GOOGLE_API_KEY "key"
ENV ASKGEO_ACCOUNT_ID "account"
ENV ASKGEO_API_KEY "key"
ENV ASKGEO_FORMAT "obj"
ENV ASKGEO_SECURE "true"
# 0 = disabled. 1 = enabled
ENV WAF_PROXY_MODE "0"
ENV WAF_KEY "someKey"
ENV LOAD_BALANCER_MODE "0"
ENV LOAD_BALANCER_KEY "KEY"
