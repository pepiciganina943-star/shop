<?php

namespace App\EventListener;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Order::class)]
class OrderCreatedListener
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $router
    ) {}

    public function postPersist(Order $order, LifecycleEventArgs $event): void
    {
        $orderId = $order->getId();
        $totalPrice = $order->getTotalPrice();
        $address = $order->getShippingAddress();

        $user = $order->getUser();
        $clientEmail = $user ? $user->getEmail() : 'Guest';
        $adminEmail = 'asem4o@gmail.com';

        // 1. ГЕНЕРИРАНЕ НА ЛИНКОВЕТЕ
        $approveLink = $this->router->generate('admin_order_approve_direct', ['id' => $orderId], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelLink = $this->router->generate('admin_order_cancel_direct', ['id' => $orderId], UrlGeneratorInterface::ABSOLUTE_URL);

        // 2. HTML ЗА ИМЕЙЛА ДО АДМИНА
        $htmlContent = <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; border: 1px solid #ddd; padding: 20px;">
            <div style="background-color: #f8f9fa; padding: 15px; border-bottom: 2px solid #007bff; margin-bottom: 20px;">
                <h2 style="margin:0; color: #333;">🔔 Нова поръчка #$orderId</h2>
                <p style="margin:5px 0 0; color: #666;">$clientEmail</p>
            </div>

            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Сума:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">$totalPrice лв.</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Адрес:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">$address</td>
                </tr>
            </table>

            <div style="margin-top: 30px; text-align: center;">
                <p style="margin-bottom: 15px;">Изберете действие:</p>
                
                <a href="$approveLink" style="background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">
                    ✅ ОДОБРИ
                </a>

                <a href="$cancelLink" style="background-color: #dc3545; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    ❌ ОТКАЖИ
                </a>
            </div>
            
            <p style="margin-top: 30px; font-size: 12px; color: #999; text-align: center;">
                <a href="http://localhost/admin">Влез в администрацията</a> за повече детайли.
            </p>
        </div>
HTML;

        // 3. ИЗПРАЩАНЕ КЪМ АДМИНА
        $emailToAdmin = (new Email())
            ->from('asem4o@gmail.com')
            ->to($adminEmail)
            ->subject("Действие: Нова поръчка #$orderId")
            ->html($htmlContent);

        $this->mailer->send($emailToAdmin);

        // 4. ИЗПРАЩАНЕ КЪМ КЛИЕНТА (Опростено)
        if ($clientEmail && $clientEmail !== 'Guest') {
            $emailToClient = (new Email())
                ->from('asem4o@gmail.com')
                ->to($clientEmail)
                ->subject("Вашата поръчка #$orderId е приета")
                ->text("Здравейте! Поръчка #$orderId на стойност $totalPrice лв. е приета успешно.");
            $this->mailer->send($emailToClient);
        }
    }
}