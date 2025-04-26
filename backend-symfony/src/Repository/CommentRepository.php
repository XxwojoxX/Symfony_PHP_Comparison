<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findCommentsByPostId(int $postId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.post = :postId')
            ->setParameter('postId', $postId)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findCommentByIdAndPostId(int $commentId, int $postId): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->where('c.id = :commentId')
            ->andWhere('c.post = :postId')
            ->setParameter('commentId', $commentId)
            ->setParameter('postId', $postId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Comment $comment): void
    {
        $this->getEntityManager()->persist($comment);
        $this->getEntityManager()->flush();
    }

    public function remove(Comment $comment): void
    {
        $this->getEntityManager()->remove($comment);
        $this->getEntityManager()->flush();
    }
}