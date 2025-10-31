#!/bin/bash

###############################################################################
# Buses - Dreamscape Deployment Script
# Version: 1.0.0
# Date: October 28, 2025
###############################################################################

set -e  # Exit on error

echo "========================================="
echo "Buses - Dreamscape Deployment Script"
echo "========================================="
echo ""

# Configuration
APP_PATH="/home/username/public_html/buses"
ENV_FILE=".env"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Functions
success() {
    echo -e "${GREEN}✓ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

error() {
    echo -e "${RED}✗ $1${NC}"
    exit 1
}

# Check if running in correct directory
if [ ! -f "artisan" ]; then
    error "This script must be run from the Laravel root directory"
fi

echo "Step 1: Checking environment..."
if [ ! -f "$ENV_FILE" ]; then
    warning ".env file not found. Copying from .env.example..."
    cp .env.example .env
    echo "Please configure .env file before continuing."
    exit 1
fi
success "Environment file found"

echo ""
echo "Step 2: Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction || error "Composer install failed"
success "Composer dependencies installed"

echo ""
echo "Step 3: Generating application key..."
if grep -q "APP_KEY=\$" .env; then
    php artisan key:generate --force || error "Key generation failed"
    success "Application key generated"
else
    success "Application key already set"
fi

echo ""
echo "Step 4: Setting up storage..."
chmod -R 775 storage bootstrap/cache || error "Failed to set permissions"
php artisan storage:link || warning "Storage link may already exist"
success "Storage configured"

echo ""
echo "Step 5: Running database migrations..."
read -p "Run migrations? (yes/no): " run_migrations
if [ "$run_migrations" = "yes" ]; then
    php artisan migrate --force || error "Migration failed"
    success "Database migrated"
else
    warning "Skipped migrations"
fi

echo ""
echo "Step 6: Clearing and caching..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
success "Caches optimized"

echo ""
echo "Step 7: Building frontend assets..."
if command -v npm &> /dev/null; then
    npm install
    npm run build
    success "Frontend assets built"
else
    warning "npm not found - skipping frontend build"
fi

echo ""
echo "Step 8: Final permissions check..."
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || warning "Could not change ownership (may need sudo)"
chmod -R 755 storage bootstrap/cache
success "Permissions set"

echo ""
echo "========================================="
echo "Deployment Complete!"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Verify .env configuration (database, email, app URL)"
echo "2. Test the application: ${APP_URL}"
echo "3. Create admin user if needed"
echo "4. Setup cron job for scheduled tasks"
echo ""
echo "Cron job to add:"
echo "* * * * * cd ${APP_PATH} && php artisan schedule:run >> /dev/null 2>&1"
echo ""
success "Done!"



