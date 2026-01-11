<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Form\CheckoutType;
use App\Service\CartService;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class CheckoutController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout')]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        CartService $cartService,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        MailerInterface $mailer
    ): Response {
        // Get cart items
        $cartItems = $cartService->getFullCart();

        // If cart is empty, redirect to cart page
        if (empty($cartItems)) {
            $this->addFlash('warning', 'Вашата количка е празна.');
            return $this->redirectToRoute('app_cart');
        }

        // Create form
        $form = $this->createForm(CheckoutType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Get current user
            $user = $this->getUser();

            // Create new Order
            $order = new Order();
            $order->setUser($user);
            $order->setStatus(Order::STATUS_PENDING);
            $order->setShippingAddress(
                "Име: " . $data['fullName'] . "\n" .
                "Телефон: " . $data['phone'] . "\n" .
                "Адрес: " . $data['address']
            );

            $totalPrice = 0;

            // Create OrderItems from cart
            foreach ($cartItems as $item) {
                $product = $item['product'];
                $quantity = $item['quantity'];

                $orderItem = new OrderItem();
                $orderItem->setProduct($product);
                $orderItem->setQuantity($quantity);
                $orderItem->setPrice($product->getPrice());

                $order->addItem($orderItem);

                $totalPrice += (float)$product->getPrice() * $quantity;
            }

            $order->setTotalPrice((string)$totalPrice);

            // Persist order to database
            $entityManager->persist($order);
            $entityManager->flush();

            // Send email to customer
            $this->sendCustomerEmail($mailer, $order, $data);

            // Send email to admin
            $this->sendAdminEmail($mailer, $order, $data);

            // Clear cart
            $cartService->clear();

            // Redirect to success page
            $this->addFlash('success', 'Вашата поръчка беше успешно приета! Поръчка #' . $order->getId());
            return $this->redirectToRoute('checkout_success', ['id' => $order->getId()]);
        }

        $total = $cartService->getTotal();

        return $this->render('checkout/index.html.twig', [
            'form' => $form->createView(),
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    #[Route('/checkout/success/{id}', name: 'checkout_success')]
    #[IsGranted('ROLE_USER')]
    public function success(int $id, EntityManagerInterface $entityManager): Response
    {
        $order = $entityManager->getRepository(Order::class)->find($id);

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Поръчката не е намерена.');
        }

        return $this->render('checkout/success.html.twig', [
            'order' => $order,
        ]);
    }

    private function sendCustomerEmail(MailerInterface $mailer, Order $order, array $data): void
    {
        $email = (new Email())
            ->from('noreply@bellamie.bg')
            ->to($order->getUser()->getEmail())
            ->subject('Поръчка получена - Чакаща одобрение #' . $order->getId() . ' - Bellamie')
            ->html($this->renderView('emails/order_confirmation.html.twig', [
                'order' => $order,
                'customerData' => $data,
            ]));

        try {
            $mailer->send($email);
        } catch (\Exception $e) {
            error_log('Failed to send customer email: ' . $e->getMessage());
        }
    }

    private function sendAdminEmail(MailerInterface $mailer, Order $order, array $data): void
    {
        $email = (new Email())
            ->from('noreply@bellamie.bg')
            ->to('asem4o@gmail.com')
            ->subject('⚠️ ДЕЙСТВИЕ НЕОБХОДИМО: Нова Поръчка #' . $order->getId() . ' - Чакаща Одобрение')
            ->html($this->renderView('emails/admin_order_notification.html.twig', [
                'order' => $order,
                'customerData' => $data,
            ]));

        try {
            $mailer->send($email);
        } catch (\Exception $e) {
            error_log('Failed to send admin email: ' . $e->getMessage());
        }
    }
}
