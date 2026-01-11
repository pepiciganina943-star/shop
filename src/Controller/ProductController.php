<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    // ВАЖНО: Тук промених името на 'app_product_show', за да съвпада с линковете в сайта!
    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(Product $product): Response
    {
        // Symfony вижда, че искаш Product и имаш {id} в адреса.
        // То автоматично прави заявката към базата.
        // Ако няма такъв продукт, само хвърля 404 грешка. Няма нужда да пишеш if (!$product)...

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}