<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Tests\ThreeBRS\OrderCommentsPlugin\Behat\Context\Ui\Admin\ManagingOrderMessageContext;
use Tests\ThreeBRS\OrderCommentsPlugin\Behat\Pages\Admin\Order\ShowPage;
use Tests\ThreeBRS\OrderCommentsPlugin\Behat\Pages\Admin\Order\ShowPageInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->public();

    $services->set('sylius.behat.context.ui.admin.order_message', ManagingOrderMessageContext::class)
        ->args([
            service(ShowPageInterface::class),
            service('sylius.behat.notification_checker.admin'),
            service('sylius.behat.email_checker'),
        ]);

    $services->set(ShowPageInterface::class, ShowPage::class)
        ->parent('sylius.behat.symfony_page');
};
