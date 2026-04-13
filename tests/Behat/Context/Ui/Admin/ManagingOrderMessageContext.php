<?php

declare(strict_types=1);

namespace Tests\MangoSylius\OrderCommentsPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Service\Checker\EmailCheckerInterface;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Tests\MangoSylius\OrderCommentsPlugin\Behat\Pages\Admin\Order\ShowPageInterface;
use Webmozart\Assert\Assert;

final class ManagingOrderMessageContext implements Context
{
    private ShowPageInterface $showPage;

    private NotificationCheckerInterface $notificationChecker;

    private EmailCheckerInterface $emailChecker;

    public function __construct(
        ShowPageInterface $showPage,
        NotificationCheckerInterface $notificationChecker,
        EmailCheckerInterface $emailChecker
    ) {
        $this->showPage = $showPage;
        $this->notificationChecker = $notificationChecker;
        $this->emailChecker = $emailChecker;
    }

    /**
     * @When I write a message
     */
    public function iWriteAMessage(): void
    {
        $this->showPage->addMessage();
    }

    /**
     * @When I send the order message
     * @When I save the order message
     */
    public function iSendTheOrderEmail(): void
    {
        $this->showPage->saveOrderEmail();
    }

    /**
     * @Then an email generated for order :orderNumber should be sent to :recipient
     */
    public function anEmailGeneratedForOrderShouldBeSentTo(string $orderNumber, string $recipient): void
    {
        Assert::true($this->emailChecker->hasMessageTo('Message regarding your order No.' . $orderNumber, $recipient));
    }

    /**
     * @Then the email to :recipient should contain the message text
     */
    public function theEmailShouldContainTheMessageText(string $recipient): void
    {
        Assert::true($this->emailChecker->hasMessageTo('Some message', $recipient));
    }

    /**
     * @Then the note generated should not be sent to :recipient
     */
    public function anEmailGeneratedForOrderShouldNotBeSentTo(string $recipient): void
    {
        Assert::false($this->emailChecker->hasRecipient($recipient));
    }

    /**
     * @Then I should be notified that the email was sent successfully
     */
    public function iShouldBeNotifiedThatTheEmailWasSentSuccessfully(): void
    {
        $this->notificationChecker->checkNotification(
            'The message to the customer has been sent',
            NotificationType::success()
        );
    }

    /**
     * @Then I should be notified that the note as been created
     */
    public function iShouldBeNotifiedThatTheNoteAsBeenCreated(): void
    {
        $this->notificationChecker->checkNotification(
            'The message has been saved',
            NotificationType::success()
        );
    }

    /**
     * @Then I see list of messages sent to the customer
     * @Then I see the note created
     */
    public function iSeeListOfMessagesSentToTheCustomer(): void
    {
        $this->showPage->showMessage();
    }

    /**
     * @When I check the checkbox :checkbox
     */
    public function iCheckTheCheckbox(string $checkbox): void
    {
        $this->showPage->checkOption($checkbox);
    }

    /**
     * @When I uncheck the checkbox :checkbox
     */
    public function iUncheckTheCheckbox(string $checkbox): void
    {
        $this->showPage->uncheckOption($checkbox);
    }
}
