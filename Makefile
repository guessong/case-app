.PHONY: setup start stop test clean

# Find available port starting from a given port
define find_port
$(shell port=$(1); while lsof -ti:$$port >/dev/null 2>&1; do port=$$((port + 1)); done; echo $$port)
endef

APP_PORT ?= $(call find_port,8000)
DB_PORT ?= $(call find_port,3306)

# One command to rule them all - builds and starts the app
setup:
	APP_PORT=$(APP_PORT) DB_PORT=$(DB_PORT) docker compose up -d --build
	@echo ""
	@echo "  App is running at http://localhost:$(APP_PORT)"
	@echo ""

# Start existing container
start:
	APP_PORT=$(APP_PORT) DB_PORT=$(DB_PORT) docker compose up -d
	@echo "App is running at http://localhost:$(APP_PORT)"

# Stop the app
stop:
	docker compose down

# Run tests (locally, not in Docker)
test:
	php artisan test

# Clean everything
clean:
	docker compose down -v --rmi local
