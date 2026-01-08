#!/bin/sh
set -e

echo "========================================"
echo "Starting Symfony Application Setup"
echo "========================================"

# Wait for database to be ready
echo "Waiting for database connection..."
until php bin/console dbal:run-sql "SELECT 1" > /dev/null 2>&1; do
  echo "Database is unavailable - sleeping"
  sleep 2
done
echo "✓ Database is ready!"

# Create log directories for supervisor
mkdir -p /var/log/supervisor
chown -R www-data:www-data /var/log/supervisor

# Create required Symfony directories
echo "Creating required directories..."
mkdir -p var/cache var/log public/uploads
chown -R www-data:www-data var/cache var/log public/uploads
echo "✓ Directories created"

# Clear and warm up cache
echo "Clearing and warming up cache..."
php bin/console cache:clear --no-warmup --env=prod
php bin/console cache:warmup --env=prod
echo "✓ Cache ready"

# Run database migrations (only if needed)
echo "Checking database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=prod || true
echo "✓ Database migrations complete"

# Set proper permissions
echo "Setting final permissions..."
chown -R www-data:www-data var/cache var/log
chmod -R 775 var/cache var/log
echo "✓ Permissions set"

echo "========================================"
echo "Starting Supervisor (Web + Workers)"
echo "========================================"

# Start supervisor which will manage:
# - PHP-FPM
# - Nginx
# - Messenger Workers (Email sending)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
