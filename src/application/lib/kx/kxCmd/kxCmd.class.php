<?php

abstract class kxCmd
{
    /**
     * Environment Shortcuts.
     *
     * @var object
     */
    protected $environment;

    /**
     * kxDB instance.
     *
     * @var object
     */
    protected $db;

    /**
     * kxOrm instance.
     *
     * @var object
     */
    protected $entityManager;

    /**
     * The request infortmation.
     *
     * @var object
     */
    protected $request;

    /**
     * Constructor.
     */
    final public function __construct() {}

    /**
     * Make shortcuts for kxEnv and kxDB.
     *
     * @param	object	kxEnv reference
     */
    public function makeRegistryShortcuts(kxEnv $environment)
    {
        $this->environment = $environment;
        $this->request = kxEnv::$request;
        $this->entityManager = kxOrm::getEntityManager();
    }

    /**
     * Wrapper for makeRegistryShortcuts() and exec().
     *
     * @param	object	kxEnv reference
     */
    public function execute(kxEnv $environment)
    {
        $this->makeRegistryShortcuts($environment);
        $this->exec($environment);
    }

    /**
     * Do execute method (must be overriden).
     *
     * @param	object	kxEnv reference
     */
    abstract protected function exec(kxEnv $environment);
}
