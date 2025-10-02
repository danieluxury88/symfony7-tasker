#!/bin/bash

# Pardux Task Manager - Development Installation Script
# This script will install and configure the Symfony project for development

set -e  # Exit on any error

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "\n${BLUE}============================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}============================================${NC}\n"
}

# Check if script is run from project root
if [ ! -f "composer.json" ]; then
    print_error "Please run this script from the project root directory"
    exit 1
fi

print_header "PARDUX TASK MANAGER - DEVELOPMENT SETUP"

# Check system requirements
print_status "Checking system requirements..."

# Check PHP version
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed. Please install PHP 8.2 or higher."
    exit 1
fi

PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
if [[ $(echo "$PHP_VERSION < 8.2" | bc -l) ]]; then
    print_error "PHP 8.2 or higher is required. Current version: $PHP_VERSION"
    exit 1
fi
print_success "PHP version $PHP_VERSION is compatible"

# Check Composer
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed. Please install Composer first."
    exit 1
fi
print_success "Composer is available"

# Check Symfony CLI (optional but recommended)
if ! command -v symfony &> /dev/null; then
    print_warning "Symfony CLI not found. It's recommended for development."
    print_status "You can install it from: https://symfony.com/download"
else
    print_success "Symfony CLI is available"
fi

# Install dependencies
print_header "INSTALLING DEPENDENCIES"

print_status "Installing Composer dependencies..."
composer install --dev --no-interaction --prefer-dist --optimize-autoloader

if [ $? -eq 0 ]; then
    print_success "Dependencies installed successfully"
else
    print_error "Failed to install dependencies"
    exit 1
fi

# Setup environment
print_header "ENVIRONMENT CONFIGURATION"

print_status "Setting up development environment..."

# Create .env.local if it doesn't exist
if [ ! -f ".env.local" ]; then
    print_status "Creating .env.local for local overrides..."
    cat > .env.local << EOL
# Local environment overrides
APP_ENV=dev
APP_DEBUG=true

# Database configuration (SQLite for development)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_dev.db"

# Mailer configuration for development
MAILER_DSN=null://null
EOL
    print_success ".env.local created"
else
    print_status ".env.local already exists, skipping..."
fi

# Database setup
print_header "DATABASE SETUP"

print_status "Setting up database..."

# Create database directory if it doesn't exist
mkdir -p var

# Create/update database schema
print_status "Creating database schema..."
if command -v symfony &> /dev/null; then
    symfony console doctrine:database:create --if-not-exists --no-interaction
    symfony console doctrine:migrations:migrate --no-interaction
else
    php bin/console doctrine:database:create --if-not-exists --no-interaction
    php bin/console doctrine:migrations:migrate --no-interaction
fi

if [ $? -eq 0 ]; then
    print_success "Database schema created/updated"
else
    print_error "Failed to setup database"
    exit 1
fi

# Load fixtures
print_status "Loading development fixtures..."
if command -v symfony &> /dev/null; then
    symfony console doctrine:fixtures:load --no-interaction
else
    php bin/console doctrine:fixtures:load --no-interaction
fi

if [ $? -eq 0 ]; then
    print_success "Development fixtures loaded"
else
    print_warning "Failed to load fixtures (this is optional)"
fi

# Asset setup
print_header "ASSET CONFIGURATION"

print_status "Installing and building assets..."
if command -v symfony &> /dev/null; then
    symfony console importmap:install
    symfony console asset-map:compile
else
    php bin/console importmap:install
    php bin/console asset-map:compile
fi

print_success "Assets configured"

# Cache setup
print_header "CACHE CONFIGURATION"

print_status "Warming up cache..."
if command -v symfony &> /dev/null; then
    symfony console cache:warmup
else
    php bin/console cache:warmup
fi

print_success "Cache warmed up"

# Permissions setup
print_status "Setting up file permissions..."
chmod -R 755 var/
chmod -R 755 public/assets/

print_success "File permissions configured"

# Run tests
print_header "RUNNING TESTS"

print_status "Running PHPUnit tests..."
if [ -f "bin/phpunit" ]; then
    ./bin/phpunit --no-coverage
    if [ $? -eq 0 ]; then
        print_success "All tests passed"
    else
        print_warning "Some tests failed (this might be expected during development)"
    fi
else
    print_warning "PHPUnit not found, skipping tests"
fi
