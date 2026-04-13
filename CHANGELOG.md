# Changelog

## Branch `1.14` — Sylius 1.12–1.14

- Require PHP ^8.1, Symfony ^6.4
- Drop support for Sylius <1.12, PHP <8.1, Symfony <6.4
- Replace FlashBag injection with RequestStack (Symfony 6.4 compatibility)
- Switch from Gulp to Webpack Encore for asset building
- Fix controller routing to use `::` notation (Symfony 6.4)

## Branch `1.11` — Sylius 1.10–1.11

- Require PHP ^8.0, Symfony ^5.4
- Drop support for Sylius <1.10, PHP <8.0, Symfony <5.4

## Branch `1.9` — Sylius 1.7–1.9

- Require PHP ^7.3, Symfony ^4.4
- Drop support for Sylius <1.7
- Symfony 5 is not supported: Sylius 1.9.0 stable pins to Symfony 4.4, so
  composer downgrades Sylius when 5.x is requested — giving a broken mix.
- prefer-lowest is not tested: Sylius 1.7 and 1.8 bring many outdated transitive
  dependencies whose minimum versions have runtime incompatibilities with Sylius
  itself. Bumping each minimum individually would mean blocking the Sylius
  versions that require them.
