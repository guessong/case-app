.PHONY: setup start stop test clean

# One command to rule them all - builds and starts the app
setup:
	docker compose up -d --build
	@echo ""
	@echo "✅ App is running at http://localhost:8000"
	@echo ""

# Start existing container
start:
	docker compose up -d
	@echo "App is running at http://localhost:8000"

# Stop the app
stop:
	docker compose down

# Run tests (locally, not in Docker)
test:
	php artisan test

# Clean everything
clean:
	docker compose down -v --rmi local
