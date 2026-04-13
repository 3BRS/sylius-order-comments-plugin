<?php

declare(strict_types=1);

namespace MangoSylius\OrderCommentsPlugin\Controller;

use MangoSylius\OrderCommentsPlugin\Entity\OrderMessage;
use MangoSylius\OrderCommentsPlugin\Form\Type\OrderMessageType;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class OrderMessageController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Environment $twig,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SenderInterface $mailer,
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
        private readonly FormFactoryInterface $builder,
        private readonly TokenStorageInterface $token,
        private readonly RepositoryInterface $orderMessageRepository,
    ) {
    }

    public function save(Request $request, int $orderId): Response
    {
        $orderMessage = new OrderMessage();
        $form = $this->builder->create(OrderMessageType::class, $orderMessage, [
            'action' => $this->router->generate('mango_sylius_admin_order_message_send', ['orderId' => $orderId]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $order = $this->orderRepository->find($orderId);

                assert($order instanceof OrderInterface);
                $orderMessage->setOrder($order);

                $tokenObj = $this->token->getToken();
                assert($tokenObj !== null);
                $sender = $tokenObj->getUser();
                assert($sender instanceof AdminUserInterface);
                $orderMessage->setSender($sender);
                $orderMessage->setSendTime(new \DateTime());

                $this->orderMessageRepository->add($orderMessage);

                $customer = $order->getCustomer();
                if ($orderMessage->isSendMail()) {
                    assert($customer instanceof CustomerInterface);
                    $this->mailer->send('order_mail', [$customer->getEmail()], ['orderMessage' => $orderMessage]);
                    $this->addFlash('success', $this->translator->trans('mango_sylius.orderMessage.success.mail'));
                } else {
                    $this->addFlash('success', $this->translator->trans('mango_sylius.orderMessage.success.note'));
                }
            } else {
                $this->addFlash('error', $this->translator->trans('mango_sylius.orderMessage.error'));
            }

            return new RedirectResponse($this->router->generate('sylius_admin_order_show', ['id' => $orderId]));
        }

        return new Response($this->twig->render('@MangoSyliusOrderCommentsPlugin/Admin/Form/_form.html.twig', [
            'form' => $form->createView(),
        ]));
    }

    public function show(int $orderId): Response
    {
        $orderMessages = $this->orderMessageRepository->findBy(['order' => $orderId]);

        return new Response($this->twig->render('@MangoSyliusOrderCommentsPlugin/Admin/_show.html.twig', [
            'messages' => $orderMessages,
        ]));
    }

    private function addFlash(string $type, string $message): void
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->requestStack->getSession();
        $session->getFlashBag()->add($type, $message);
    }
}
