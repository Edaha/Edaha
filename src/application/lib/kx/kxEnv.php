<?php

namespace kx;

use kx\Exceptions\kxException;
use kx\Interfaces\ConfigInterface;
use kx\Message\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

class kxEnv implements ConfigInterface
{
    public string $current_application = '';
    public string $current_module = '';
    public string $current_section = '';

    public static ServerRequestInterface $request;

    public static string $requestClass = ServerRequest::class;

    public static ConfigInterface $configuration;

    public static $configClass = kxConfig::class;

    private static self $instance;
    private static $cache;

    private function __construct(
        public string $environment_name,
    ) {
        self::$configuration = self::$configClass::loadConfigFromDirectory($environment_name);
        self::$request = new self::$requestClass();
    }

    public static function setRequestClass(string $className): void
    {
        if (!class_exists($className::class)) {
            throw new kxException("Class {$className} is not available.");
        }

        $reflection = new \ReflectionClass($className);

        if (!\in_array('ServerRequestInterface', $reflection->getInterfaceNames())) {
            throw new kxException("Class {$className} does not implement ConfigInterface.");
        }

        self::$requestClass = $className::class;
    }

    public static function setConfigClass(string $className): void
    {
        if (!class_exists($className::class)) {
            throw new kxException("Class {$className} is not available.");
        }

        $reflection = new \ReflectionClass($className);

        if (!\in_array('ServerRequestInterface', $reflection->getInterfaceNames())) {
            throw new kxException("Class {$className} does not implement ConfigInterface.");
        }

        self::$configClass = $className::class;
    }

    /**
     * Get the kxEnv instance if it exists.
     */
    public static function getInstance(): ?kxEnv
    {
        if (!isset(self::$instance) || !self::$instance instanceof self) {
            throw new kxException('kxEnv has not been instantiated.');
        }

        return self::$instance;
    }

    /**
     * Set up the environment.
     *
     * @param string $environment_name e.g. 'dev', 'prod'
     */
    public static function initialize(
        string $environment_name
    ): self {
        if (isset(self::$instance) && self::$instance instanceof self) {
            throw new kxException('Cannot re-initialize kxEnv.');
        }

        self::$instance = new self(
            $environment_name
        );

        self::$instance->setContextVariables();

        // Load the cache
        // self::$cache = kxCache::instance();

        return self::$instance;
    }

    /**
     * Set a configuration key to a specific volume.
     *
     * @param string $path  The configuration key to set
     * @param mixed  $value The value to set the configuration key to
     */
    public function set(string $path, mixed $value): mixed
    {
        // Shortcut for setting the cache (without having to use the cache object directly)
        if (0 === strpos($path, 'cache')) {
            self::getInstance()->cache->set($path, $value);
        }
        self::getInstance()->configuration->set($path, $value);
    }

    /**
     * Get a specific configuration value.
     *
     * @param ?string $path    The path of the configuration value to return
     * @param mixed   $default The value to return if the configuration is not found
     */
    public function get(?string $path = null, mixed $default = null): mixed
    {
        // Shortcut for getting stuff from the cache (without having to use the cache object directly)
        if (0 === strpos($path, 'cache')) {
            // Cache doesn't care about $default
            return self::getInstance()->cache->get($path);
        }

        return self::getInstance()->configuration->get($path, $default);
    }

    private function setContextVariables(): void
    {
        // Grab our app
        $_application = preg_replace(
            '/[^a-zA-Z0-9\-\_]/',
            '',
            '' != $this->request->get('app') ? $this->request->get('app') : 'core'
        );

        // Make sure we get (hopefully) a string
        if (\is_array($_application)) {
            $_application = array_shift($_application);
        }

        define('KX_CURRENT_APP', $_application);

        $this->current_application = KX_CURRENT_APP;
        $this->current_module = $this->request->get('module') ? kxFunc::alphaNum($this->request->get('module')) : '';
        $this->current_section = $this->request->get('section') ? kxFunc::alphaNum($this->request->get('section')) : '';
    }
}
