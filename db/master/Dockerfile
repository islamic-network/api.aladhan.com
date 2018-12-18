FROM percona:5.7

ENV MYSQL_USER "someUser"
ENV MYSQL_PASSWORD "somePassword"
ENV MYSQL_DATABASE "aladhan_locations"
ENV MYSQL_ROOT_PASSWORD "root"

EXPOSE 3306

COPY aladhan.cnf /etc/mysql/conf.d/aladhan.cnf

# Copy and Insert DB Backup
ADD 2018-11-26.sql.tar.gz /docker-entrypoint-initdb.d/

