<?php

namespace kx;

use kx\Interfaces\ConfigInterface;

class kxConfig implements \ArrayAccess, ConfigInterface
{
    public function __construct(
        private array $container = []
    ) {}

    /**
     * Set the configuration keyed by $path to $value.
     */
    public function set(string $path, mixed &$value): void
    {
        $newValue = self::setRecursive(explode(':', $path), $value);
        $this->container = self::mergeRecursive($this->container, $newValue);
    }

    /**
     * Get the config value stored at $path.
     */
    public function get(?string $path = null, mixed $default = null): mixed
    {
        return $this->getRecursive($this->container, strlen($path) ? explode(':', $path) : [], $default);
    }

    // {{{ ArrayAccess implementation
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->container[$offset] = $value;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->container[$offset];
    }

    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->container);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->container[$offset]);
    }
    // }}}

    public static function loadConfigFromDirectory(string $environment, string $config_path): kxConfig
    {
        $configuration = [];

        // Load config
        foreach (self::getConfigFiles($config_path) as $configfile) {
            $configuration = array_merge_recursive(array_reduce(
                array_intersect_key(
                    self::loadConfigFile($configfile),
                    array_flip(['all', $environment])
                ),
                [self::class, 'mergeWrapper']
            ), $configuration);
        }

        return new self($configuration);
    }

    /**
     * Works down the array keyed by $path to set $value.
     */
    private function setRecursive(array $path, mixed $value): array|string
    {
        if (!count($path)) {
            return $value;
        }

        return [array_shift($path) => self::setRecursive($path, $value)];
    }

    /**
     * Traverses $root via $path to return the configuration value.
     */
    private function getRecursive(array|string $root, array $path = [], mixed $default = null): mixed
    {
        if (is_null($root)) {
            return $default;
        }
        if (!count($path)) {
            return $root;
        }
        if (!is_array($root)) {
            return $default;
        }

        $node = array_shift($path);

        return array_key_exists($node, $root) ? self::getRecursive($root[$node], $path, $default) : $default;
    }

    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => 'new value');
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param mixed $array2
     *
     * @author daniel@danielsmedegaardbuus.dk
     */
    private static function &mergeRecursive(array &$array1, &$array2 = null): array
    {
        $merged = $array1;

        if (is_array($array2)) {
            foreach ($array2 as $key => $val) {
                if (is_array($array2[$key])) {
                    $merged[$key] = (isset($merged[$key]) && is_array($merged[$key])) ? self::mergeRecursive($merged[$key], $array2[$key]) : $array2[$key];
                } else {
                    $merged[$key] = $val;
                }
            }
        }

        return $merged;
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
        return kxYml::loadFile($configfile);
    }
}
