<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')] // Само регистрирани потребители имат достъп
class WishlistController extends AbstractController
{
    #[Route('/wishlist', name: 'app_wishlist')]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $favorites = $user->getFavorites();

        $recommended = [];
        if (count($favorites) > 0) {
            $lastFavorite = $favorites->last();
            $price = $lastFavorite->getPrice();
            $category = $lastFavorite->getCategory(); // Взимаме категорията на последния любим

            $minPrice = $price - 10;
            $maxPrice = $price + 10;

            $recommended = $productRepository->createQueryBuilder('p')
                ->where('p.category = :category') // Търсим в същата категория
                ->andWhere('p.price >= :min')
                ->andWhere('p.price <= :max')
                ->andWhere('p.id NOT IN (:favIds)')
                ->setParameter('category', $category)
                ->setParameter('min', $minPrice)
                ->setParameter('max', $maxPrice)
                ->setParameter('favIds', $favorites->map(fn($p) => $p->getId())->toArray())
                ->setMaxResults(4)
                ->getQuery()
                ->getResult();
        }

        return $this->render('wishlist/index.html.twig', [
            'favorites' => $favorites,
            'recommended' => $recommended,
            'allCategories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/wishlist/toggle/{id}', name: 'app_wishlist_toggle', methods: ['POST'])]
    public function toggle(Product $product, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($user->getFavorites()->contains($product)) {
            $user->removeFavorite($product);
            $status = 'removed';
        } else {
            $user->addFavorite($product);
            $status = 'added';
        }

        $em->flush();

        return $this->json([
            'status' => $status,
            'count' => $user->getFavorites()->count()
        ]);
    }
}