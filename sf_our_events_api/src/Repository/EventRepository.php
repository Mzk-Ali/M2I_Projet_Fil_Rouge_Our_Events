<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findAllWithPagination(int $page = 1, int $limit = 3, ?int $categoryId  = null, ?string $city = null): array
    {
        $firstResult = max(0, ($page - 1) * $limit);

        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.categories', 'c')
            ->addSelect('c')
            ->leftJoin('e.premise', 'p')
            ->addSelect('p')
            ->orderBy('e.id', 'ASC');

        // Filtrage par Catégorie via ID
        if (!empty($categoryId)) {
            $qb->andWhere(':categoryId MEMBER OF e.categories')
            ->setParameter('categoryId', $categoryId);
        }

        // Filtrage par Ville via city
        if (!empty($city)) {
            $qb->andWhere('p.city = :city')
            ->setParameter('city', $city);
        }

        $query = $qb->getQuery()
            ->setFirstResult($firstResult)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, true);

        // Optionnel : forcer les output walkers si tu utilises DISTINCT / HAVING complexe
        // $paginator->setUseOutputWalkers(true);

        // Récupère les entités paginées
        $events = iterator_to_array($paginator);

        // total (count du paginator)
        $total = count($paginator);

        return [
            'data'  => $events,
            'total' => $total,
        ];
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
