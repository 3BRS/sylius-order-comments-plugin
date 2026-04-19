# Order Comments Plugin

Create notes and send emails from the Sylius admin order detail page.

## Features

* Create notes on order details
* Send personalized email to the addressee of the order

## Requirements

| Package | Version |
|---------|---------|
| PHP     | >=8.2   |
| Sylius  | ^2.0    |

> This branch (`2.2`) supports Sylius 2.0, 2.1, and 2.2 on PHP 8.2/8.3.
> For Sylius 1.12–1.14, use branch `1.14`.
> For Sylius 1.10–1.11, use branch `1.11`.
> For Sylius 1.7–1.9, use branch `1.9`.

## Installation

1. Run `composer require 3brs/sylius-order-comments-plugin`.

2. Add plugin class to your `config/bundles.php`:

   ```php
   return [
       // ...
       ThreeBRS\OrderCommentsPlugin\ThreeBRSOrderCommentsPlugin::class => ['all' => true],
   ];
   ```

3. Import plugin config in `config/packages/_sylius.yaml`:

    ```yaml
    imports:
        # ...
        - { resource: "@ThreeBRSOrderCommentsPlugin/config/config.yml" }
    ```

4. Add routing to `config/routes/sylius_admin.yaml`:

    ```yaml
    threebrs_order_comments_plugin:
        resource: "@ThreeBRSOrderCommentsPlugin/config/routing.yml"
        prefix: /admin
    ```

5. Create and run doctrine database migrations. If you are upgrading from the
   `mangoweb-sylius/sylius-order-comments-plugin` package, generate a migration
   that renames the table `mangoweb_order_message` to `threebrs_order_message`.

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
Developed by [3BRS](https://www.3brs.com)<br>
Forked from [manGoweb](https://github.com/mangoweb-sylius/SyliusOrderCommentsPlugin).
