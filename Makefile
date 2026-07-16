up:
	docker compose up -d --build

down:
	docker compose down

restart:
	docker compose down
	docker compose up -d --build

logs:
	docker compose logs -f

backend:
	docker exec -it game-library-backend bash

frontend:
	docker exec -it game-library-frontend sh

mongo:
	docker exec -it game-library-mongodb mongosh

redis:
	docker exec -it game-library-redis redis-cli

ps:
	docker compose ps