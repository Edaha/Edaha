<?php

class kxCache {
  /**
   * Instance
   *
   * @var    object
   */
  private static $_instance;

  /**
   * Database instance
   *
   * @var    array
   */
  protected $save_options  = array();

  /**
   * External cache library gets stored here
   */
  protected static $cacheLib;

  /**
   * Initialized flag
   *
   * @var    bool
   */
  protected static $initiated = FALSE;

  /**
   * Generic data storage
   *
   * @var    array
   */
  protected static $data  = array();

  /**
   * Initialize singleton
   *
   * @return  object
   */
  static public function instance() {
    if (empty(self::$_instance)) {
      self::$_instance = new self();
      self::$_instance->init();
    }
    return self::$_instance;
  }
  
  /**
   * Get started
   */
  private function init() {
    if (self::$initiated !== TRUE) {
      //---------------------------------------------------------------------------------
      // Are we using a caching engine?
      // Check in the following order (most ideal to least):
      // WinCache, APC, Memcache, Xcache, eaccelerator (yuck), disk cache (double yuck)
      //---------------------------------------------------------------------------------

      // Wincache
      if(function_exists('wincache_ucache_info') && kxEnv::Get('kx:cache:wincache')) {
        require(KX_LIB.'/kxCache/cacheInterface.php');
        require(KX_LIB.'/kxCache/cacheWincache.php');
        self::$cacheLib = new cacheWincache(kxEnv::Get('kx:paths:main:path'));
      }
      // APC
      else if( function_exists('apc_cache_info') && kxEnv::Get('kx:cache:apc')) {
        require(KX_LIB.'/kxCache/engines/cacheInterface.php');
        require(KX_LIB.'/kxCache/engines/cacheApc.php');
        self::$cacheLib = new classCacheApc(kxEnv::Get('kx:paths:main:path'));
      }
      // Memcache
      else if(function_exists('memcache_connect') && kxEnv::Get('kx:cache:memcache:enabled')){
        require(KX_LIB.'/kxCache/engines/cacheInterface.php');
        require(KX_LIB.'/kxCache/engines/cacheMemcache.php');
        self::$cacheLib = new classCacheMemcache(kxEnv::Get('kx:paths:main:path'), kxEnv::Get('kx:cache:memcache'));
      }
      // XCache
      else if( function_exists('xcache_info') && kxEnv::Get('kx:cache:xcache')) {
        require(KX_LIB.'/kxCache/engines/cacheInterface.php');
        require(KX_LIB.'/kxCache/engines/cacheXcache.php');
        require( IPS_KERNEL_PATH.'classCacheXcache.php' );/*noLibHook*/
        self::$cacheLib = new classCacheXcache(kxEnv::Get('kx:paths:main:path'));
      }
      // Eaccelerator
      else if(function_exists('eaccelerator_put') && kxEnv::Get('kx:cache:eaccelerator')) {
        require(KX_LIB.'/kxCache/engines/cacheInterface.php');
        require(KX_LIB.'/kxCache/engines/cacheEaccelerator.php');
        self::$cacheLib = new classCacheEaccelerator(kxEnv::Get('kx:paths:main:path'));
      }
      // Diskcache
      else if(kxEnv::Get('kx:cache:diskcache')) {
        require(KX_LIB.'/kxCache/engines/cacheInterface.php');
        require(KX_LIB.'/kxCache/engines/cacheDisk.php');
        self::$cacheLib = new classCacheDiskcache(kxEnv::Get('kx:paths:main:path'));
      }

      if(is_object(self::$cacheLib) && self::$cacheLib->fail) {
        // Failsafe in case the cache library somehow manages to load despite not having it installed?
        self::$cacheLib = NULL;
      }

      
      $caches = array();
      for ($i = 0; $i < 2; $i++) {
        if ($i == 0) {
          // Load the global cache
          $cacheData = kxEnv::fetchCoreConfig('cache');
          $caches      = self::_implodeConfig($cacheData);
          $loads       = kxEnv::fetchCoreConfig('cachetoload');
        }
        else {
          // Load the cache for this app
          $cacheData = kxEnv::fetchAppConfig( KX_CURRENT_APP, 'cache' );
          $caches = self::_implodeConfig($cacheData);
          $loads  = kxEnv::fetchAppConfig( KX_CURRENT_APP, 'cachetoload' );
        }
        if (is_array($caches)) {
          foreach($caches as $path => $info) {
            
            if (!IN_MANAGE && !empty($info['manage'])) {
              continue;
            }
            if ($info['force_load']) {
              $loadCaches[$path] = $path;
            }
          }

          if(count($loads)) {
            foreach(array_keys($loads) as $path) {
              $loadCaches[$path] = $path;
            }
          }
        }
      }
      // Let's do it
      self::_loadCaches($loadCaches);
    }

    self::$initiated = TRUE;
  }

