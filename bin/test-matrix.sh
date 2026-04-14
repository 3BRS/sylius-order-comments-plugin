#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(dirname "$DIR")"
cd "$ROOT"

# Matrix: Sylius x Symfony x composer strategy
# PHP version is defined by .docker/php/Dockerfile (8.2 — matching the upper bound
# required by Sylius 2.0; newer PHP breaks api-platform/serializer's AttributeLoader
# signature with the older api-platform versions Sylius 2.0 pins).
# prefer-lowest is not tested: Sylius 2.0 pins old api-platform versions that have
# runtime bugs, and transitive Symfony packages resolve to incompatible versions.
COMBINATIONS=(
    "2.0 6.4 prefer-dist"
    "2.0 7.4 prefer-dist"
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

# Ensure docker is up. Only rebuild if the images don't exist yet (avoids hitting
# the Docker registry on every run — useful when the registry is unreachable).
if docker images --format '{{.Repository}}' | grep -q '^syliusordercommentsplugin-php$'; then
    docker compose up -d
else
    docker compose up -d --build
fi

# Backup composer.json (the per-combination script expects composer.json.bak to exist)
cp composer.json composer.json.bak

trap 'cp composer.json.bak composer.json; rm -f composer.json.bak' EXIT

user_id=$(id -u)
group_id=$(id -g)

for combination in "${COMBINATIONS[@]}"; do
    # Split combination string on spaces regardless of IFS
    IFS=' ' read -r sylius_version symfony_version strategy <<< "$combination"
    label="Sylius ${sylius_version} / Symfony ${symfony_version} / ${strategy}"

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
