<?php

namespace App\Infrastructure\Repository;

use App\Application\Repository\WeatherEntityRepositoryInterface;
use App\Infrastructure\Entity\WeatherEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WeatherEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method WeatherEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method WeatherEntity[]    findAll()
 * @method WeatherEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeatherEntityRepository extends ServiceEntityRepository implements WeatherEntityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeatherEntity::class);
    }

    public function saveEntities(array $entities): void
    {
        $entityManager = $this->getEntityManager();
        foreach ($entities as $entity) {
            $entityManager->persist($entity);
        }
        $entityManager->flush();
    }

    public function deleteEntities(array $entities): void
    {
        $entityManager = $this->getEntityManager();
        foreach ($entities as $entity) {
            $entityManager->remove($entity);
        }
        $entityManager->flush();
    }

    /**
     * @return WeatherEntity[]
     */
    public function getLatestEntites(): array
    {
        $qb = $this->createQueryBuilder('w1');
        return $qb
            ->select('w1')
            ->leftJoin(WeatherEntity::class, 'w2', Join::WITH, 'w1.region = w2.region AND w1.date < w2.date')
            ->where('w2.region IS NULL')
            ->orderBy('w1.date', 'DESC')
            ->orderBy('w1.region')
            ->groupBy('w1.region')
            ->getQuery()
            ->getResult();
    }

    public function getOutdatedEntities(): array
    {
        $latestEntities = $this->getLatestEntites();
        $entityIds = array_map(function (WeatherEntity $entity) {
            return $entity->id;
        }, $latestEntities);

        $qb = $this->createQueryBuilder('w');
        return $qb
            ->select('w')
            ->where($qb->expr()->notIn('w.id', $entityIds))
            ->getQuery()
            ->getResult();
    }
}
