#!/usr/bin/env bash
# Runs a single Sylius x Symfony x strategy combination inside the php container.
# Invoked by bin/test-matrix.sh.
#
# Arguments:
#   $1 = sylius_version (e.g. "2.3")
#   $2 = symfony_version (e.g. "8.1")
#   $3 = strategy ("prefer-dist" | "prefer-lowest")
#
# Expects /srv/sylius/composer.json.bak to exist (pristine composer.json).
set -euo pipefail
IFS=$'\n\t'

sylius_version="$1"
symfony_version="$2"
strategy="$3"

cd /srv/sylius

# Restore original composer.json, remove lock file and vendor
# (vendor is removed so Composer plugins are not upgraded or downgraded while loaded)
cp composer.json.bak composer.json
rm -f composer.lock
rm -fr vendor

# Require specific Sylius version
composer require "sylius/sylius:${sylius_version}.*" --no-interaction --no-update --no-scripts

# Global Flex applies SYMFONY_REQUIRE to all symfony/* packages, including transitive ones
composer global config --no-plugins allow-plugins.symfony/flex true
composer global require --no-progress --no-scripts --no-plugins symfony/flex

# Sylius 2.0, 2.1 and 2.2 Behat contexts need Behat 3
# (Sylius 2.0 conflicts with behat/gherkin ^4.13, which Behat 3.30+ requires)
if [ "$sylius_version" = "2.0" ]; then
    composer require --dev "behat/behat:^3.22" --no-interaction --no-update --no-scripts
elif [[ "$sylius_version" =~ ^2\.[12]$ ]]; then
    composer require --dev "behat/behat:^3.34" --no-interaction --no-update --no-scripts
fi

# Composer install
composer_flag="--prefer-dist"
if [ "$strategy" = "prefer-lowest" ]; then
    composer_flag="--prefer-lowest"
fi
SYMFONY_REQUIRE="${symfony_version}.*" composer update --no-interaction "${composer_flag}"

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

# JWT keypair
(cd tests/Application && php bin/console --env=test lexik:jwt:generate-keypair --skip-if-exists --no-interaction)

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
