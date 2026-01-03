<?php

namespace App\Repository\System;

use App\Entity\System\AppBanner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppBanner>
 *
 * @method AppBanner|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppBanner|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppBanner[]    findAll()
 * @method AppBanner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppBannerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppBanner::class);
    }

    /**
     * @return AppBanner[]
     */
    public function findActiveOrdered(): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isEnabled = :active')
            ->setParameter('active', true)
            ->orderBy('b.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
