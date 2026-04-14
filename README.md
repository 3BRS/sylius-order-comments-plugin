<p align="center">
    <a href="https://www.3brs.com" target="_blank">
        <img src="https://3brs1.fra1.cdn.digitaloceanspaces.com/3brs/logo/3BRS-logo-sylius-200.png"/>
    </a>
</p>
<h1 align="center">
Order Comments Plugin
<br />
    <a href="https://packagist.org/packages/3brs/sylius-order-comments-plugin" title="License" target="_blank">
        <img src="https://img.shields.io/packagist/l/3brs/sylius-order-comments-plugin.svg" />
    </a>
    <a href="https://packagist.org/packages/3brs/sylius-order-comments-plugin" title="Version" target="_blank">
        <img src="https://img.shields.io/packagist/v/3brs/sylius-order-comments-plugin.svg" />
    </a>
    <a href="http://travis-ci.com/3brs/sylius-order-comments-plugin" title="Build status" target="_blank">
        <img src="https://img.shields.io/travis/3brs/sylius-order-comments-plugin/master.svg" />
    </a>
</h1>

## Features

* Create notes on order details
* Send personalized email to the addressee of the order

## Requirements

| Package | Version |
|---------|---------|
| PHP     | 8.2     |
| Sylius  | ~2.0.0  |

> This branch (`2.0`) supports Sylius 2.0 only, on PHP 8.2.
> For Sylius 2.1 and 2.2, use branch `2.2`.
> For Sylius 1.12–1.14, use branch `1.14`.
> For Sylius 1.10–1.11, use branch `1.11`.
> For Sylius 1.7–1.9, use branch `1.9`.

## Installation

1. Run `composer require mangoweb-sylius/sylius-order-comments-plugin`.

2. Add plugin class to your `config/bundles.php`:

   ```php
   return [
       // ...
       MangoSylius\OrderCommentsPlugin\MangoSyliusOrderCommentsPlugin::class => ['all' => true],
   ];
   ```

3. Import plugin config in `config/packages/_sylius.yaml`:

    ```yaml
    imports:
        # ...
        - { resource: "@MangoSyliusOrderCommentsPlugin/config/config.yml" }
    ```

4. Add routing to `config/routes/sylius_admin.yaml`:

    ```yaml
    mango_sylius_order_comments_plugin:
        resource: "@MangoSyliusOrderCommentsPlugin/config/routing.yml"
        prefix: /admin
    ```

5. Create and run doctrine database migrations.

The order comments form and message list are automatically added to the admin order show page via Twig Hooks. No template overrides needed.

## Usage

* Comment can be written from the order detail page.
* If "Send to customer" is checked, an email is sent to the customer's email address.

## Development

```bash
make run        # Start Docker, install deps, build assets, set up DB
make tests      # Run PHPStan, PHPUnit, Behat
make fixtures   # Reset DB and load sample data
make bash       # Enter PHP container
```

## License

This library is under the MIT license.

Credits
-------
Developed by [3BRS](https://3brs.com)<br>
Forked from [manGoweb](https://github.com/mangoweb-sylius/SyliusOrderCommentsPlugin).
