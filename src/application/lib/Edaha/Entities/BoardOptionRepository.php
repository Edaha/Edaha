<?php
namespace Edaha\Entities;

use Doctrine\ORM\EntityRepository;

class BoardOptionRepository extends EntityRepository
{
    public function getOptionsByBoard($board_id)
    {
        $dql = "SELECT o FROM \Edaha\Entities\BoardOption o
                JOIN o.board b
                WHERE b.id = :board_id";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('board_id', $board_id);
        return $query->getArrayResult();
    }
}