  /**
   * Load caches
   *
   * @param  array   Caches to load
   * @return  void
   */
  protected static function _loadCaches($caches=array()) {
    if (!is_array($caches) || empty($caches)) {
      return NULL;
    }

    foreach($caches as $cache) {
      self::$data[$cache] = NULL;
    }
    
    // Use the alternate cache if we have it
    if (is_object(self::$cacheLib)) {
      $tempCache    = array();
      
      foreach($caches as $path) {
        $tempCache[$path] = self::$cacheLib->get($path);

        if ($tempCache[$path]) {
          if (is_string($tempCache[$path]) && strpos($tempCache[$path], "a:") !== false) {
            $this->data[ $path ] = unserialize($tempCache[$path]);
            
            // If for some reason unserialize doesn't work, then we want to try to use the db instead.
            if(!is_array($this->data[ $path ]) || !count($this->data[ $path ])) {
              continue;
            }
          }
          else if ($tempCache[$path] != "NULL"){
            $this->data[$path] = $tempCache[$path];
          }
          unset($caches[$path]);
        }
      }
      unset($tempCache);
    }

    if(!empty($caches)){

      // If we still have anything leftover (or aren't using an alternate cache), let's try using the database cache
      $dbCaches = kxDB::getInstance()->select("cache")
                                     ->fields("cache")
                                     ->condition("cache_path", $caches)
                                     ->execute()
                                     ->fetchAll();

      foreach($dbCaches as $cache) {

        // Array or what?
        if ($cache->cache_array || substr($cache->cache_value, 0, 2) == "a:") {
          $data = unserialize($cache->cache_value);
          if (!is_array($data)) {
            $data = array();
          }
          self::$data[$cache->cache_path] = $data;
        }
        else if ( $cache->cache_value ) {
          self::$data[$cache->cache_path] = $cache->cache_value;
        }
  
        // If we're using an alternate cache, put it there
        if (is_object(self::$cacheLib)) {
          if (!$cache->cache_value) {
            $cache->cache_value = "NULL";
          }
          self::$cacheLib->put($cache->cache_value, $cache->cache_value);
        }
      }
    }
  }

  public function set($path, $value) {
  print_r(self::$data);
    self::$data = array_merge_recursive(self::$data, self::instance()->_setCache(explode(':', $path), $value));
	print_r(self::$data);
  }
  
  public function get($path = null) {
    if (strlen($path)) {
      $path = explode(":", $path);
      array_shift($path);
      $path = implode(":", $path);
    }
    return $this->_getCache($path, self::$data);
  }

  /**
   * Take a multidimensional array from the app/core config files and combine the keys to something that kxEnv likes
   *
   * @param  array the config array as it passes through each iteration of the function
   * @param  array the parsed array as it passes through each iteration of the function
   * @param  The full configuration array (reference to $config)
   * @return mixed
   */
  protected function _implodeConfig(&$config, $paths=array(), &$fullConfig=NULL) {
    // We need an array to proceed
    if (!is_array($config)) { return NULL; }
    // Initialize the $fullConfig pointer (modifications to $config will affect $fullConfig)
    if (!$fullConfig) { $fullConfig =& $config;}
    // We're done!
    if (!count($config)) { array_pop($paths); return $paths; }
    if (!isset($paths['temp'])) {
      $paths['temp'] = "";
    }
    foreach($config as $key=>$contents) {
      // If we're not getting an array in the array, we have a problem (maybe recache_file isn't being set in a cache). Whatever the reson, stop this function.
      if(is_array($contents)) {
        // Is this key now empty? Remove it, then start over from the next key
        if (!count($contents)) {
          unset($config[$key]);
          return self::_implodeConfig($config, $paths, $fullConfig);
        }
        // "recache_file" is our magic key to let us know we've reach the last dimension of an array (all cache declarations should have this key)
        // If we don't have it, add this key name to the current $path we're working on, then dig through a deeper dimension
        if(!array_key_exists("recache_file", $contents)){

          $paths['temp'] .= $key.":";
          return self::_implodeConfig($config[$key], $paths, $fullConfig);
        }
        // We have it? Okay, then we're done with this path. Add the final key to the path, move the
        // value to a key, then remove the temporary path and now unneeded key from the parent array.
        else {
            $paths['temp'] .= $key;
            $paths[$paths['temp']] = $contents;
            unset($config[$key], $paths['temp']);
            return self::_implodeConfig($fullConfig, $paths, $fullConfig);
        }
      }
      return NULL;
    }
  }

