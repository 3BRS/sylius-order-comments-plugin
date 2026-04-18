#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(dirname "$DIR")"
cd "$ROOT"

# Matrix: PHP x Sylius x Symfony x composer strategy
# The PHP container is rebuilt for each PHP version via the PHP_VERSION build arg.
COMBINATIONS=(
    "8.2 2.0 6.4 prefer-dist"
    "8.2 2.0 7.4 prefer-dist"
    "8.2 2.0 6.4 prefer-lowest"
    "8.2 2.0 7.4 prefer-lowest"
    "8.3 2.0 6.4 prefer-dist"
    "8.3 2.0 7.4 prefer-dist"
    "8.3 2.0 6.4 prefer-lowest"
    "8.3 2.0 7.4 prefer-lowest"
)

PASSED=()
FAILED=()

log() {
    echo ""
    echo "========================================"
    echo "$1"
    echo "========================================"
    echo ""
}

# Backup composer.json (the per-combination script expects composer.json.bak to exist)
cp composer.json composer.json.bak

trap 'cp composer.json.bak composer.json; rm -f composer.json.bak' EXIT

user_id=$(id -u)
group_id=$(id -g)

current_php_version=""

for combination in "${COMBINATIONS[@]}"; do
    # Split combination string on spaces regardless of IFS
    IFS=' ' read -r php_version sylius_version symfony_version strategy <<< "$combination"
    label="PHP ${php_version} / Sylius ${sylius_version} / Symfony ${symfony_version} / ${strategy}"

    # Rebuild/restart the php container only when the PHP version changes.
    if [ "$php_version" != "$current_php_version" ]; then
        log "SWITCHING PHP: ${php_version}"
        PHP_VERSION="$php_version" docker compose up -d --build php
        current_php_version="$php_version"
    fi

    log "TESTING: ${label}"

    if docker compose exec -T --user="${user_id}:${group_id}" php \
        bash bin/test-combination.sh "$sylius_version" "$symfony_version" "$strategy"; then
        PASSED+=("$label")
    else
        FAILED+=("$label")
    fi
done

# Restore original state
cp composer.json.bak composer.json
rm -f composer.lock
./bin-docker/composer update --no-interaction --no-plugins

log "MATRIX RESULTS"

if [ ${#PASSED[@]} -gt 0 ]; then
    echo "PASSED:"
    for label in "${PASSED[@]}"; do
        echo "  ✅ ${label}"
    done
fi

if [ ${#FAILED[@]} -gt 0 ]; then
    echo ""
    echo "FAILED:"
    for label in "${FAILED[@]}"; do
        echo "  ❌ ${label}"
    done
    echo ""
    exit 1
fi

echo ""
echo "All combinations passed!"
