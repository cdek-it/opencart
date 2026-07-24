FROM php:8.4-apache

ARG OPENCART_VERSION=3.0.5.0
ARG RU_LANGUAGE_PACK_VERSION=3.0.4.0.14

RUN apt-get update && apt-get install -y \
      $PHPIZE_DEPS \
      libzip-dev libpng-dev libjpeg-dev \
      unzip curl default-mysql-client && \
    docker-php-ext-configure gd --with-jpeg && \
    docker-php-ext-install -j$(nproc) zip gd mysqli pdo_mysql && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

COPY docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
# доступен на запись всем: в этот лог пишет и root (composer, запускается из
# entrypoint), и www-data (apache) - кто бы ни создал файл первым, не должен
# заблокировать запись для другого.
RUN touch /tmp/xdebug.log && chmod 666 /tmp/xdebug.log

# Скачиваем свежую копию ядра OpenCart при каждой сборке образа.
# system/storage переносится за пределы document root (/var/www/html) по
# рекомендации безопасности самого OpenCart - там лежат логи, сессии, кэш
# и загрузки, ничего из этого не должно быть напрямую доступно из веба.
RUN curl -fsSL "https://github.com/opencart/opencart/releases/download/${OPENCART_VERSION}/opencart-${OPENCART_VERSION}.zip" -o /tmp/opencart.zip && \
    unzip -q /tmp/opencart.zip -d /tmp/opencart && \
    rm -rf /var/www/html && \
    mv /tmp/opencart/upload /var/www/html && \
    rm -rf /tmp/opencart /tmp/opencart.zip && \
    mv /var/www/html/system/storage /var/www/oc-storage && \
    chown -R www-data:www-data /var/www/html /var/www/oc-storage && \
    chmod -R 777 /var/www/oc-storage /var/www/html/image

# Русский языковой пакет интерфейса (admin + catalog) из релиза community-проекта
# ruOpenCart/opencart-language-russian-30, соответствующего нашей версии ядра.
# Используются только файлы перевода - вложенный в пакет opencart.sql - это
# полный дамп демо-данных, который нам не нужен, поэтому он отбрасывается.
RUN curl -fsSL "https://github.com/ruOpenCart/opencart-language-russian-30/releases/download/${RU_LANGUAGE_PACK_VERSION}/opencart-language-russian-${RU_LANGUAGE_PACK_VERSION}.ocmod.zip" -o /tmp/ru-lang.zip && \
    unzip -q /tmp/ru-lang.zip -d /tmp/ru-lang && \
    cp -r /tmp/ru-lang/upload/admin/language/ru-ru /var/www/html/admin/language/ru-ru && \
    cp -r /tmp/ru-lang/upload/catalog/language/ru-ru /var/www/html/catalog/language/ru-ru && \
    rm -rf /tmp/ru-lang /tmp/ru-lang.zip && \
    chown -R www-data:www-data /var/www/html/admin/language/ru-ru /var/www/html/catalog/language/ru-ru

COPY docker/entrypoint.sh /usr/local/bin/oc-entrypoint.sh
RUN chmod +x /usr/local/bin/oc-entrypoint.sh

ENTRYPOINT ["oc-entrypoint.sh"]
CMD ["apache2-foreground"]
