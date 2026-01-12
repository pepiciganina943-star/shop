<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(Request $request, ProductRepository $productRepository): Response
    {
        $query = $request->query->get('q');
        $suggestion = null; // Тук ще пазим предложението

        if (!$query) {
            return $this->redirectToRoute('app_home');
        }

        // 1. Първо търсим точно съвпадение
        $products = $productRepository->searchByName($query);

        // 2. АКО НЯМА РЕЗУЛТАТИ -> Търсим предложение
        if (count($products) === 0) {
            $allNames = $productRepository->getAllProductNames();
            $bestMatch = null;
            $shortestDistance = -1;

            foreach ($allNames as $name) {
                // Изчисляваме разликата между търсеното (query) и името на продукта
                // Тъй като търсим на кирилица, понякога е добре да сравняваме малки букви
                $lev = levenshtein(mb_strtolower($query), mb_strtolower($name));

                // Логика: Ако няма разстояние или новото е по-малко от старото
                if ($lev <= 6 && ($lev < $shortestDistance || $shortestDistance < 0)) {
                    // Ако разликата е малка (напр. до 6 грешни букви), го приемаме
                    $bestMatch = $name;
                    $shortestDistance = $lev;
                }
            }

            if ($bestMatch) {
                $suggestion = $bestMatch;
            }
        }

        return $this->render('product/search.html.twig', [
            'products' => $products,
            'query' => $query,
            'suggestion' => $suggestion, // Пращаме предложението към шаблона
        ]);
    }
}