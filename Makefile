up:
	@echo "Starting up the application..."
	@docker compose up -d

build-app:
	@echo "Building the application..."
	docker compose build
