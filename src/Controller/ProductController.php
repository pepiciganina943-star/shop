<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{

    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(Product $product, ProductRepository $productRepo): Response
    {
        // 1. Взимаме всички продукти от същата категория
        $allSimilar = $productRepo->findBy(
            ['category' => $product->getCategory()],
            ['id' => 'DESC'],
            10
        );

        // 2. ФИЛТЪР: Махаме текущия продукт
        $similarProducts = [];
        foreach ($allSimilar as $p) {
            if ($p->getId() !== $product->getId()) {
                $similarProducts[] = $p;
            }
        }

        // 3. Орязваме до 8 броя
        $similarProducts = array_slice($similarProducts, 0, 8);

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'similarProducts' => $similarProducts,
        ]);
    }
}