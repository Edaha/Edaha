<?php

namespace kx;

class kxYml
{
    /**
     * Takes a $path to a .yml file and returns an array representation of it.
     */
    public static function loadFile(string $path): array
    {
        return \Spyc::YAMLLoad($path);
    }

    /**
     * Takes a $string representation of a YAML document and returns an array representation of it.
     */
    public static function loadString(string $string): array
    {
        return \Spyc::YAMLLoadString($string);
    }

    /**
     * Take an associative $array and return a string containing its YAML representation.
     */
    public static function dump(array $array): string
    {
        return \Spyc::YAMLDump($array);
    }
}
