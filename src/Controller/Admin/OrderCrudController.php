<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField; // Ползваме това за продуктите
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Order')
            ->setEntityLabelInPlural('Orders')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle('index', 'Order Management')
            // ТЪРСЕНЕ: Оставяме само полетата, които работят бързо и сигурно
            // EasyAdmin автоматично се справя с user.email, ако е настроен правилно
            ->setSearchFields(['id', 'user.email', 'shippingAddress', 'totalPrice']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $approveAction = Action::new('approveOrder', 'Одобри', 'fas fa-check')
            ->linkToRoute('admin_order_approve_direct', function (Order $order) {
                return ['id' => $order->getId()];
            })
            ->displayIf(fn (Order $order) => $order->getStatus() === Order::STATUS_PENDING)
            ->setCssClass('btn btn-success text-white');

        $cancelAction = Action::new('cancelOrder', 'Откажи', 'fas fa-times')
            ->linkToRoute('admin_order_cancel_direct', function (Order $order) {
                return ['id' => $order->getId()];
            })
            ->displayIf(fn (Order $order) => $order->getStatus() === Order::STATUS_PENDING)
            ->setCssClass('btn btn-danger text-white');

        return $actions
            ->add(Crud::PAGE_INDEX, $approveAction)
            ->add(Crud::PAGE_INDEX, $cancelAction)
            ->disable(Action::NEW)
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->onlyOnIndex(),

            // Колона 1: Клиент
            AssociationField::new('user', 'Customer')
                ->setSortable(true) // Позволява сортиране по потребител
                ->formatValue(function ($value, $entity) {
                    // Ако има потребител, показваме имейла, иначе Guest
                    return $entity->getUser() ? $entity->getUser()->getEmail() : 'Guest';
                }),

            // Колона 2: ПРОДУКТИ (Поправената част)
            // Използваме AssociationField, защото 'items' е връзка (OneToMany)
            AssociationField::new('items', 'Products Bought')
                ->onlyOnIndex()
                ->setSortable(false) // Не можем да сортираме по списък с продукти
                ->formatValue(function ($value, $entity) {
                    // Тук правим магията - превръщаме списъка в текст
                    $productNames = [];
                    foreach ($entity->getItems() as $item) {
                        $productNames[] = $item->getProduct()->getName() . ' (x' . $item->getQuantity() . ')';
                    }
                    // Ако списъкът е празен, връщаме тире
                    return empty($productNames) ? '-' : implode(', ', $productNames);
                }),

            DateTimeField::new('createdAt', 'Date')
                ->setFormat('dd.MM.yyyy HH:mm'),

            MoneyField::new('totalPrice', 'Total')
                ->setCurrency('BGN')
                ->setStoredAsCents(false),

            ChoiceField::new('status', 'Status')
                ->setChoices(Order::getAvailableStatuses())
                ->renderAsBadges([
                    Order::STATUS_PENDING => 'warning',
                    Order::STATUS_APPROVED => 'success',
                    Order::STATUS_SHIPPED => 'info',
                    Order::STATUS_CANCELLED => 'danger',
                ]),

            TextareaField::new('shippingAddress', 'Address')
                ->hideOnIndex(),
        ];
    }
}