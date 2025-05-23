<?php
namespace Edaha\Entities;

use Doctrine\ORM\EntityRepository;

class PostRepository extends EntityRepository
{
    public function getAllPaginatedThreads($page = 1, $limit = 10)
    {
        $dql = "SELECT p, b
                FROM \Edaha\Entities\Post p
                JOIN p.board b
                WHERE p.parent IS NULL
                ORDER BY p.stickied_at DESC, p.bumped_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setFirstResult(($page - 1) * $limit);
        $query->setMaxResults($limit);
        return $query->getResult();
    }

    public function getBoardPaginatedThreads($board_id, $page = 1, $limit = 10)
    {
        $dql = "SELECT p, b
                FROM \Edaha\Entities\Post p
                JOIN p.board b
                WHERE b.id = :board_id AND p.parent IS NULL
                ORDER BY p.stickied_at DESC, p.bumped_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('board_id', $board_id);
        // $query->setFirstResult(($page - 1) * $limit);
        // $query->setMaxResults($limit);
        return $query->getResult();
    }

    public function getAllRecentPosts(?int $limit = 10)
    {
        $dql = "SELECT p, b
                FROM \Edaha\Entities\Post p
                JOIN p.board b
                ORDER BY p.created_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        if (!is_null($limit)) {
            $query->setMaxResults($limit);
        }
        return $query->getResult();
    }

    public function getBoardRecentPosts(int $board_id, ?int $limit = 10)
    {
        $dql = "SELECT p, b
                FROM \Edaha\Entities\Post p
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

    public function getAllRecentThreads(?int $limit = 10)
    {
        $dql = "SELECT p, b
                FROM \Edaha\Entities\Post p
                JOIN p.board b
                WHERE p.parent IS NULL
                ORDER BY p.created_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        if (!is_null($limit)) {
            $query->setMaxResults($limit);
        }
        return $query->getResult();
    }

    public function getBoardAllThreads(int $board_id, ?int $limit = 10)
    {
        $dql = "SELECT p, b
                FROM \Edaha\Entities\Post p
                JOIN p.board b
                WHERE b.id = :board_id AND p.parent IS NULL
                ORDER BY p.created_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('board_id', $board_id);
        if (!is_null($limit)) {
            $query->setMaxResults($limit);
        }
        return $query->getResult();
    }

    public function getBoardRecentThreads(int $board_id, ?int $limit = 10)
    {
        $dql = "SELECT p, b
                FROM \Edaha\Entities\Post p
                JOIN p.board b
                WHERE b.id = :board_id AND p.parent IS NULL
                ORDER BY p.created_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('board_id', $board_id);
        if (!is_null($limit)) {
            $query->setMaxResults($limit);
        }
        return $query->getResult();
    }

    public function getThreadReplies(int $post_id, ?int $limit = 10)
    {
        $dql = "SELECT p, b
                FROM \Edaha\Entities\Post p
                JOIN p.board b
                WHERE p.parent = :post_id
                ORDER BY p.created_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('post_id', $post_id);
        if (!is_null($limit)) {
            $query->setMaxResults($limit);
        }
        return $query->getResult();
    }

    public function getPostsByIp($ip, $limit = 10)
    {
        $dql = "SELECT p, b
                FROM \Edaha\Entities\Post p
                JOIN p.board b
                WHERE p.ip = :ip
                ORDER BY p.created_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('ip', $ip);
        $query->setMaxResults($limit);
        return $query->getResult();
    }
}