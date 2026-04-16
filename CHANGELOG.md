# Changelog

## Branch `1.9` — Sylius 1.7–1.9

- Require PHP ^7.3, Symfony ^4.4
- Drop support for Sylius <1.7
- Symfony 5 is not supported: Sylius 1.9.0 stable pins to Symfony 4.4, so
  composer downgrades Sylius when 5.x is requested — giving a broken mix.
- prefer-lowest is not tested: Sylius 1.7 and 1.8 bring many outdated transitive
  dependencies whose minimum versions have runtime incompatibilities with Sylius
  itself. Bumping each minimum individually would mean blocking the Sylius
  versions that require them.
