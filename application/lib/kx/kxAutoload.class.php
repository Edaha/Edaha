<?php

class kxAutoload {
    private static $instances = array();
    
    private $repository = false;
    private $options = array();
    
    private function __construct($repository, $options = array()) {
        $this->repository = $repository;
        $this->options = $options;
    }
    
    public static function unregisterRepository($repository) {
        if(!array_key_exists($repository, self::$instances)) return false;
        
        $instance = self::$instances[$repository];
        return $instance->unregister();
    }
    
    public static function registerRepository($path, $options = array()) {
        $id = md5($path . time());
        
        self::$instances[$id] = new self($path, $options);
        
        return spl_autoload_register(array(self::$instances[$id], 'autoload')) ? $id : false;
    }
    
    public function autoload($class) {
        
        if(!preg_match(
            sprintf(
                '/^%s[a-zA-Z0-9_\x7f-\xff]*$/',
                array_key_exists('prefix', $this->options) ?
                        $this->options['prefix'] :
                            '[a-zA-Z0-9_\x7f-\xff]'
            ), $class)) return false;
        
        return self::searchRepository($class, $this->repository, $this->options);
    }
    
    public static function searchRepository($class, $path, $options = array()) {
        $glob_pattern = array_key_exists('glob', $options) ? $options['glob'] : '*';
        
        foreach(glob($path .'/'. $glob_pattern) as $node) {
            if(is_dir($node)) {
                if(self::searchRepository($class, $node, $options)) {
                    return true; // Return if subdir contained hit
                } else {
                    continue;
                }
            }
            
            if(!preg_match(sprintf('/^%s\.class\.php$/', $class), basename($node))) continue; // Skip over file if regex does not match
            
            require($node); // Potential target found
            if(class_exists($class, false)) return true; // Only return true if we have verified the class is loaded
        }
        
        return false;
    }
}
