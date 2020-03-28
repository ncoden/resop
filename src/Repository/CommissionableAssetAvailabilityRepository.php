<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CommissionableAsset;
use App\Entity\CommissionableAssetAvailability;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CommissionableAsset|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommissionableAsset|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommissionableAsset[]    findAll()
 * @method CommissionableAsset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommissionableAssetAvailabilityRepository extends ServiceEntityRepository implements AvailabilityRepositoryInterface
{
    use AvailabilityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommissionableAssetAvailability::class);
    }

    public function loadRawDataForEntity(array $availabilitables, DateTimeInterface $from, DateTimeInterface $to): array
    {
        $qb = $this->createQueryBuilder('ua');
        $qb->where($qb->expr()->in('ua.asset', ':assets'))
            ->setParameter('assets', $availabilitables);

        return $this->getRawSlots($qb, $from, $to);
    }

    public function findBetweenDates(CommissionableAsset $asset, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('caa')
            ->where('caa.asset = :asset')
            ->andWhere('caa.startTime >= :start')
            ->andWhere('caa.endTime <= :end')
            ->setParameters([
                'asset' => $asset,
                'start' => $start,
                'end' => $end,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByOwnerAndDates(array $owners, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('ua')
            ->where('ua.asset IN (:owners)')
            ->andWhere('ua.startTime >= :start')
            ->andWhere('ua.endTime <= :end')
            ->setParameters([
                'owners' => $owners,
                'start' => $start,
                'end' => $end,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findLastUpdatedForEntities(array $availabilitables)
    {
        $qb = $this->createQueryBuilder('ca');

        return $qb
            ->select('ca.updatedAt')
            ->where($qb->expr()->in('ca.asset', ':owners'))
            ->setParameter('owners', $availabilitables)
            ->setMaxResults(1)
            ->addOrderBy('ca.updatedAt', 'DESC')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
