# start with a standard php7+apache image
FROM php:7-apache

# use PHP's recommended configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# install and enable PHP's `mysqli` extension
RUN docker-php-ext-install mysqli
RUN docker-php-ext-enable mysqli

# set timezome
RUN ln -s -f /usr/share/zoneinfo/America/New_York /etc/localtime
RUN sed -i $PHP_INI_DIR/php.ini -e 's/^;date.timezone =$/date.timezone = "America\/New_York"/'

# copy sources
COPY repos/undefx/Automation/interface/ /var/www/html/

# use development credentials
COPY repos/undefx/Automation/dev/docker/web/assets/settings.php /var/www/html/
