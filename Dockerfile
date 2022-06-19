FROM wordpress

RUN apt-get update && apt-get install -y \
  wget \
  unzip

RUN cd /var/www/html/wp-content/plugins \
  && wget https://downloads.wordpress.org/plugin/woocommerce.6.6.0.zip \
  && unzip woocommerce.6.6.0.zip