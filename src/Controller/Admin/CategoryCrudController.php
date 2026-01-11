<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name', 'Име'),

            // Полето за избиране на Родител
            AssociationField::new('parent', 'Родителска категория')
                ->setHelp('Избери само ако това е под-категория (напр. Плюшени играчки -> Играчки)'),

            BooleanField::new('inMenu', 'Покажи в Менюто')
                ->renderAsSwitch(true),
        ];
    }
}
