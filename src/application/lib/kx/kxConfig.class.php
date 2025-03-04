<?php

class kxConfig implements ArrayAccess {
    private $container = array();
    
    public function __construct(array $data) {
        $this->container = $data;
    }
    
    public function set($path, &$value) {
				$newValue = self::setRecursive(explode(':', $path), $value);
        $this->container = self::mergeRecursive($this->container, $newValue);
    }
    
    public function setRecursive(array $path, $value) {
        if(!count($path)) return $value;
        
        return array(array_shift($path) => self::setRecursive($path, $value));
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
   * @param array $array1
   * @param mixed $array2
   * @author daniel@danielsmedegaardbuus.dk
   * @return array
   */
    public function &mergeRecursive(array &$array1, &$array2 = null) {
      $merged = $array1;
     
      if (is_array($array2))
        foreach ($array2 as $key => $val)
          if (is_array($array2[$key]))
            $merged[$key] = (isset($merged[$key]) && is_array($merged[$key])) ? self::mergeRecursive($merged[$key], $array2[$key]) : $array2[$key];
          else
            $merged[$key] = $val;
     
      return $merged;
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
    public function offsetSet(mixed $offset, mixed $value): void {
        $this->container[$offset] = $value;
    }
    
    public function offsetGet(mixed $offset): mixed {
        return $this->container[$offset];
    }
    
    public function offsetExists(mixed $offset): bool {
        return array_key_exists($this->container[$offset]);
    }
    
    public function offsetUnset(mixed $offset): void {
        unset($this->container[$offset]);
    }
    /* }}} */
}

class coreConfig {

  /**
   * Fetch the cache array
   *
   * @access  public
   * @return  array caches and caches to load
   */
  public function fetchCaches() {

    /* Apps and modules */
    $cache = array (  'version' =>         
                                array(
                                        'force_load'     => 1,
                                        'recache_file'     => kxFunc::getAppDir( 'core' ) . '/modules/manage/index/index.php',
                                        'recache_class'    => 'manage_core_index_index',
                                        'recache_function' => 'recacheEdahaVersion' 
                                      ),
             'test' =>
             array(
                                        'testing' => 
                                                       array(
                                                              'force_load'     => 0,
                                                              'recache_file'     => kxFunc::getAppDir( 'core' ) . '/modules/manage/addons/addons.php',
                                                              'recache_class'    => 'manage_core_addons_addons',
                                                              'recache_function' => 'recacheApplications' 
                                                            )
                        ),
                      'addons' =>
                                array(
                                        'app_cache' => 
                                                       array(
                                                              'force_load'     => 1,
                                                              'recache_file'     => kxFunc::getAppDir( 'core' ) . '/modules/manage/addons/addons.php',
                                                              'recache_class'    => 'manage_core_addons_addons',
                                                              'recache_function' => 'recacheApplications' 
                                                            ),
                                        'app_menu' => 
                                                       array(
                                                              'force_load'     => 1,
                                                              'recache_file'     => kxFunc::getAppDir( 'core' ) . '/modules/manage/addons/addons.php',
                                                              'recache_class'    => 'manage_core_addons_addons',
                                                              'recache_function' => 'recacheAppMenu' 
                                                            ),
                                        'module_cache' => 
                                                       array(
                                                              'force_load'     => 1,
                                                              'recache_file'     => kxFunc::getAppDir( 'core' ) . '/modules/manage/addons/addons.php',
                                                              'recache_class'    => 'manage_core_addons_addons',
                                                              'recache_function' => 'recacheModules' 
                                                            ),
                                        'hooks_cache' => 
                                                       array(
                                                              'force_load'     => 1,
                                                              'recache_file'     => kxFunc::getAppDir( 'core' ) . '/modules/manage/addons/hooks.php',
                                                              'recache_class'    => 'manage_core_addons_hooks',
                                                              'recache_function' => 'recacheHooks' 
                                                            )
                                      ),
                      'filters' =>         
                                array(
                                        'wordfilters' => 
                                                       array(
                                                              'force_load'     => 1,
                                                              'recache_file'     => kxFunc::getAppDir( 'core' ) . '/modules_admin/posts/filter.php',
                                                              'recache_class'    => 'manage_board_posts_filter',
                                                              'recache_function' => 'recacheWordFilters'
                                                            ),
                                        'spamfilters' => 
                                                       array(
                                                              'force_load'     => 1,
                                                              'recache_file'     => kxFunc::getAppDir( 'core' ) . '/modules_admin/posts/filter.php',
                                                              'recache_class'    => 'manage_board_posts_filter',
                                                              'recache_function' => 'recacheSpamFilters'
                                                            )
                                      ),
                      'attachments' =>
                                array(
                                        'filetypes' =>
                                                     array(
                                                              'force_load'     => 0,
                                                              'recache_file'     => kxFunc::getAppDir( 'board' ) . '/modules/manage/filetypes.php',
                                                              'recache_class'    => 'manage_board_attachments_filetypes',
                                                              'recache_function' => 'recacheFiletypes'
                                                          ),
                                        'embeds' =>  
                                                      array(
                                                              'force_load'     => 0,
                                                              'recache_file'     => kxFunc::getAppDir( 'board' ) . '/modules/manage/embeds.php',
                                                              'recache_class'    => 'manage_board_attachments_embeds',
                                                              'recache_function' => 'recacheEmbeds'
                                                          )
                                      )
                    );
    if (isset(kxEnv::$request['board'])) {
      $cache['boardopts'] = array(
                                kxEnv::$request['board'] =>
                                                      array(
                                                            'force_load'       => 1,
                                                            'recache_file'     => kxFunc::getAppDir( 'board' ) . '/modules/manage/boardopts.php',
                                                            'recache_class'    => 'manage_board_board_boardopts',
                                                            'recache_function' => 'recacheBoardOptions'
                                                            )
                                );
    
    }
    $load = array();
    
    return array( 'caches'      => $cache,
                  'cachetoload' => $load );
  }
}