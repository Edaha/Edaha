<<<<<<< HEAD
<?php

class kxConfig implements ArrayAccess {
    private $container = array();
    
    public function __construct(array $data) {
        $this->container = $data;
    }
    
    public function set($path, &$value) {
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

class coreConfig {

  /**
   * Fetch the cache array
   *
   * @access	public
   * @return	array caches and caches to load
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
                                                              'recache_class'    => 'manage_core_posts_filter',
                                                              'recache_function' => 'recacheWordFilters'
                                                            ),
                                        'spamfilters' => 
                                                       array(
                                                              'force_load'     => 1,
                                                              'recache_file'     => kxFunc::getAppDir( 'core' ) . '/modules_admin/posts/filter.php',
                                                              'recache_class'    => 'manage_core_posts_filter',
                                                              'recache_function' => 'recacheSpamFilters'
                                                            )
                                      )
                    );

    $load = array();
    
    return array( 'caches'      => $cache,
                  'cachetoload' => $load );
  }
=======
<?php

class kxConfig implements ArrayAccess {
    private $container = array();
    
    public function __construct(array $data) {
        $this->container = $data;
    }
    
    public function set($path, &$value) {
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

class coreConfig {

  /**
   * Fetch the cache array
   *
   * @access	public
   * @return	array caches and caches to load
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
>>>>>>> ec57997... Test commit to see if this breaks git
}