init:
	if ! [ -f .env ]; then cp .env.example .env; fi
	docker compose -f docker-compose.local.yml up -d
	docker compose exec php composer install
	docker compose exec php php artisan key:generate
