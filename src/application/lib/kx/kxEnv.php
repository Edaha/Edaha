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

    private string $environment;
    private kxConfig $configuration;

    private function __construct(string $environment, kxConfig $configuration)
    {
        $this->environment = $environment;
        $this->configuration = $configuration;
    }

    /**
     * Get the kxEnv instance if it exists.
     */
    public static function getInstance(): ?kxEnv
    {
        if (!self::$instance instanceof self) {
            return;
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
        if (self::$instance instanceof self) {
            return;
        }

        $configuration = [];

        // Load config
        foreach (self::getConfigFiles($configdir) as $configfile) {
            $configuration = array_merge_recursive(array_reduce(
                array_intersect_key(
                    self::loadConfigFile($configfile),
                    array_flip(['all', $environment])
                ),
                [self::class, 'mergeWrapper']
            ), $configuration);
        }

        // Set our instance, load kxConfig
        self::$instance = new self($environment, new kxConfig($configuration));

        // Add any classes we want added to the autoloader.
        foreach (kxEnv::get('kx:autoload:load') as $repo => $opts) {
            kxEnv::set(sprintf('kx:autoload:repository:%s:id', $repo), kxAutoload::registerRepository(sprintf('%s/%s/%s', KX_ROOT, 'application/lib', $opts['path']), [
                'prefix' => $opts['prefix'],
            ]));
        }

        self::$request = kxRequest::getInstance();

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

        // Load the cache
        // self::$cache = kxCache::instance();
    }

    /**
     * Loads kx core configuration class.
     */
    public static function loadCoreConfig(): void
    {
        if (!(isset(self::$_coreConfig['core_config_class']) and is_object(self::$_coreConfig['core_config_class']))) {
            self::$_coreConfig['core_config_class'] = new coreConfig();
        }
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
     * Loads the configuration for an application.
     *
     * @param string $app The name of the application
     */
    public static function loadAppConfig(string $app): void
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
    public static function set(string $path, mixed $value)
    {
        // Shortcut for setting the cache (without having to use the cache object directly)
        if (0 === strpos($path, 'cache')) {
            return self::getInstance()->getCache()->set($path, $value);
        }
        self::getInstance()->getConfig()->set($path, $value);
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

    /**
     * Wrapper for array_merge_recursive that should actually be an anonymous function.
     *
     * @param mixed $base
     * @param mixed $next
     */
    private static function mergeWrapper($base, $next): array
    {
        return array_merge_recursive(\is_null($base) ? [] : $base, $next);
    }

    /**
     * Get an array containing the paths of all config files.
     *
     * @return bool|string[]
     */
    private static function getConfigFiles(string $configdir): array|bool
    {
        return glob($configdir.'/*.yml.php');
    }

    /**
     * Load a configuration file into an array.
     *
     * @param string $configfile The path of the configuraton file
     */
    private static function loadConfigFile(string $configfile): array
    {
        if (self::isCached($configfile)) {
            return self::loadCached($configfile);
        }

        return kxYml::loadFile($configfile);
    }

    /**
     * Do nothing lol.
     *
     * @param string $configfile The configuration file to get false about
     *
     * @return bool Always false
     */
    private static function isCached(string $configfile): bool
    {
        return false;
    }
}
