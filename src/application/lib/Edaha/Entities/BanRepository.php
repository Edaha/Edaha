<?php
namespace Edaha\Entities;

use Doctrine\ORM\EntityRepository;

class BanRepository extends EntityRepository
{
    public function getAllBans()
    {
        $dql = "SELECT ba FROM \Edaha\Entities\Ban ba";
        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getResult();
    }

    public function getAllActiveBans()
    {
        $dql = "SELECT ba FROM \Edaha\Entities\Ban ba
                WHERE 
                    ba.expires_at IS NULL
                    OR ba.expires_at > :now";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('now', New \DateTime());
        return $query->getResult();
    }

    public function getActiveBansForIp($ip)
    {
        $dql = "SELECT ba FROM \Edaha\Entities\Ban ba
                WHERE 
                    ba.ip = :ip
                    AND (ba.expires_at IS NULL OR ba.expires_at > :now)";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('ip', $ip);
        $query->setParameter('now', New \DateTime());
        return $query->getResult();
    }

    public function getBoardActiveBans($board_id)
    {
        $dql = "SELECT ba FROM \Edaha\Entities\Ban ba
                JOIN ba.board bo
                WHERE 
                    (bo.id = :board_id OR ba.is_global)
                    AND (ba.expires_at IS NULL OR ba.expires_at > :now)";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('board_id', $board_id);
        $query->setParameter('now', New \DateTime());
        return $query->getResult();
    }
}