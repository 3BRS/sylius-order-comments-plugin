<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\OrderCommentsPlugin\Behat\Pages\Admin\Order;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface ShowPageInterface extends SymfonyPageInterface
{
    public function saveOrderEmail(): void;

    public function addMessage(): void;

    public function showMessage(): void;

    public function checkOption($arg1): void;

    public function uncheckOption($arg1): void;
}
