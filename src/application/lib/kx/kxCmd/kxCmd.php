<?php

namespace kx\kxCmd;

use Doctrine\Orm\EntityManager;
use kx\kxEnv;
use kx\kxOrm;
use kx\kxRequest;

/**
 * The prototype for "pages" within the application.
 */
abstract class kxCmd
{
    protected kxEnv $environment;

    /**
     * TODO Delete.
     */
    protected $db;

    protected EntityManager $entityManager;

    protected kxRequest $request;

    /**
     * Constructor.
     */
    final public function __construct() {}

    /**
     * Make shortcuts for kxEnv and kxDB.
     */
    public function makeRegistryShortcuts(kxEnv $environment): void
    {
        $this->environment = $environment;
        $this->request = $environment->request;
        $this->entityManager = kxOrm::getEntityManager();
    }

    /**
     * Wrapper for makeRegistryShortcuts() and exec().
     */
    public function execute(kxEnv $environment): void
    {
        $this->makeRegistryShortcuts($environment);
        $this->exec($environment);
    }

    /**
     * Do execute method (must be overriden).
     */
    abstract protected function exec(kxEnv $environment);
}
