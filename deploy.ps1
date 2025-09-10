# Windows Deployment Script for Church Management System
Write-Host "🚀 Starting deployment..." -ForegroundColor Green

# Function to check if a command exists
function Command-Exists {
    param($command)
    $exists = $null -ne (Get-Command $command -ErrorAction SilentlyContinue)
    return $exists
}

# Check for Composer
if (-not (Command-Exists "composer")) {
    Write-Host "❌ Composer not found. Please install Composer first." -ForegroundColor Red
    exit 1
}

# Check for Node.js and npm
if (-not (Command-Exists "node") -or -not (Command-Exists "npm")) {
    Write-Host "❌ Node.js and npm not found. Please install Node.js first." -ForegroundColor Red
    exit 1
}

# Set environment to production
$env:APP_ENV="production"
$env:APP_DEBUG="false"

# Install PHP dependencies
Write-Host "🔧 Installing PHP dependencies..." -ForegroundColor Cyan
composer install --optimize-autoloader --no-dev
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Composer install failed" -ForegroundColor Red
    exit 1
}

# Install Node.js dependencies
Write-Host "📦 Installing Node.js dependencies..." -ForegroundColor Cyan
npm install --production
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ npm install failed" -ForegroundColor Red
    exit 1
}

# Build assets
Write-Host "🎨 Building assets..." -ForegroundColor Cyan
npm run prod
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Asset compilation failed" -ForegroundColor Red
    exit 1
}

# Generate application key if not exists
if (-not (Test-Path .env)) {
    Write-Host "🔑 Generating .env file..." -ForegroundColor Cyan
    Copy-Item .env.example .env
    php artisan key:generate
}

# Cache configuration
Write-Host "⚡ Optimizing application..." -ForegroundColor Cyan
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
Write-Host "💾 Running database migrations..." -ForegroundColor Cyan
php artisan migrate --force

# Set proper permissions (Windows specific)
Write-Host "🔒 Setting file permissions..." -ForegroundColor Cyan
icacls . /grant "IIS_IUSRS:(OI)(CI)F" /T

Write-Host ""
Write-Host "✨ Deployment completed successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Configure your web server to point to the 'public' directory"
Write-Host "2. Set up your .env file with production settings"
Write-Host "3. Set up a task scheduler to run 'php artisan schedule:run' every minute"
Write-Host ""
Write-Host "For production, consider setting up:" -ForegroundColor Yellow
Write-Host "- Queue workers (Supervisor or Windows Task Scheduler)"
Write-Host "- Log rotation"
Write-Host "- Backup system"
Write-Host "- Monitoring"
Write-Host ""