  /**
   * Check if a cache entry exists
   *
   * @param  string    Cache name
   * @return  boolean
   */
  static public function exists($cache) {
    return(isset($this->data[$cache]) AND ($this->data[$cache] !== NULL)) ? TRUE : FALSE;
  }
  
  /**
   * Set a cache
   *
   * @param  string  Cache Key
   * @param  mixed  Cache value
   */
  private function _setCache($path, $value) {
    if ($path) {
	  array_shift($path);
      // First, update the already-loaded cache with the new value      
      $return = array(implode(":", $path) => $value);
      
      // Are we using an alt cache engine?
      // Update it if so
      if (is_object(self::$cacheLib)) {
        if (!$value) {
          $value = "NULL";
        }
        self::$cacheLib->update(implode(':', $path), $value);
      }
      
      // Now update the database
      // Merge does an update if the key exists, otherwise, it inserts
	  kxDB::getInstance()->merge("cache")
                         ->key(array("cache_path" => implode(':', $path)))
                         ->fields(array(
                                   "cache_array"   => intval(is_array($value)),
                                   "cache_value"   => is_array($value) ? serialize($value) : $value,
                                   "cache_updated" => time()
                                  )
                                 )
						->execute();
		return $return;
    }
  }

  /**
   * Get the cache
   *
   * @param  array  Cache path
   * @return  mixed  Cache value
   */
  private function _getCache($path, $root) {
    if(!$path) return FALSE;
    if (is_array($root) && !array_key_exists($path, $root)) {
      self::_loadCaches($path);
      if(!array_key_exists($path, $root)) {
        return FALSE;
      }
    }
    else {
      return $root[$path];
    }
  }

  /**
   * Fetch all the caches as a reference
   *
   * @return  array
   */
  static public function &fetchCaches() {
    return self::$data;
  }

  /**
    * Rebuild a cache using defined $CACHE settings in it's extensions file
   *
   * @param  string  Cache path
   * @param  string  Application
   * @return  @e void
   */
  static public function rebuildCache($path, $app='') {
    
    $app    = kxFunc::alphaNum($app);
    $caches = array();
    
    if($app) {
      if ($app == 'base'){
        $caches = self::_implodeConfig(kxEnv::fetchCoreConfig('cache'));
      }
      else {
        if(isset(kxEnv::$applications[$app]) && !kxFunc::isAppEnabled($app)) {
          return;
        }

        $caches = self::_implodeConfig(kxEnv::fetchAppConfig($app,'cache'));
      }
    }
    else {
      $caches = self::_implodeConfig(kxEnv::fetchCoreConfig('cache'));
      foreach(array_keys($kxEnv::$applications) as $appName) {
        $appCache = self::_implodeConfig(kxEnv::fetchAppConfig($appName, 'cache'));
        
        if (is_array($appCache)) {
          $caches = array_merge($caches, $appCache);
        }
      }
    }
    
    if(isset($caches[$path]))
    {
      $recacheFile = $caches[$path]['recache_file'];

      if ($recacheFile && is_file($recacheFile)) {
        
        // If the recache function is in the modules directory, check if we're using a module extender for this module
        if (strpos($recacheFile, '/modules') !== FALSE) {
          $className = kxFunc::loadModule( $recacheFile, $caches[ $key ]['recache_class'] );
        }
        // Otherwise, we're using a helper class, so check if we're using an extender for that.
        elseif ($app) {
          $className = kxFunc::loadHelper( $recacheFile, $caches[$key]['recache_class'], ($app == 'global') ? 'core' : $app );
        }
        
        if (!$className) {
          $className = $caches[$key]['recache_class'];
        }
        
        $recache = new $className(kxEnv::getInstance());

        if(method_exists($recache, 'makeRegistryShortcuts')) {
          $recache->makeRegistryShortcuts(kxEnv::getInstance());
        }

        $recache->$caches[$path]['recache_function']();
      }
    }
  }
}

