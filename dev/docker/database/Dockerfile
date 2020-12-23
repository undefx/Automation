# start with a standard mariadb image
FROM mariadb

# set timezome
RUN ln -s -f /usr/share/zoneinfo/America/New_York /etc/localtime

# specify a development-only password for the database user "root"
ENV MYSQL_ROOT_PASSWORD pass

# create the `automation` database
ENV MYSQL_DATABASE automation

# create the `auto` user account with a development-only password
ENV MYSQL_USER user
ENV MYSQL_PASSWORD pass

# provide DDL which will create empty tables at container startup
COPY repos/undefx/Automation/database/*.sql /docker-entrypoint-initdb.d/
