<?php

namespace kx;

use Spyc;

class kxYml
{
    private static bool $useSpyc = true;
    private static bool $init = false;

    /**
     * Takes a $path to a .yml file and returns an array representation of it.
     */
    public static function loadFile(string $path): array
    {
        self::_init();

        return self::$useSpyc ? \Spyc::YAMLLoad($path) : syck_load(file_get_contents($path));
    }

    /**
     * Takes a $string representation of a YAML document and returns an array representation of it.
     */
    public static function loadString(string $string): array
    {
        self::_init();

        return self::$useSpyc ? \Spyc::YAMLLoadString($string) : syck_load($string);
    }

    /**
     * Take an associative $array and return a string containing its YAML representation.
     */
    public static function dump(array $array): string
    {
        self::_init();

        return self::$useSpyc ? \Spyc::YAMLDump($array) : syck_dump($array);
    }

    /**
     * Sets up the class, checks if syck is installed, otherwise uses Spyc.
     */
    private static function _init(): bool
    {
        if (!self::$init) {
            if (function_exists('syck_load')) {
                self::$useSpyc = false;
            }
        }

        self::$init = true;

        return self::$init;
    }
}
