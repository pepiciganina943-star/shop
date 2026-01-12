<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Връща само активните продукти (които НЕ са маркирани като изтрити)
     * @return Product[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isDeleted = :val')
            ->setParameter('val', false)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Търсене по категория, само за активни продукти
     */
    public function findActiveByCategory($category): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.category = :cat')
            ->andWhere('p.isDeleted = :deleted')
            ->setParameter('cat', $category)
            ->setParameter('deleted', false)
            ->getQuery()
            ->getResult();
    }

    /**
     * Търсене за API-то (начална страница) с поддръжка на търсене
     */
    public function searchActive(?string $query): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.isDeleted = :deleted')
            ->setParameter('deleted', false);

        if ($query) {
            $qb->andWhere('p.name LIKE :query OR p.description LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        return $qb->getQuery()->getResult();
    }
    /**
     * @return Product[] Returns an array of Product objects
     */
    public function searchByName(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name LIKE :val OR p.description LIKE :val')
            ->setParameter('val', '%' . $query . '%')
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
    /**
     * Взима само имената на всички продукти за сравнение
     */
    public function getAllProductNames(): array
    {
        // Връща масив от стрингoве: ['Костенурка', 'Заек', 'Гердан'...]
        return $this->createQueryBuilder('p')
            ->select('p.name')
            ->getQuery()
            ->getSingleColumnResult();
    }
}