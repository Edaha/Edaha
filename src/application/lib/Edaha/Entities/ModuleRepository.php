<?php
namespace Edaha\Entities;

use Doctrine\ORM\EntityRepository;

class ModuleRepository extends EntityRepository
{
    public function getAllModules()
    {
        $dql = "SELECT m FROM \Edaha\Entities\Board m
                ORDER BY m.created_at DESC";
        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getResult();
    }

    public function getManagementModules()
    {
        $dql = "SELECT m FROM \Edaha\Entities\Module m
                WHERE m.is_manage = true
                ORDER BY m.name ASC";
        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getResult();
    }

    public function getBoardModules()
    {
        $dql = "SELECT m FROM \Edaha\Entities\Module m
                WHERE m.type = 'board' AND m.is_manage = false
                ORDER BY m.name ASC";
        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getResult();
    }
}