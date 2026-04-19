<?php

declare(strict_types=1);

namespace ThreeBRS\OrderCommentsPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'threebrs_order_message')]
class OrderMessage implements OrderMessageInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank]
    protected ?string $message = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTime $sendTime = null;

    #[ORM\Column(type: 'boolean')]
    protected bool $sendMail = false;

    #[ORM\ManyToOne(targetEntity: \Sylius\Component\Order\Model\OrderInterface::class)]
    protected ?OrderInterface $order = null;

    #[ORM\ManyToOne(targetEntity: \Sylius\Component\Core\Model\AdminUserInterface::class)]
    protected ?AdminUserInterface $sender = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getSendTime(): ?\DateTime
    {
        return $this->sendTime;
    }

    public function setSendTime(?\DateTime $sendTime): void
    {
        $this->sendTime = $sendTime;
    }

    public function isSendMail(): bool
    {
        return $this->sendMail;
    }

    public function setSendMail(bool $sendMail): void
    {
        $this->sendMail = $sendMail;
    }

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function setOrder(?OrderInterface $order): void
    {
        $this->order = $order;
    }

    public function getSender(): ?AdminUserInterface
    {
        return $this->sender;
    }

    public function setSender(?AdminUserInterface $sender): void
    {
        $this->sender = $sender;
    }
}
