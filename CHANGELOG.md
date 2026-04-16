# Changelog

## Branch `1.9` — Sylius 1.7–1.9

- Require PHP ^7.3, Symfony ^4.4
- Drop support for Sylius <1.7
- Symfony 5 is not supported: Sylius 1.9.0 stable pins to Symfony 4.4, so
  composer downgrades Sylius when 5.x is requested — giving a broken mix.
