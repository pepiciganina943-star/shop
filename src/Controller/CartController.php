<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Service\CartService;
use App\Repository\ProductRepository; // <--- ВАЖНО: Добави това
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface; // <--- ВАЖНО: Добави това
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService
    ) {
    }

    #[Route('/cart', name: 'app_cart')]
    public function index(): Response
    {
        $cartItems = $this->cartService->getFullCart();
        $total = $this->cartService->getTotal();

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function add(int $id): Response
    {
        $this->cartService->add($id);
        $this->addFlash('success', 'Product added to cart!');

        return $this->redirectToRoute('app_cart');
    }

    // === ТУК Е ПРОМЯНАТА ЗА СТАНДАРТНОТО ТРИЕНЕ ===
    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    public function remove(int $id, SessionInterface $session): Response
    {
        // Взимаме количката директно
        $cart = $session->get('cart', []);

        // Изтриваме напълно продукта
        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        // Записваме промяната
        $session->set('cart', $cart);

        $this->addFlash('info', 'Product removed from cart.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/api/add/{id}', name: 'api_cart_add')]
    public function apiAdd(int $id): JsonResponse
    {
        $this->cartService->add($id);
        $newCount = $this->cartService->count();

        return new JsonResponse([
            'success' => true,
            'newCount' => $newCount
        ]);
    }

    // === ТУК Е ГОЛЯМАТА ПРОМЯНА ЗА БУТОНА В КОЛИЧКАТА ===
    #[Route('/cart/api/remove/{id}', name: 'api_cart_remove')]
    public function apiRemove(int $id, SessionInterface $session, ProductRepository $productRepository): JsonResponse
    {
        $cart = $session->get('cart', []);

        // 1. Изтриваме напълно (unset)
        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);

        // 2. Трябва да сметнем новия тотал ръчно, защото сервизът може да не се е усетил
        $newTotal = 0;
        foreach ($cart as $prodId => $quantity) {
            $product = $productRepository->find($prodId);
            if ($product) {
                $newTotal += $product->getPrice() * $quantity;
            }
        }

        // 3. Броим колко неща остават
        $count = 0;
        foreach ($cart as $qty) {
            $count += $qty;
        }

        return new JsonResponse([
            'success' => true,
            'newTotal' => $newTotal,
            'count' => $count // Връщаме и бройката, за да се обнови хедъра
        ]);
    }

    #[Route('/cart/api/count', name: 'api_cart_count')]
    public function apiCount(): JsonResponse
    {
        $count = $this->cartService->count();

        return new JsonResponse([
            'count' => $count
        ]);
    }

    #[Route('/cart/api/update-quantity/{id}/{quantity}', name: 'api_cart_update_quantity', methods: ['POST'])]
    public function apiUpdateQuantity(int $id, int $quantity): JsonResponse
    {
        // Тук ползваме сервиза, защото updateQuantity обикновено е написан правилно
        // ($cart[$id] = $quantity)

        if ($quantity < 1) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Quantity must be at least 1'
            ], 400);
        }

        $this->cartService->updateQuantity($id, $quantity);
        $newTotal = $this->cartService->getTotal();
        $count = $this->cartService->count();

        return new JsonResponse([
            'success' => true,
            'newTotal' => $newTotal,
            'count' => $count
        ]);
    }
    public function getMenu(CategoryRepository $categoryRepository): Response
    {
        // Взима всички категории от базата данни
        $categories = $categoryRepository->findAll();

        return $this->render('category/menu.html.twig', [
            'categories' => $categories,
        ]);
    }
}