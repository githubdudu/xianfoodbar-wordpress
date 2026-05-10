FROM wordpress:php8.1-apache

ARG VERSION=1.0.0

LABEL org.opencontainers.image.title="XianFoodBar WordPress" \
      org.opencontainers.image.description="WordPress PHP8.1 + Apache with Composer 2 for XianFoodBar" \
      org.opencontainers.image.version="${VERSION}" \
      org.opencontainers.image.authors="dudu_win10"

COPY --from=composer/composer:2-bin /composer /usr/bin/composer
