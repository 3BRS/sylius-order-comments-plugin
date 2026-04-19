# Changelog

## Branch `2.2` — Sylius 2.0–2.2

- Require PHP >=8.2, Symfony ^6.4|^7.4, Sylius ^2.0 (covers 2.0, 2.1, 2.2)
- Drop support for Sylius <2.0, PHP <8.2
- CI covers PHP 8.2 and 8.3; newer PHP versions are allowed but untested (use at your own risk)
- **BREAKING**: Config import path changed from `@MangoSyliusOrderCommentsPlugin/Resources/config/config.yml` to `@MangoSyliusOrderCommentsPlugin/config/config.yml`
- **BREAKING**: Routing import path changed from `@MangoSyliusOrderCommentsPlugin/Resources/config/routing.yml` to `@MangoSyliusOrderCommentsPlugin/config/routing.yml`
- Order comments form is now automatically added to the admin order page via Twig Hooks (no manual template override needed)
- Convert Doctrine annotations to PHP 8 attributes
- Moved `mailer.yml` config from `src/Resources/config/` to `config/` (loaded automatically via `config.yml` imports)
- Conflict with `doctrine/orm <2.20.10`: earlier 2.20.x versions fail to fire `postLoad`
  on lazy-loaded proxies (LazyGhost), breaking `TranslatableEntityLocaleAssigner` so
  translatable entities throw "No locale has been set" during rendering

## Branch `1.14` — Sylius 1.12–1.14

- Require PHP ^8.1, Symfony ^6.4
- Drop support for Sylius <1.12, PHP <8.1, Symfony <6.4
- Replace FlashBag injection with RequestStack (Symfony 6.4 compatibility)
- Switch from Gulp to Webpack Encore for asset building

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
