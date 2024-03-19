FROM wordpress

RUN apt-get update && apt-get install -y \
  wget \
  unzip

RUN cd /var/www/html/wp-content/plugins \
  && wget https://downloads.wordpress.org/plugin/woocommerce.8.6.1.zip \
  && unzip woocommerce.8.6.1.zip