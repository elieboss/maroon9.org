# Runs the full PHP site (logins, forms, email) on hosts like Render or Railway.
# The built React files in assets/ are committed, so no Node build is needed.
FROM php:8.3-cli

WORKDIR /app
COPY . /app

ENV PORT=8080
EXPOSE 8080
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} router.php"]
