<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'allCategories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/api/products', name: 'api_products')]
    public function apiProducts(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $qb = $productRepository->createQueryBuilder('p');

        // --- ФИЛТРИ ---
        if ($catId = $request->query->get('category_id')) {
            $qb->join('p.category', 'c')->andWhere('c.id = :catId')->setParameter('catId', $catId);
        }
        if ($min = $request->query->get('min_price')) {
            $qb->andWhere('p.price >= :min')->setParameter('min', $min);
        }
        if ($max = $request->query->get('max_price')) {
            $qb->andWhere('p.price <= :max')->setParameter('max', $max);
        }
        if ($request->query->get('in_stock')) {
            $qb->andWhere('p.stock > 0');
        }

        // --- СОРТИРАНЕ ---
        $sort = $request->query->get('sort', 'newest');
        switch ($sort) {
            case 'price_asc': $qb->orderBy('p.price', 'ASC'); break;
            case 'price_desc': $qb->orderBy('p.price', 'DESC'); break;
            default: $qb->orderBy('p.id', 'DESC');
        }

        $products = $qb->getQuery()->getResult();
        $user = $this->getUser();

        // --- JSON ПРЕОБРАЗУВАНЕ ---
        $data = [];
        foreach ($products as $product) {
            $images = $product->getImages();

            // 1. ОПРАВЯМЕ ГЛАВНАТА СНИМКА
            $mainImage = '/images/placeholder.jpg'; // По подразбиране

            if (count($images) > 0) {
                $filename = $images[0]->getFilename();
                // Тук е магията: Проверяваме дали е Cloudinary линк или локален файл
                if (str_contains($filename, 'http')) {
                    $mainImage = $filename; // Cloudinary
                } else {
                    $mainImage = '/uploads/products/' . $filename; // Стара локална снимка
                }
            }
            // Поддръжка за стари методи (ако имаш такива)
            elseif (method_exists($product, 'getImageFilename') && $product->getImageFilename()) {
                $fname = $product->getImageFilename();
                if (str_contains($fname, 'http')) {
                    $mainImage = $fname;
                } else {
                    $mainImage = '/uploads/products/' . $fname;
                }
            }

            // 2. ОПРАВЯМЕ HOVER СНИМКАТА (за мишката)
            $hoverImage = $mainImage;
            if (count($images) > 1) {
                $filenameHover = $images[1]->getFilename();
                if (str_contains($filenameHover, 'http')) {
                    $hoverImage = $filenameHover;
                } else {
                    $hoverImage = '/uploads/products/' . $filenameHover;
                }
            }

            // ПРОВЕРКА: Любим ли е?
            $isFavorite = false;
            if ($user && method_exists($user, 'getFavorites')) {
                $isFavorite = $user->getFavorites()->contains($product);
            }

            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'image' => $mainImage,
                'hoverImage' => $hoverImage,
                'isFavorite' => $isFavorite,
            ];
        }

        return $this->json($data);
    }
}