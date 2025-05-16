FROM php:8.4-fpm
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get install -y curl git nano unzip
RUN apt-get install -y zlib1g-dev libzip-dev libpq-dev libpng-dev
RUN apt-get clean

RUN docker-php-ext-install pgsql mysqli bcmath gd zip
RUN docker-php-ext-enable pgsql mysqli bcmath gd zip

RUN echo 'export PATH=$PATH:/root/.local/bin' >> ~/.bashrc
RUN echo 'export PATH=$PATH:/root/.composer/vendor/bin' >> ~/.bashrc

RUN curl -s https://ohmyposh.dev/install.sh | bash -s

RUN echo 'eval "$(oh-my-posh init bash --config /root/.cache/oh-my-posh/themes/slimfat.omp.json)"' >> ~/.bashrc

ENV NODE_VERSION=23.2.0
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh | bash
ENV NVM_DIR="/root/.nvm"
RUN . "$NVM_DIR/nvm.sh" && nvm install ${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm use v${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm alias default v${NODE_VERSION}
ENV PATH="${PATH}:/root/.nvm/versions/node/v${NODE_VERSION}/bin/"
RUN node --version
RUN npm --version

ENV PHP_CS_FIXER_IGNORE_ENV=1

ARG APP_DEBUG

RUN if [ "$APP_DEBUG" = "true" ]; then \
    pecl install xdebug \
	&& docker-php-ext-enable xdebug; fi