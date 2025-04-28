<?php
namespace Edaha\Entities;

use Doctrine\ORM\EntityRepository;

class PostRepository extends EntityRepository
{
    public function getPaginatedThreadsByBoard($board_id, $page = 1, $limit = 10)
    {
        $dql = "SELECT p, b
                FROM \Edaha\Entities\Post p
                JOIN p.board b
                WHERE b.id = :board_id AND p.parent IS NULL
                ORDER BY p.stickied_at DESC, p.created_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('board_id', $board_id);
        // $query->setFirstResult(($page - 1) * $limit);
        // $query->setMaxResults($limit);
        return $query->getResult();
    }

    public function getAllRecentPosts(?int $limit = 10)
    {
        $dql = "SELECT p, b
                FROM Post p
                JOIN p.board b
                ORDER BY p.created_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        if (!is_null($limit)) {
            $query->setMaxResults($limit);
        }
        return $query->getResult();
    }

    public function getRecentPosts(int $board_id, ?int $limit = 10)
    {
        $dql = "SELECT p, b
                FROM Post p
                JOIN p.board b
                WHERE b.id = :board_id
                ORDER BY p.created_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('board_id', $board_id);
        if (!is_null($limit)) {
            $query->setMaxResults($limit);
        }
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