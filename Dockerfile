# Runs the full PHP site (logins, forms, email) on hosts like Render or Railway.
# The built React files in assets/ are committed, so no Node build is needed.
FROM php:8.3-cli

# GD (with WebP/AVIF) and EXIF resize Gallery photos and strip their GPS data
RUN apt-get update \
 && apt-get install -y --no-install-recommends libjpeg62-turbo-dev libpng-dev libwebp-dev libavif-dev \
 && docker-php-ext-configure gd --with-jpeg --with-webp --with-avif \
 && docker-php-ext-install -j"$(nproc)" gd exif \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . /app

ENV PORT=8080
EXPOSE 8080
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} router.php"]
