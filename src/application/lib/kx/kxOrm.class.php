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
            'path' => KX_ROOT . '/' . kxEnv::Get('kx:db:sqlite:dbname', 'db') . '.sqlite',
        ];
    }

    private static function getMysqlConnectionParams(): array
    {
        return [
            'driver' => 'pdo_mysql',
            'host' => kxEnv::Get('kx:db:mysql:host'),
            'port' => kxEnv::Get('kx:db:mysql:port'),
            'dbname' => kxEnv::Get('kx:db:mysql:dbname'),
            'user' => kxEnv::Get('kx:db:mysql:user'),
            'password' => kxEnv::Get('kx:db:mysql:password'),
        ];
    }

    private static function getPgsqlConnectionParams(): array
    {
        return [
            'driver' => 'pdo_pgsql',
            'host' => kxEnv::Get('kx:db:pgsql:host'),
            'port' => kxEnv::Get('kx:db:pgsql:port'),
            'dbname' => kxEnv::Get('kx:db:pgsql:dbname'),
            'user' => kxEnv::Get('kx:db:pgsql:user'),
            'password' => kxEnv::Get('kx:db:pgsql:password'),
        ];
    }

    public static function getEntityManager(): EntityManager
    {
        if (self::$entityManager === null) {
            $config = ORMSetup::createAttributeMetadataConfiguration(
                paths: [ KX_ROOT . '/application/lib/Edaha/Entities'],
                isDevMode: true,
            );
            $config->enableNativeLazyObjects(true);

            $connectionParams = match (kxEnv::Get('kx:db:adapter')) {
                'pdo_sqlite' => self::getSqliteConnectionParams(),
                'pdo_mysql' => self::getMysqlConnectionParams(),
                'pdo_pgsql' => self::getPgsqlConnectionParams(),
                default => throw new InvalidArgumentException('Unsupported database adapter')
            };
            
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