<?php
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

class kxOrm
{
    public static ?EntityManager $entityManager = null;
    
    public static function getEntityManager(): EntityManager
    {
        if (self::$entityManager === null) {
            $config = ORMSetup::createAttributeMetadataConfiguration(
                paths: [ __DIR__ . '/../Edaha/Entities'],
                isDevMode: true,
            );

            $connection = DriverManager::getConnection([
                'driver' => 'pdo_sqlite',
                'path' => __DIR__ . '/db.sqlite',
            ], $config);

            self::$entityManager = new EntityManager($connection, $config);
        }
        return self::$entityManager;
    }

    public static function persistImmediately($entity)
    {
        if (self::$entityManager === null) {
            self::getEntityManager();
        }
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function removeImmediately($entity)
    {
        if (self::$entityManager === null) {
            self::getEntityManager();
        }
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}