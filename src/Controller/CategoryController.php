<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category')]
class CategoryController extends AbstractController
{
    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(
        Category $category,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        Request $request
    ): Response
    {
        // Основна заявка: взимаме продукти от категорията, които НЕ са изтрити
        $qb = $productRepository->createQueryBuilder('p')
            ->where('p.category = :category')
            ->andWhere('p.isDeleted = :deleted')
            ->setParameter('category', $category)
            ->setParameter('deleted', false);

        // --- ФИЛТРИ ---

        // 1. Цена (от GET параметрите на формата)
        if ($minPrice = $request->query->get('min_price')) {
            $qb->andWhere('p.price >= :minPrice')->setParameter('minPrice', $minPrice);
        }
        if ($maxPrice = $request->query->get('max_price')) {
            $qb->andWhere('p.price <= :maxPrice')->setParameter('maxPrice', $maxPrice);
        }

        // 2. Наличност (Проверка дали стокът е над 0)
        if ($request->query->get('in_stock')) {
            $qb->andWhere('p.stock > 0');
        }

        // --- СОРТИРАНЕ ---
        $sort = $request->query->get('sort', 'newest');

        switch ($sort) {
            case 'price_asc':
                $qb->orderBy('p.price', 'ASC');
                break;
            case 'price_desc':
                $qb->orderBy('p.price', 'DESC');
                break;
            default: // най-нови (по ID)
                $qb->orderBy('p.id', 'DESC');
        }

        $products = $qb->getQuery()->getResult();

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'products' => $products,
            'allCategories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/_partial/menu', name: 'app_menu_partial')]
    public function getMenu(CategoryRepository $categoryRepository): Response
    {
        return $this->render('partials/menu.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }
}