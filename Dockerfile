FROM php:8.2-cli

# 1. Instalasi alat sistem & Node.js (untuk Tailwind)
RUN apt-get update && apt-get install -y libsqlite3-dev unzip git curl
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get install -y nodejs

# 2. Pasang Composer (PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Pindahkan semua file proyek
WORKDIR /app
COPY . .

# 4. Instal paket Laravel & Build UI Tailwind
RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN npm run build

# 5. JURUS RAHASIA: Jalankan Database, Worker AI, dan Web Server bersamaan!
CMD touch database/database.sqlite && \
    php artisan migrate --force && \
    (php artisan queue:work & php artisan serve --host=0.0.0.0 --port=${PORT:-10000})