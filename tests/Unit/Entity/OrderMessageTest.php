<?php

declare(strict_types=1);

namespace Tests\MangoSylius\OrderCommentsPlugin\Unit\Entity;

use MangoSylius\OrderCommentsPlugin\Entity\OrderMessage;
use MangoSylius\OrderCommentsPlugin\Entity\OrderMessageInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

final class OrderMessageTest extends TestCase
{
    /** @var OrderMessage */
    private $orderMessage;

    protected function setUp(): void
    {
        $this->orderMessage = new OrderMessage();
    }

    public function testImplementsInterfaces(): void
    {
        self::assertInstanceOf(OrderMessageInterface::class, $this->orderMessage);
        self::assertInstanceOf(ResourceInterface::class, $this->orderMessage);
    }

    public function testMessageIsNullByDefault(): void
    {
        self::assertNull($this->orderMessage->getMessage());
    }

    public function testMessageCanBeSet(): void
    {
        $this->orderMessage->setMessage('Hello customer');
        self::assertSame('Hello customer', $this->orderMessage->getMessage());
    }

    public function testMessageCanBeSetToNull(): void
    {
        $this->orderMessage->setMessage('Hello');
        $this->orderMessage->setMessage(null);
        self::assertNull($this->orderMessage->getMessage());
    }

    public function testSendTimeIsNullByDefault(): void
    {
        self::assertNull($this->orderMessage->getSendTime());
    }

    public function testSendTimeCanBeSet(): void
    {
        $dateTime = new \DateTime('2024-01-15 10:30:00');
        $this->orderMessage->setSendTime($dateTime);
        self::assertSame($dateTime, $this->orderMessage->getSendTime());
    }

    public function testSendMailIsFalseByDefault(): void
    {
        self::assertFalse($this->orderMessage->isSendMail());
    }

    public function testSendMailCanBeSet(): void
    {
        $this->orderMessage->setSendMail(true);
        self::assertTrue($this->orderMessage->isSendMail());
    }

    public function testOrderIsNullByDefault(): void
    {
        self::assertNull($this->orderMessage->getOrder());
    }

    public function testOrderCanBeSet(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $this->orderMessage->setOrder($order);
        self::assertSame($order, $this->orderMessage->getOrder());
    }

    public function testSenderIsNullByDefault(): void
    {
        self::assertNull($this->orderMessage->getSender());
    }

    public function testSenderCanBeSet(): void
    {
        $sender = $this->createMock(AdminUserInterface::class);
        $this->orderMessage->setSender($sender);
        self::assertSame($sender, $this->orderMessage->getSender());
    }
}
