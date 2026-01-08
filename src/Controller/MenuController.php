<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AbstractController
{
    // 1. МЕТОД ЗА ГОРНОТО МЕНЮ (Header)
    public function renderHeader(CategoryRepository $categoryRepository): Response
    {
        // Взимаме само ГЛАВНИТЕ категории (без родител), които са маркирани за менюто
        $categories = $categoryRepository->findBy([
            'parent' => null, // Само главни
            'inMenu' => true  // Само избраните за меню
        ]);

        return $this->render('partials/_header_menu.html.twig', [
            'categories' => $categories,
        ]);
    }

    // 2. МЕТОД ЗА СТРАНИЧНОТО МЕНЮ (Sidebar)
    public function renderSidebar(CategoryRepository $categoryRepository): Response
    {
        // Взимаме ВСИЧКИ главни категории, независимо дали са "inMenu" или не
        $categories = $categoryRepository->findBy(['parent' => null]);

        return $this->render('partials/_sidebar_menu.html.twig', [
            'categories' => $categories,
        ]);
    }
}