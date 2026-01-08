<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\ProductImageType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Doctrine\ORM\EntityManagerInterface;

// === ВАЖНО: Добавяме библиотеките на Cloudinary ===
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Продукт')
            ->setEntityLabelInPlural('Продукти')
            ->setSearchFields(['name', 'description']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Име на продукта'),

            MoneyField::new('price', 'Цена')
                ->setCurrency('BGN')
                ->setStoredAsCents(false),

            IntegerField::new('stock', 'Наличност'),
            AssociationField::new('category', 'Категория'),
            TextEditorField::new('description', 'Описание'),

            CollectionField::new('images', 'Снимки на продукта')
                ->setEntryType(ProductImageType::class)
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->onlyOnForms()
                ->setHelp('Качете снимки. Cloudinary автоматично ще ги оптимизира.'),
        ];
    }

    // При триене вече не търсим файл на диска, защото той е в облака
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Тук просто трием записа от базата. Снимката остава в Cloudinary (за безопасност).
        // Ако искаш да се трие и от облака, трябва допълнителен код, но засега е по-лесно така.
        parent::deleteEntity($entityManager, $entityInstance);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->handleImageUploads($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->handleImageUploads($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handleImageUploads(Product $product): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $files = $request->files->all();
        $formName = 'Product';

        // 1. Настройваме Cloudinary с данните от Environment Variables
        // Ако гърми тук, значи не си настроил променливите в Koyeb
        if (isset($_ENV['CLOUDINARY_CLOUD_NAME'])) {
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'],
                    'api_key'    => $_ENV['CLOUDINARY_API_KEY'],
                    'api_secret' => $_ENV['CLOUDINARY_API_SECRET']
                ],
                'url' => [
                    'secure' => true
                ]
            ]);
        }

        if (isset($files[$formName]['images'])) {
            $hasCover = false;
            $imageFiles = $files[$formName]['images'];

            foreach ($product->getImages() as $index => $productImage) {
                // Ако има качен нов файл
                if (isset($imageFiles[$index]['file']) && $imageFiles[$index]['file']) {
                    $file = $imageFiles[$index]['file'];

                    try {
                        // 2. КАЧВАНЕ В CLOUDINARY
                        // Това е магията - пращаме файла директно в облака
                        $upload = (new UploadApi())->upload($file->getPathname(), [
                            'folder' => 'products',  // Папка в Cloudinary
                            'format' => 'webp',      // Автоматично става на WebP
                            'quality' => 'auto'      // Автоматична оптимизация
                        ]);

                        // 3. Взимаме линка (Secure URL) и го записваме в базата
                        // Сега в базата ще пише "https://res.cloudinary.com/..." вместо "snimka.webp"
                        $productImage->setFilename($upload['secure_url']);

                    } catch (\Exception $e) {
                        // Ако няма връзка с Cloudinary, хвърляме грешка или логваме
                        // Засега просто не правим нищо
                    }
                }

                // Логика за главна снимка (Cover)
                if ($productImage->isCover()) {
                    if ($hasCover) {
                        $productImage->setIsCover(false);
                    } else {
                        $hasCover = true;
                    }
                }
            }

            if (!$hasCover && $product->getImages()->count() > 0) {
                $product->getImages()->first()->setIsCover(true);
            }
        }
    }

    // Премахнахме convertToWebP, защото Cloudinary го прави автоматично!

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}