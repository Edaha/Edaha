<?php
namespace Edaha\Entities;

use Doctrine\ORM\EntityRepository;

class PostRepository extends EntityRepository
{
    public function getRecentPosts($boardName, $limit = 10)
    {
        $dql = "SELECT p, b
                FROM Post p
                JOIN p.board b
                WHERE b.name = :boardName
                ORDER BY p.created_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('boardName', $boardName);
        $query->setMaxResults($limit);
        return $query->getResult();
    }

    public function getRecentThreads($boardName, $limit = 10)
    {
        $dql = "SELECT p, b
                FROM Post p
                JOIN p.board b
                WHERE b.name = :boardName AND p.parent IS NULL
                ORDER BY p.created_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('boardName', $boardName);
        $query->setMaxResults($limit);
        return $query->getResult();
    }

    public function getPostsByIp($ip, $limit = 10)
    {
        $dql = "SELECT p, b
                FROM Post p
                JOIN p.board b
                WHERE p.ip = :ip
                ORDER BY p.created_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('ip', $ip);
        $query->setMaxResults($limit);
        return $query->getResult();
    }
}