<?php

namespace kx;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use kx\Exceptions\kxException;
use kx\Interfaces\ConfigInterface;

class kxOrm
{
    public const array SUPPORTED_ADAPTERS = [
        'pdo_sqlite',
        'pdo_mysql',
        'pdo_pgsql',
    ];

    public static string $adapter = '' {
        set {
            if (!\in_array($value, self::SUPPORTED_ADAPTERS)) {
                throw new kxException("Unsupported adapter: {$value}.");
            }
            if ('' != self::$adapter) {
                throw new kxException('Cannot modify kxOrm adapter after initialization.');
            }
            self::$adapter = $value;
        }
    }

    public static ?EntityManager $entityManager = null;
    private static ?self $instance = null;

    private static array $connectionParams {
        get {
            return match (self::$adapter) {
                'pdo_sqlite' => self::getSqliteConnectionParams(),
                'pdo_mysql' => self::getMysqlConnectionParams(),
                'pdo_pgsql' => self::getPgsqlConnectionParams(),
                default => throw new kxException('Unsupported adapter.')
            };
        }
    }

    private static ConfigInterface $config;

    public static function getEntityManager(): EntityManager
    {
        if (!isset(self::$instance)) {
            throw new kxException('kxOrm has not been initialized.');
        }

        if (null === self::$entityManager) {
            $doctrine_config = ORMSetup::createAttributeMetadataConfiguration(
                paths: [KX_ROOT.'/application/lib/Edaha/Entities'],
                isDevMode: true,
            );
            $doctrine_config->enableNativeLazyObjects(true);

            $connection = DriverManager::getConnection(self::$connectionParams, $doctrine_config);

            self::$entityManager = new EntityManager($connection, $doctrine_config);
        }

        return self::$entityManager;
    }

    public static function initialize(ConfigInterface $config): void
    {
        if (isset(self::$instance)) {
            throw new kxException('Cannot re-initialize kxOrm.');
        }

        self::$config = $config;
        self::loadAdapter();
        self::$instance = new self();
    }

    public static function persistImmediately($entity)
    {
        if (null === self::$entityManager) {
            self::getEntityManager();
        }
        self::$entityManager->persist($entity);
        self::$entityManager->flush();
    }

    public function removeImmediately($entity)
    {
        if (null === self::$entityManager) {
            self::getEntityManager();
        }
        self::$entityManager->remove($entity);
        self::$entityManager->flush();
    }

    private static function loadAdapter(): void
    {
        self::$adapter = self::$config->get('kx:db:adapter', 'sqlite');
    }

    private static function getSqliteConnectionParams(): array
    {
        return [
            'driver' => 'pdo_sqlite',
            'path' => KX_ROOT.'/'.self::$config->get('kx:db:sqlite:dbname', 'edaha').'.sqlite',
        ];
    }

    private static function getMysqlConnectionParams(): array
    {
        return [
            'driver' => 'pdo_mysql',
            'host' => self::$config->get('kx:db:mysql:host', 'localhost'),
            'port' => self::$config->get('kx:db:mysql:port', 3306),
            'dbname' => self::$config->get('kx:db:mysql:dbname', 'edaha'),
            'user' => self::$config->get('kx:db:mysql:user', 'edaha'),
            'password' => self::$config->get('kx:db:mysql:password', 'edaha'),
        ];
    }

    private static function getPgsqlConnectionParams(): array
    {
        return [
            'driver' => 'pdo_pgsql',
            'host' => self::$config->get('kx:db:pgsql:host', 'localhost'),
            'port' => self::$config->get('kx:db:pgsql:port', 5432),
            'dbname' => self::$config->get('kx:db:pgsql:dbname', 'edaha'),
            'user' => self::$config->get('kx:db:pgsql:user', 'edaha'),
            'password' => self::$config->get('kx:db:pgsql:password', 'edaha'),
        ];
    }
}
