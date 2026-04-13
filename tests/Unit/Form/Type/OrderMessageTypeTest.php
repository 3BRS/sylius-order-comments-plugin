<?php

declare(strict_types=1);

namespace Tests\MangoSylius\OrderCommentsPlugin\Unit\Form\Type;

use MangoSylius\OrderCommentsPlugin\Entity\OrderMessage;
use MangoSylius\OrderCommentsPlugin\Form\Type\OrderMessageType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

final class OrderMessageTypeTest extends TestCase
{
    /** @var OrderMessageType */
    private $formType;

    protected function setUp(): void
    {
        $this->formType = new OrderMessageType(OrderMessage::class, ['Default']);
    }

    public function testBlockPrefix(): void
    {
        self::assertSame('mango_sylius_order_message', $this->formType->getBlockPrefix());
    }

    public function testBuildFormAddsExpectedFields(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::exactly(3))
            ->method('add')
            ->withConsecutive(
                ['message', TextareaType::class, self::callback(function (array $options): bool {
                    return $options['label'] === false && $options['required'] === true;
                })],
                ['sendMail', CheckboxType::class, self::callback(function (array $options): bool {
                    return $options['label'] === 'mango_sylius.orderMessage.sendMail' && $options['required'] === false;
                })],
                ['save', SubmitType::class, self::callback(function (array $options): bool {
                    return $options['label'] === 'mango_sylius.orderMessage.save';
                })]
            )
            ->willReturnSelf();

        $this->formType->buildForm($builder, []);
    }
}
