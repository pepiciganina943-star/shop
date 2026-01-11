<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class OrderApprovalController extends AbstractController
{
    // --- ДЕЙСТВИЕ ЗА ОДОБРЯВАНЕ (Вади от склада) ---
    #[Route('/admin/order/approve-direct/{id}', name: 'admin_order_approve_direct')]
    public function approve(Order $order, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        // Ако вече е одобрена, не правим нищо
        if ($order->getStatus() === Order::STATUS_APPROVED) {
            return new Response('<h1 style="color:blue; text-align:center;">Тази поръчка вече е била одобрена!</h1>');
        }

        // Ако поръчката е била отказана преди това, но сега я одобряваме отново,
        // трябва да сме сигурни, че вадим бройките.
        // Логиката тук е: "Винаги вади бройки, когато става APPROVED"

        // 1. НАМАЛЯВАНЕ НА КОЛИЧЕСТВАТА (STOCK)
        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();

            $currentStock = $product->getStock();
            $orderedQty = $item->getQuantity();

            // Вадим от склада
            $product->setStock($currentStock - $orderedQty);
        }

        // 2. Смяна на статус
        $order->setStatus(Order::STATUS_APPROVED);
        $em->flush();

        // 3. Имейл
        $this->sendEmail($order, $mailer, 'APPROVED');

        return new Response(
            '<div style="text-align:center; font-family: Arial; margin-top:50px;">' .
            '<h1 style="color: green;">✅ УСПЕХ!</h1>' .
            '<h2>Поръчката е одобрена и количествата са взети от склада!</h2>' .
            '<a href="/admin">Към Админ панела</a>' .
            '</div>'
        );
    }

    // --- ДЕЙСТВИЕ ЗА ОТКАЗВАНЕ (Връща в склада, САМО ако е била одобрена) ---
    #[Route('/admin/order/cancel-direct/{id}', name: 'admin_order_cancel_direct')]
    public function cancel(Order $order, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        if ($order->getStatus() === Order::STATUS_CANCELLED) {
            return new Response('<h1 style="color:red; text-align:center;">Тази поръчка вече е отказана!</h1>');
        }

        // ВАЖНО: Проверяваме дали трябва да върнем стоката!
        // Връщаме стоката САМО ако статусът досега е бил APPROVED (или SHIPPED)
        // Ако е бил PENDING, значи стоката не е била пипана, така че не я връщаме.
        if ($order->getStatus() === Order::STATUS_APPROVED || $order->getStatus() === Order::STATUS_SHIPPED) {

            foreach ($order->getItems() as $item) {
                $product = $item->getProduct();

                $currentStock = $product->getStock();
                $orderedQty = $item->getQuantity();

                // ВРЪЩАМЕ БРОЙКИТЕ ОБРАТНО (+)
                $product->setStock($currentStock + $orderedQty);
            }
        }

        // 1. Смяна на статус
        $order->setStatus(Order::STATUS_CANCELLED);
        $em->flush();

        // 2. Имейл
        $this->sendEmail($order, $mailer, 'CANCELLED');

        return new Response(
            '<div style="text-align:center; font-family: Arial; margin-top:50px;">' .
            '<h1 style="color: red;">❌ ОТКАЗАНА!</h1>' .
            '<h2>Поръчката е отказана.</h2>' .
            '<p>Ако е била одобрена, бройките са върнати в склада.</p>' .
            '<a href="/admin">Към Админ панела</a>' .
            '</div>'
        );
    }

    // --- Помощна функция за имейлите (за да не пишем един и същи код 2 пъти) ---
    private function sendEmail(Order $order, MailerInterface $mailer, string $type): void
    {
        $clientEmail = $order->getUser() ? $order->getUser()->getEmail() : null;
        if (!$clientEmail) return;

        $subject = ($type === 'APPROVED')
            ? '✅ Поръчка #' . $order->getId() . ' е ОДОБРЕНА!'
            : '❌ Поръчка #' . $order->getId() . ' е ОТКАЗАНА';

        $color = ($type === 'APPROVED') ? '#28a745' : '#dc3545';
        $message = ($type === 'APPROVED')
            ? 'Поръчката ви е потвърдена и стоката е запазена за вас.'
            : 'Поръчката ви е отказана. Моля свържете се с нас.';

        $email = (new Email())
            ->from('asem4o@gmail.com')
            ->to($clientEmail)
            ->subject($subject)
            ->html(
                "<div style='font-family: Arial; text-align: center; color: #333;'>" .
                "<h1 style='color: $color;'>$message</h1>" .
                "<p>Номер на поръчка: <strong>#{$order->getId()}</strong></p>" .
                "</div>"
            );

        $mailer->send($email);
    }
}