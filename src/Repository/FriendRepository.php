<?php
/**
 * Friend repository.
 */

namespace App\Repository;

use App\Entity\Friend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FriendRepository.
 *
 * @method Friend|null find($id, $lockMode = null, $lockVersion = null)
 * @method Friend|null findOneBy(array $criteria, array $orderBy = null)
 * @method Friend[]    findAll()
 * @method Friend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FriendRepository extends ServiceEntityRepository
{
    /**
     * FriendRepository constructor.
     *
     * @param \Doctrine\Common\Persistence\ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friend::class);
    }

    /**
     * Save record.
     *
     * @param \App\Entity\Friend $friend Friend entity
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Friend $friend): void
    {
        $this->_em->persist($friend);
        $this->_em->flush($friend);
    }

    /**
     * Delete record.
     *
     * @param \App\Entity\Friend $friend Friend entity
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Friend $friend): void
    {
        $this->_em->remove($friend);
        $this->_em->flush($friend);
    }

    /**
     * Query all records.
     *
     */
    public function getFriendConnection($user1, $user2)
    {
        return $this->getOrCreateQueryBuilder()
            ->andWhere('(friend.fromUser = :from AND friend.toUser = :to) OR (friend.fromUser = :to AND friend.toUser = :from)')
            ->setParameter('from', $user1)
            ->setParameter('to', $user2)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Query all records.
     *
     */
    public function getInvitation($from, $to)
    {
        return $this->getOrCreateQueryBuilder()
            ->andWhere('friend.fromUser = :from')
            ->andWhere('friend.toUser = :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Query all records.
     *
     * @return \Doctrine\ORM\QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->orderBy('friend.updatedAt', 'DESC');
    }

    /**
     * Get or create new query builder.
     *
     * @param \Doctrine\ORM\QueryBuilder|null $queryBuilder Query builder
     *
     * @return \Doctrine\ORM\QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('friend');
    }
}