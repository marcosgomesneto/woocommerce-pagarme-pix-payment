FROM wordpress

RUN apt-get update && apt-get install -y \
  wget \
  unzip

RUN wget https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN chmod +x wp-cli.phar 
RUN mv wp-cli.phar /usr/local/bin/wp

RUN echo "memory_limit = -1" > $PHP_INI_DIR/conf.d/custom.ini

RUN cd /var/www/html/wp-content/plugins \
  && wget https://downloads.wordpress.org/plugin/woocommerce.8.6.1.zip \
  && unzip woocommerce.8.6.1.zip