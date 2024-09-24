FROM php:7.4-apache

# Instala extensões PHP necessárias e ferramentas adicionais
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configura o DocumentRoot do Apache
WORKDIR /var/www/html

# Copia o projeto para o contêiner
COPY . /var/www/html/

# Ajusta as permissões do diretório de trabalho
RUN chown -R www-data:www-data /var/www/html

# Define a variável de ambiente para permitir que o Composer rode como root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Instala dependências do Composer
RUN composer install

# Configurações adicionais de PHP
RUN echo "upload_max_filesize = 10M\npost_max_size = 10M\nmax_execution_time = 300\nmax_file_uploads = 50" > /usr/local/etc/php/conf.d/uploads.ini

# Exponha a porta 80
EXPOSE 80

# Comando para iniciar o Apache
CMD ["apache2-foreground"]