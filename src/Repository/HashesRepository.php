<?php

namespace App\Repository;

use App\Entity\Hashes;
use App\Traits\PaginationQueryBuilder;
use Closure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Hashes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hashes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hashes[]    findAll()
 * @method Hashes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HashesRepository extends ServiceEntityRepository
{
    use PaginationQueryBuilder;

    /** @var EntityManagerInterface */
    private EntityManagerInterface $manager;

    /**
     * @param ManagerRegistry $registry
     * @param EntityManagerInterface $manager
     */
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $manager)
    {
        parent::__construct($registry, Hashes::class);
        $this->manager = $manager;
    }

    /**
     * @param array $data
     * @return Hashes
     */
    public function saveHash(array $data): Hashes
    {
        $newHash = new Hashes();
        $newHash->setBlockNumber($data['block_number'])
            ->setHash($data['hash'])
            ->setInput($data['input'])
            ->setKey($data['key'])
            ->setAttempts($data['attempts'])
            ->setIp($data['ip'])
            ->setBatch(new \DateTime())
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $this->manager->persist($newHash);
        $this->manager->flush();

        return $newHash;
    }

    /**
     * @param int $page
     * @param int $size
     * @param mixed $filters
     * @return array
     */
    public function getAll(int $page = 1, int $size = 10, $filters = null)
    {
        $query = $this->createQueryBuilder('h')
            ->orderBy('h.batch', 'ASC');

        return $this->setQuery($query)
            ->setScope('attempts_less_than', $this->scopeAttemptsLessThan())
            ->filters($filters)
            ->paginate($page, $size);
    }

    /**
     * @param string $datetime
     * @param string $ip
     * @return int
     */
    public function getLastMinuteByIp(string $datetime, string $ip): int
    {
        $query = $this->createQueryBuilder('h')
            ->andWhere('h.batch > :batch')->setParameter('batch', $datetime)
            ->andWhere('h.ip = :ip')->setParameter('ip', $ip)
            ->select('count(h.id)')
            ->getQuery()
            ->getSingleScalarResult();
        
        return $query;
    }

    /**
     * @return Closure
     */
    public function scopeAttemptsLessThan(): Closure
    {
        return function (QueryBuilder $query, $value): QueryBuilder {
            $query->andWhere('h.attempts < :attempts')->setParameter('attempts', $value);
            return $query;
        };
    }
}
