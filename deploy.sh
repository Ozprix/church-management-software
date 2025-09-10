#!/bin/bash

# Deployment Script for Church Management System
echo "🚀 Starting deployment..."

# Check for required commands
command -v php >/dev/null 2>&1 || { echo >&2 "❌ PHP is required but not installed. Aborting."; exit 1; }
command -v composer >/dev/null 2>&1 || { echo >&2 "❌ Composer is required but not installed. Aborting."; exit 1; }
command -v node >/dev/null 2>&1 || { echo >&2 "❌ Node.js is required but not installed. Aborting."; exit 1; }
command -v npm >/dev/null 2>&1 || { echo >&2 "❌ npm is required but not installed. Aborting."; exit 1; }

# Set environment
export APP_ENV=production
export APP_DEBUG=false

# Install PHP dependencies
echo "🔧 Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev
if [ $? -ne 0 ]; then
    echo "❌ Composer install failed"
    exit 1
fi

# Install Node.js dependencies
echo "📦 Installing Node.js dependencies..."
npm install --production
if [ $? -ne 0 ]; then
    echo "❌ npm install failed"
    exit 1
fi

# Build assets
echo "🎨 Building assets..."
npm run prod
if [ $? -ne 0 ]; then
    echo "❌ Asset compilation failed"
    exit 1
fi

# Generate application key if not exists
if [ ! -f ".env" ]; then
    echo "🔑 Generating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Cache configuration
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "💾 Running database migrations..."
php artisan migrate --force

# Set proper permissions
echo "🔒 Setting file permissions..."
chown -R www-data:www-data .
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 775 storage bootstrap/cache

# Restart queue workers if running
if [ -f "/etc/init.d/php8.1-fpm" ]; then
    echo "🔄 Restarting PHP-FPM..."
    sudo systemctl restart php8.1-fpm
fi

echo ""
echo "✨ Deployment completed successfully!"
echo ""
echo "Next steps:"
echo "1. Configure your web server to point to the 'public' directory"
echo "2. Set up your .env file with production settings"
echo "3. Set up a cron job for the scheduler: * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "For production, consider setting up:"
echo "- Queue workers (Supervisor recommended)"
echo "- Log rotation"
echo "- Backup system"
echo "- Monitoring"
echo ""
