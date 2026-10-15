<?php

namespace Edaha\Entities;

use Doctrine\ORM\EntityRepository;

class ModuleRepository extends EntityRepository
{
    public function getAllModules()
    {
        $dql = 'SELECT m FROM \\Edaha\\Entities\\Module m
                ORDER BY m.installed_at DESC';
        $query = $this->getEntityManager()->createQuery($dql);

        return $query->getResult();
    }

    public function getManagementModules()
    {
        $dql = 'SELECT m FROM \\Edaha\\Entities\\Module m
                WHERE m.is_manage = true
                ORDER BY m.name ASC';
        $query = $this->getEntityManager()->createQuery($dql);

        return $query->getResult();
    }

    public function getBoardModules()
    {
        $dql = "SELECT m FROM \\Edaha\\Entities\\Module m
                WHERE m.type = 'board' AND m.is_manage = false
                ORDER BY m.name ASC";
        $query = $this->getEntityManager()->createQuery($dql);

        return $query->getResult();
    }

    public function getCoreModules()
    {
        $dql = "SELECT m FROM \\Edaha\\Entities\\Module m
                WHERE m.type = 'core' AND m.is_manage = false
                ORDER BY m.name ASC";
        $query = $this->getEntityManager()->createQuery($dql);

        return $query->getResult();
    }

    public function getPostPreprocessorModules()
    {
        $dql = "SELECT m FROM \\Edaha\\Entities\\Module m
                WHERE m.type = 'post_preprocessor' AND m.is_manage = false
                ORDER BY m.name ASC";
        $query = $this->getEntityManager()->createQuery($dql);

        return $query->getResult();
    }

    public function getPostProcessorModules()
    {
        $dql = "SELECT m FROM \\Edaha\\Entities\\Module m
                WHERE m.type = 'post_processor' AND m.is_manage = false
                ORDER BY m.name ASC";
        $query = $this->getEntityManager()->createQuery($dql);

        return $query->getResult();
    }

    public function getPostPostprocessorModules()
    {
        $dql = "SELECT m FROM \\Edaha\\Entities\\Module m
                WHERE m.type = 'post_postprocessor' AND m.is_manage = false
                ORDER BY m.name ASC";
        $query = $this->getEntityManager()->createQuery($dql);

        return $query->getResult();
    }
}
