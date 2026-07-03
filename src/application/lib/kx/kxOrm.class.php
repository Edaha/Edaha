<?php
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

class kxOrm
{
    public static ?EntityManager $entityManager = null;
    
    private static function getSqliteConnectionParams(): array
    {
        return [
            'driver' => 'pdo_sqlite',
            'path' => realpath(__DIR__ . '/db.sqlite'),
        ];
    }

    public static function getEntityManager(): EntityManager
    {
        if (self::$entityManager === null) {
            $config = ORMSetup::createAttributeMetadataConfiguration(
                paths: [ __DIR__ . '/../Edaha/Entities'],
                isDevMode: true,
            );
            $config->enableNativeLazyObjects(true);

            $connectionParams = self::getSqliteConnectionParams();
            $connection = DriverManager::getConnection($connectionParams, $config);

            self::$entityManager = new EntityManager($connection, $config);
        }
        return self::$entityManager;
    }

    public static function persistImmediately($entity)
    {
        if (self::$entityManager === null) {
            self::getEntityManager();
        }
        self::$entityManager->persist($entity);
        self::$entityManager->flush();
    }

    public function removeImmediately($entity)
    {
        if (self::$entityManager === null) {
            self::getEntityManager();
        }
        self::$entityManager->remove($entity);
        self::$entityManager->flush();
    }
}