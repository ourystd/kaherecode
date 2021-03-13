<?php

namespace App\Repository;

use App\Entity\Tutorial;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Tutorial|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tutorial|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tutorial[]    findAll()
 * @method Tutorial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TutorialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tutorial::class);
    }

    /**
     * @return Tutorial[] Returns an array of Tutorial objects
     */
    public function findAllPublishedByTag(string $label)
    {
        return $this->createQueryBuilder('t')
            ->where('t.isPublished = :isPublished')
            ->setParameter('isPublished', true)
            ->orderBy('t.publishedAt', 'DESC')
            ->innerJoin('t.tags', 'c')
            ->andWhere('c.label = :tag')
            ->setParameter('tag', $label)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Tutorial[] Returns an array of Tutorial objects
     */
    public function findRelatedTutorials(Tutorial $tutorial, $limit = null)
    {
        $query = $this->createQueryBuilder('t')
            ->innerJoin('t.tags', 'c')
            ->addSelect('c')
            ->andWhere("c IN(:tags)")
            ->setParameter('tags', array_values($tutorial->getTags()->toArray()))
            ->andWhere('t.id != :id')
            ->setParameter('id', $tutorial->getId())
            ->andWhere('t.isPublished = true');

        if ($limit) {
            $query->setMaxResults($limit);
        }

        return $query
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Tutorial Returns a Tutorial object or null
     */
    public function getUserLastPublishedTutorial(Tutorial $tutorial)
    {
        return $this->createQueryBuilder('t')
            ->where('t.author = :author')
            ->setParameter('author', $tutorial->getAuthor())
            ->andWhere('t.id != :id')
            ->setParameter('id', $tutorial->getId())
            ->orderBy('t.publishedAt', 'DESC')
            ->setMaxResults(1)
            ->andWhere('t.isPublished = true')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Tutorial Returns video tutorials
     */
    public function findVideoTutorials()
    {
        return $this->createQueryBuilder('t')
            ->where('t.videoLink is not null')
            ->andWhere('t.isPublished = true')
            ->orderBy('t.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
