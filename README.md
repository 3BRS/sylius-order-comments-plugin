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

<p align="center">
	<img src="https://raw.githubusercontent.com/mangoweb-sylius/SyliusOrderCommentsPlugin/master/doc/CreateEmailsAndNotes.png"/>
</p>

## Installation

1. Run `$ composer require mangoweb-sylius/sylius-order-comments-plugin`.

2. Add plugin classes to your `config/bundles.php`:
 
   ```php
   return [
      ...
      MangoSylius\OrderCommentsPlugin\MangoSyliusOrderCommentsPlugin::class => ['all' => true],
   ];
   ```
  
3. Add resource to `config/packages/_sylius.yaml`

    ```yaml
    imports:
         ...
         - { resource: "@MangoSyliusOrderCommentsPlugin/Resources/config/config.yml" }
    ```
   
4. Add routing to `config/_routes.yaml`

    ```yaml
    mango_sylius_order_comments_plugin:
      resource: "@MangoSyliusOrderCommentsPlugin/Resources/config/routing.yml"
      prefix: /admin
    ```
5. Override the template in `@SyliusAdminBundle/Order/Show/_notes.html.twig`

   ```twig
   ...
    {{ include('@MangoSyliusOrderCommentsPlugin/Admin/_order.html.twig') }}
    ```

6. Create and run doctrine database migrations.

For the guide how to use your own entity see [Sylius docs - Customizing Models](https://docs.sylius.com/en/1.6/customization/model.html)

## Usage

* Comment can be written from the order detail.
* If I check the "Send to customer" checkbox, it will send an email to the customer's email.

## Development

### Usage

- Create symlink from .env.dist to .env or create your own .env file
- Develop your plugin in `/src`
- See `bin/` for useful commands

### Testing

After your changes you must ensure that the tests are still passing.

```bash
$ composer install
$ bin/console doctrine:schema:create -e test
$ bin/behat.sh
$ bin/phpstan.sh
$ bin/ecs.sh
```

License
-------
This library is under the MIT license.

Credits
-------
Developed by [3BRS](https://3brs.com)<br>
Forked from [manGoweb](https://github.com/mangoweb-sylius/SyliusOrderCommentsPlugin).
