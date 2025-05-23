<?php
namespace Edaha\Entities;

use Doctrine\ORM\EntityRepository;

class BoardRepository extends EntityRepository
{
    public function getAllBoards()
    {
        $dql = "SELECT b FROM \Edaha\Entities\Board b
                ORDER BY b.created_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getResult();
    }
}