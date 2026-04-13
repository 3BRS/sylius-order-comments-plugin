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
    private OrderMessageType $formType;

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

        $addedFields = [];
        $builder->expects(self::exactly(3))
            ->method('add')
            ->willReturnCallback(function (string $name, string $type, array $options) use ($builder, &$addedFields) {
                $addedFields[] = ['name' => $name, 'type' => $type, 'options' => $options];

                return $builder;
            });

        $this->formType->buildForm($builder, []);

        self::assertSame('message', $addedFields[0]['name']);
        self::assertSame(TextareaType::class, $addedFields[0]['type']);
        self::assertFalse($addedFields[0]['options']['label']);
        self::assertTrue($addedFields[0]['options']['required']);

        self::assertSame('sendMail', $addedFields[1]['name']);
        self::assertSame(CheckboxType::class, $addedFields[1]['type']);
        self::assertSame('mango_sylius.orderMessage.sendMail', $addedFields[1]['options']['label']);

        self::assertSame('save', $addedFields[2]['name']);
        self::assertSame(SubmitType::class, $addedFields[2]['type']);
        self::assertSame('mango_sylius.orderMessage.save', $addedFields[2]['options']['label']);
    }
}
