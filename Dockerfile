FROM dunglas/frankenphp:1-php8.3 AS frankenphp_dev

WORKDIR /app

VOLUME /app/var/

RUN apt-get update && apt-get install -y --no-install-recommends \
	acl \
	file \
	gettext \
	git \
	&& rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    apt-get update && apt-get install -y \
            librdkafka-dev \
	&& install-php-extensions \
		@composer \
		apcu \
		intl \
		opcache \
		zip \
        xdebug \
        pdo_pgsql \
        pcntl \
        sockets \
        redis \
        rdkafka \
	;


ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"
ENV APP_ENV=dev

COPY --link frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --link frankenphp/conf.d/20-app.dev.ini $PHP_INI_DIR/app.conf.d/
COPY --link --chmod=755 frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link frankenphp/Caddyfile /etc/caddy/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1
CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--watch" ]