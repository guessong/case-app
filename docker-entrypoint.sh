#!/bin/bash
set -e

echo "Waiting for MySQL..."
MIGRATED=false
for i in $(seq 1 30); do
    if php artisan migrate --force 2>/dev/null; then
        MIGRATED=true
        break
    fi
    echo "  Attempt $i - MySQL not ready, retrying in 3s..."
    sleep 3
done

if [ "$MIGRATED" = false ]; then
    echo "ERROR: Migration failed after 30 attempts"
    exit 1
fi

echo "Seeding database..."
php artisan db:seed --force

echo "Starting server at http://0.0.0.0:8000"
exec php artisan serve --host=0.0.0.0 --port=8000
