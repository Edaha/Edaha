<?php
namespace Edaha\Entities;

use Doctrine\ORM\EntityRepository;

class BanRepository extends EntityRepository
{
    public function getArrayAllActiveBans($board_id)
    {
        $dql = "SELECT ba, bo.id, bo.directory FROM \Edaha\Entities\Ban ba
                JOIN ba.board bo
                WHERE 
                    ba.expires_at IS NULL
                    OR ba.expires_at > :now";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('now', New \DateTime());
        return $query->getArrayResult();
    }

    public function getArrayBoardActiveBans($board_id)
    {
        $dql = "SELECT ba FROM \Edaha\Entities\Ban ba
                JOIN ba.board bo
                WHERE 
                    bo.id = :board_id
                    AND ba.expires_at IS NULL
                    OR ba.expires_at > :now";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('board_id', $board_id);
        $query->setParameter('now', New \DateTime());
        return $query->getArrayResult();
    }
}