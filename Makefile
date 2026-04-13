.PHONY: run init var yarn ci fix ecs bash static cache tests cache-warmup-test behat

MAKEFLAGS += --no-print-directory # to disable "make: Entering directory ..." messages

run: init
	@PORT=$$(docker compose port nginx 80 2>/dev/null | cut -d: -f2); \
	echo "App runs on http://localhost:$${PORT} http://localhost:$${PORT}/admin"

init:
	which docker > /dev/null || (echo "Please install docker binary" && exit 1)
	if command -v direnv >/dev/null; then \
		[ -f .envrc ] || cp .envrc.dist .envrc; \
		direnv allow; \
	fi
	docker compose up -d
	./bin-docker/composer update --no-interaction --no-plugins
	@make var
	./bin-docker/php ./bin/console doctrine:database:create --no-interaction --if-not-exists
	./bin-docker/php ./bin/console doctrine:schema:create --no-interaction || ./bin-docker/php ./bin/console doctrine:schema:update --force --no-interaction
	./bin-docker/php ./bin/console assets:install
	./bin-docker/yarn --cwd=tests/Application install
	./bin-docker/yarn --cwd=tests/Application build
	@make var

init-tests:
	which docker > /dev/null || (echo "Please install docker binary" && exit 1)
	if command -v direnv >/dev/null; then \
		[ -f .envrc ] || cp .envrc.dist .envrc; \
		direnv allow; \
	fi
	docker compose up -d
	./bin-docker/composer update --no-interaction --no-plugins
	rm -fr tests/Application/var/test
	@make var
	./bin-docker/php ./bin/console --env=test doctrine:database:drop --no-interaction --force --if-exists
	./bin-docker/php ./bin/console --env=test doctrine:database:create --no-interaction --if-not-exists
	./bin-docker/php ./bin/console --env=test doctrine:schema:create --no-interaction || ./bin-docker/php ./bin/console --env=test doctrine:schema:update --force --no-interaction
	./bin-docker/php ./bin/console --env=test assets:install
	./bin-docker/yarn --cwd=tests/Application install
	./bin-docker/yarn --cwd=tests/Application build
	@make var

cache:
	@make var
	./bin-docker/php ./bin/console cache:clear
	chmod -R 0777 tests/Application/var

static: static-only

static-only:
	@make phpstan
	@make composer-lint
	@make say-ok

phpstan:
	./bin-docker/docker-bash bin/phpstan.sh

phpunit:
	./bin-docker/php bin/phpunit

behat:
	./bin-docker/docker-bash bin/behat.sh

ecs:
	./bin-docker/docker-bash bin/ecs.sh

symfony-lint:
	./bin-docker/docker-bash bin/symfony-lint.sh

composer-lint:
	./bin-docker/composer validate --no-check-lock

lint: symfony-lint composer-lint

yarn-build:
	./bin-docker/yarn --cwd=tests/Application install
	./bin-docker/yarn --cwd=tests/Application build

yarn: yarn-build

schema-reset:
	./bin-docker/php ./bin/console doctrine:database:drop --force --if-exists
	./bin-docker/php ./bin/console doctrine:database:create --no-interaction
	./bin-docker/php ./bin/console doctrine:schema:create --no-interaction

fix:
	./bin-docker/docker-bash bin/ecs.sh --fix

bare-fixtures:
	@echo "############\nLoading fixtures\n############"
	./bin-docker/php ./bin/console sylius:fixtures:load --no-interaction

var:
	docker compose run --rm --user root php rm -fr tests/Application/var
	mkdir -p tests/Application/var/log
	mkdir -p tests/Application/public/media/image
	touch tests/Application/var/log/test.log
	touch tests/Application/var/log/dev.log
	chmod -R 0777 tests/Application/var
	docker compose run --rm --user root php chmod -R 0777 tests/Application/public/media

fixtures: schema-reset bare-fixtures var

cache-warmup-test:
	rm -fr tests/Application/var/cache/test
	./bin-docker/php ./bin/console --env=test cache:warmup

tests: static phpunit cache-warmup-test behat

ci: init-tests tests

say-ok:
	@echo "✅ OK ✅"

php-bash:
	./bin-docker/docker-bash

bash: php-bash
