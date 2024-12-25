## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	docker compose build --no-cache

up: ## Start the docker hub in detached mode (no logs)
	docker compose up --detach

down: ## Stop the docker hub
	docker compose down --remove-orphans

init: build up wait symfony-migrate-silent symfony-seed-silent symfony-generate-api-keys-silent
reload: down up
rebuild: down build up

wait:
	@echo "Waiting for a few seconds to ensure DB is ready..."
	@sleep 10

## —— Php container ————————————————————————————————————————————————————————————————
sh: ## Connect to the FrankenPHP container
	docker compose exec php sh

bash: ## Connect to the FrankenPHP container via bash so up and down arrows go to previous commands
	docker compose exec php bash

own: ## Own newly generated files
	docker compose exec php chown -R $$(id -u):$$(id -g) .

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Example: make composer arg="-v"
	@$(eval arg ?=)
	docker compose exec php composer $(arg)

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
symfony: ## Example: make symfony arg="-V"
	@$(eval arg ?=)
	docker compose exec php php bin/console $(arg)

## —— Tests
php-unit:
	@$(eval test ?=)
	docker-compose exec php php vendor/bin/phpunit $(test) --testdox --colors=always

php-cs:
	@$(eval file ?=)
	docker-compose exec php php vendor/bin/php-cs-fixer fix $(file) --dry-run --diff

php-cs-fix:
	@$(eval file ?=)
	docker-compose exec php php vendor/bin/php-cs-fixer fix $(file) --diff

## —— Utilities
symfony-list:
	docker compose exec php php bin/console list

symfony-migrate-silent:
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction;

symfony-seed-silent:
	docker compose exec php php bin/console doctrine:fixtures:load --no-interaction;

symfony-generate-api-keys-silent:
	docker compose exec php php bin/console lexik:jwt:generate-keypair --overwrite --no-interaction
