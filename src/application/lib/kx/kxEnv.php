<?php

namespace kx;

class kxEnv
{
    public static $current_application = '';
    public static $current_module = '';
    public static $current_section = '';

    public static ?kxRequest $request;

    protected static $_coreConfig = [];
    protected static $_appConfig = [];

    private static kxEnv $instance;
    private static $cache;

    private function __construct(
        private string $environment,
        private kxConfig $configuration
    ) {
        $this->environment = $environment;
        $this->configuration = $configuration;
    }

    /**
     * Get the kxEnv instance if it exists.
     */
    public static function getInstance(): ?kxEnv
    {
        if (!self::$instance instanceof self) {
            return null;
        }

        return self::$instance;
    }

    /**
     * Set up the environment.
     *
     * @param string $environment The name of the environment (e.g. 'dev', 'prod')
     * @param string $configdir   The directory storing the configuration YAML
     */
    public static function initialize(string $environment, string $configdir): void
    {
        if (isset(self::$instance) && self::$instance instanceof self) {
            return;
        }

        self::createInstance($environment, $configdir);
        self::setupAutoloader();
        self::$request = kxRequest::getInstance();
        self::setContextVariables();

        // Load the cache
        // self::$cache = kxCache::instance();
    }

    /**
     * Loads data from kx core config.
     */
    public static function fetchCoreConfig(string $type): coreConfig
    {
        if (!isset(self::$_coreConfig[$type]) || !\is_array(self::$_coreConfig[$type])) {
            self::loadCoreConfig();
            $return = self::$_coreConfig['core_config_class']->fetchCaches();
            self::$_coreConfig['cache'] = \is_array($return['caches']) ? $return['caches'] : [];
            self::$_coreConfig['cachetoload'] = \is_array($return['cachetoload']) ? $return['cachetoload'] : [];
        }

        return self::$_coreConfig[$type];
    }

    /**
     * Fetches apps core variable data.
     *
     * @param string $app  App dir
     * @param string $type Type of variable to return ('cache' or 'cachetoload')
     *
     * @return array The app configuration
     */
    public static function fetchAppConfig(string $app, string $type): array
    {
        if (!isset(self::$_appConfig[$app][$type]) or !\is_array(self::$_appConfig[$app][$type])) {
            self::loadAppConfig($app);
        }

        return self::$_appConfig[$app][$type] ?? [];
    }

    /**
     * Get a specific configuration value.
     *
     * @param ?string $path    The path of the configuration value to return
     * @param mixed   $default The value to return if the configuration is not found
     */
    public static function get(?string $path = null, mixed $default = null): mixed
    {
        // Shortcut for getting stuff from the cache (without having to use the cache object directly)
        if (0 === strpos($path, 'cache')) {
            // Cache doesn't care about $default
            return self::getInstance()->getCache()->get($path);
        }

        return self::getInstance()->getConfig()->get($path, $default);
    }

    /**
     * Get the configuration of the environment.
     */
    public static function dumpConfig(): kxConfig
    {
        return self::getInstance()->getConfig();
    }

    /**
     * Set a configuration key to a specific volume.
     *
     * @param string $path  The configuration key to set
     * @param mixed  $value The value to set the configuration key to
     */
    public static function set(string $path, mixed $value): void
    {
        // Shortcut for setting the cache (without having to use the cache object directly)
        if (0 === strpos($path, 'cache')) {
            self::getInstance()->getCache()->set($path, $value);
        }
        self::getInstance()->getConfig()->set($path, $value);
    }

    private static function createInstance(string $environment, string $config_path): void
    {
        // Set our instance, load kxConfig
        self::$instance = new self($environment, kxConfig::loadConfigFromDirectory($environment, $config_path));
    }

    private static function setupAutoloader(): void
    {
        // Add any classes we want added to the autoloader.
        foreach (self::get('kx:autoload:load') as $repo => $opts) {
            self::set(sprintf('kx:autoload:repository:%s:id', $repo), kxAutoload::registerRepository(sprintf('%s/%s/%s', KX_ROOT, 'application/lib', $opts['path']), [
                'prefix' => $opts['prefix'],
            ]));
        }
    }

    private static function setContextVariables(): void
    {
        // Grab our app
        $_application = preg_replace(
            '/[^a-zA-Z0-9\-\_]/',
            '',
            '' != self::$request->get('app') ? self::$request->get('app') : 'core'
        );

        // Make sure we get (hopefully) a string
        if (\is_array($_application)) {
            $_application = array_shift($_application);
        }

        define('KX_CURRENT_APP', $_application);

        self::$current_application = KX_CURRENT_APP;
        self::$current_module = self::$request->get('module') ? kxFunc::alphaNum(self::$request->get('module')) : '';
        self::$current_section = self::$request->get('section') ? kxFunc::alphaNum(self::$request->get('section')) : '';
    }

    /**
     * Loads kx core configuration class.
     */
    private static function loadCoreConfig(): void
    {
        if (!(isset(self::$_coreConfig['core_config_class']) and is_object(self::$_coreConfig['core_config_class']))) {
            self::$_coreConfig['core_config_class'] = new coreConfig();
        }
    }

    /**
     * Loads the configuration for an application.
     *
     * @param string $app The name of the application
     */
    private static function loadAppConfig(string $app): void
    {
        $CACHE = $LOAD = [];

        if (!isset(self::$_appConfig[$app])) {
            $file = kxFunc::getAppDir($app).'/appConfig.php';

            if (is_file($file)) {
                require $file;

                self::$_appConfig[$app]['cache'] = $CACHE;
                self::$_appConfig[$app]['cachetoload'] = $LOAD;
            }
        }
    }

    /**
     * Get the environment's configuration.
     */
    private function getConfig(): kxConfig
    {
        return $this->configuration;
    }

    /**
     * Get the environment's cache object.
     */
    private function getCache(): mixed
    {
        return self::$cache;
    }
}
