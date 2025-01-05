build:
	docker compose build

up:
	docker compose up -d

test:
	docker compose run app composer install --prefer-dist --no-scripts --no-progress --no-interaction
	docker compose run app vendor/bin/phpunit --testdox

down:
	docker compose down
