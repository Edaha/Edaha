<?php

class kxConfig implements ArrayAccess {
  private $container = array();

  public function __construct(array $data) {
    $this->container = $data;
  }
  
  public function set($path, $value) {
    $this->container = array_merge_recursive($this->container, self::setRecursive(explode(':', $path), $value));
  }

  public function setRecursive(array $path, $value) {
    if(!count($path)) return $value;
    
    return array(array_shift($path) => self::setRecursive($path, $value));
  }

  public function get($path = null, $default = null) {
    return $this->getRecursive($this->container, strlen($path) ? explode(':', $path) : array(), $default);
  }

  public function getRecursive($root, $path = array(), $default = null) {
    if(is_null($root)) return $default;
    if(!count($path)) return $root;
    if(!is_array($root)) return $default;

    $node = array_shift($path);

    return array_key_exists($node, $root) ? self::getRecursive($root[$node], $path, $default) : $default;
  }

  public function getContainer() {
    return $this->container;
  }
  /* {{{ ArrayAccess implementation */
  public function offsetSet($offset, $value) {
    $this->container[$offset] = $value;
  }

  public function offsetGet($offset) {
    return $this->container[$offset];
  }

  public function offsetExists($offset) {
    return array_key_exists($this->container[$offset]);
  }

  public function offsetUnset($offset) {
    unset($this->container[$offset]);
  }
  /* }}} */
}
