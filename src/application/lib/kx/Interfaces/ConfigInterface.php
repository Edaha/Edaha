<?php

namespace kx\Interfaces;

interface ConfigInterface
{
    public function set(string $path, mixed $value): mixed;

    public function get(string $path, mixed $default): mixed;
}
