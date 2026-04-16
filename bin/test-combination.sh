#!/usr/bin/env bash
# Runs a single Sylius x Symfony x strategy combination inside the php container.
# Invoked by bin/test-matrix.sh.
#
# Arguments:
#   $1 = sylius_version (e.g. "1.9")
#   $2 = symfony_version (e.g. "4.4")
#   $3 = strategy ("prefer-dist" | "prefer-lowest")
#
# Expects /srv/sylius/composer.json.bak to exist (pristine composer.json).
set -euo pipefail
IFS=$'\n\t'

sylius_version="$1"
symfony_version="$2"
strategy="$3"

cd /srv/sylius

# Restore original composer.json and remove lock file
cp composer.json.bak composer.json
rm -f composer.lock

# Require specific Sylius version
composer require "sylius/sylius:${sylius_version}.*" --no-interaction --no-update --no-scripts

# Require specific Symfony version for packages in composer.json
# (excluding packages that are not actual Symfony components or have independent versioning)
grep -o -E '"(symfony/[^"]+)"' composer.json \
    | grep -v -E '(symfony/flex|symfony/webpack-encore-bundle|symfony/maker-bundle|symfony/panther|symfony/thanks|symfony/web-server-bundle)' \
    | xargs printf '%s:'"${symfony_version}"'.* ' \
    | xargs composer require --no-interaction --no-update

# Composer install
composer_flag="--prefer-dist"
if [ "$strategy" = "prefer-lowest" ]; then
    composer_flag="--prefer-lowest"
fi
composer update --no-interaction "${composer_flag}" --no-plugins

# Clean cache
rm -fr tests/Application/var/cache
mkdir -p tests/Application/var/cache

# Setup database
(cd tests/Application && php bin/console --env=test doctrine:database:drop --force --if-exists -vvv)
(cd tests/Application && php bin/console --env=test doctrine:database:create -vvv)
(cd tests/Application && php bin/console --env=test doctrine:schema:create -vvv) \
    || (cd tests/Application && php bin/console --env=test doctrine:schema:update --force -vvv)

# Assets
(cd tests/Application && php bin/console --env=test assets:install -vvv)

# Cache warmup
(cd tests/Application && php bin/console --env=test cache:warmup -vvv)

# PHPStan
bash bin/phpstan.sh

# PHPUnit
vendor/bin/phpunit

# Clear cache again before Behat (PHPStan's partial warmup can leave corrupted state)
rm -fr tests/Application/var/cache
mkdir -p tests/Application/var/cache
(cd tests/Application && php bin/console --env=test cache:warmup -vvv)

# Behat
APP_ENV=test vendor/bin/behat --no-interaction
