#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(dirname "$DIR")"
cd "$ROOT"

# Matrix: Sylius x Symfony x composer strategy
# PHP version is defined by .docker/php/Dockerfile (8.3, matching highest in GitHub matrix)
SYLIUS_VERSIONS=("1.12" "1.13" "1.14")
SYMFONY_VERSIONS=("6.4")
COMPOSER_STRATEGIES=("prefer-dist" "prefer-lowest")

log() {
    echo ""
    echo "========================================"
    echo "$1"
    echo "========================================"
    echo ""
}

run_in_docker() {
    local user_id
    user_id=$(id -u)
    local group_id
    group_id=$(id -g)
    docker compose exec -T --user="${user_id}:${group_id}" php bash -c "$1"
}

cleanup_cache() {
    rm -fr tests/Application/var/cache
    docker compose run --rm --user root php rm -fr tests/Application/var/cache 2>/dev/null || true
    mkdir -p tests/Application/var/cache
    chmod -R 0777 tests/Application/var
}

require_versions() {
    local sylius_version="$1"
    local symfony_version="$2"

    # Require specific Sylius version
    run_in_docker "composer require 'sylius/sylius:${sylius_version}.*' --no-interaction --no-update --no-scripts"

    # Require specific Symfony version for packages in composer.json (same logic as GitHub CI)
    local symfony_packages
    symfony_packages=$(run_in_docker "grep -o -E '\"(symfony/[^\"]+)\"' composer.json" \
        | grep -v -E '(symfony/flex|symfony/webpack-encore-bundle|symfony/maker-bundle|symfony/panther|symfony/thanks)' \
        | xargs printf '%s:'"${symfony_version}"'.* ')
    run_in_docker "composer require ${symfony_packages} --no-interaction --no-update"

}

# Ensure docker is up
docker compose up -d --build

# Backup composer.json
cp composer.json composer.json.bak

trap 'cp composer.json.bak composer.json; rm -f composer.json.bak' EXIT

for sylius_version in "${SYLIUS_VERSIONS[@]}"; do
    for symfony_version in "${SYMFONY_VERSIONS[@]}"; do
        for strategy in "${COMPOSER_STRATEGIES[@]}"; do
            label="Sylius ${sylius_version} / Symfony ${symfony_version} / ${strategy}"

            log "TESTING: ${label}"

            # Restore original composer.json before each combination
            run_in_docker "cp composer.json.bak composer.json"

            # Remove lock file
            run_in_docker "rm -f composer.lock"

            # Require specific versions
            require_versions "$sylius_version" "$symfony_version"

            # Composer install
            composer_flag="--prefer-dist"
            if [ "$strategy" = "prefer-lowest" ]; then
                composer_flag="--prefer-lowest"
            fi
            run_in_docker "composer update --no-interaction ${composer_flag} --no-plugins"

            # Clean cache
            cleanup_cache

            # Setup database
            run_in_docker "cd tests/Application && php bin/console --env=test doctrine:database:drop --force --if-exists -vvv"
            run_in_docker "cd tests/Application && php bin/console --env=test doctrine:database:create -vvv"
            run_in_docker "cd tests/Application && php bin/console --env=test doctrine:schema:update --force --complete -vvv"

            # Assets
            run_in_docker "cd tests/Application && php bin/console --env=test assets:install -vvv"

            # Cache warmup
            run_in_docker "cd tests/Application && php bin/console --env=test cache:warmup -vvv"

            # JWT keypair
            run_in_docker "cd tests/Application && php bin/console --env=test lexik:jwt:generate-keypair --skip-if-exists --no-interaction"

            # PHPStan
            run_in_docker "bash bin/phpstan.sh"

            # PHPUnit
            run_in_docker "vendor/bin/phpunit"

            # Clear cache again before Behat (PHPStan's partial warmup can leave corrupted state)
            cleanup_cache
            run_in_docker "cd tests/Application && php bin/console --env=test cache:warmup -vvv"

            # Behat
            run_in_docker "APP_ENV=test vendor/bin/behat --no-interaction"

            log "PASSED: ${label}"
        done
    done
done

# Restore original state
cp composer.json.bak composer.json
rm -f composer.lock
./bin-docker/composer update --no-interaction --no-plugins

log "All combinations passed!"
