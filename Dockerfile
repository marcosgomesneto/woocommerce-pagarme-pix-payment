FROM wordpress

RUN apt-get update && apt-get install -y \
  wget \
  unzip

RUN wget https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN chmod +x wp-cli.phar 
RUN mv wp-cli.phar /usr/local/bin/wp

RUN echo "memory_limit = -1" > $PHP_INI_DIR/conf.d/custom.ini

RUN cd /var/www/html/wp-content/plugins \
  && wget https://downloads.wordpress.org/plugin/woocommerce.9.0.2.zip \
  && unzip woocommerce.9.0.2.zip

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN echo "zend_extension = xdebug" >> /usr/local/etc/php/conf.d/xdebug.ini \
  && echo "xdebug.mode = develop,debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
  && echo "xdebug.start_with_request = yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
  && echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini