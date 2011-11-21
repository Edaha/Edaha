<?php

class kxEnv {
    
    public static $current_application = '';
    public static $current_module      = '';
    public static $current_section     = '';
    
    static public $request = array('act' => '', 'do' => '', 'action' => '');
    
    protected static $_coreConfig = array();
    protected static $_appConfig  = array();
    
    private static $instance = null;
    private static $cache =  null;
    
    private $environment;
    private $configuration;
        
    private function __construct($environment, $configuration) {
        $this->environment = $environment;
        $this->configuration = $configuration;
    }
    
    public static function getInstance() {
        if(!self::$instance instanceof self) return;
        
        return self::$instance;
    }
    
    private function getConfig() {
        return $this->configuration;
    }
    
    private function getCache() {
        return self::$cache;
    }
    
    public static function initialize($environment, $configdir) {
        if(self::$instance instanceof self) return;

        $configuration = array();
        
        // Load config
        foreach(self::getConfigFiles($configdir) as $configfile) {
            $configuration = array_merge_recursive(array_reduce(
                array_intersect_key(
                    self::loadConfigFile($configfile),
                    array_flip(array('all', $environment))),
                    array('self', 'mergeWrapper')), $configuration);
        }

        // Set our instance, load kxConfig
        self::$instance = new self($environment, new kxConfig($configuration));
        
        // Add any classes we want added to the autoloader.
        foreach (kxEnv :: get('kx:autoload:load') as $repo => $opts) {
            kxEnv :: set(sprintf('kx:autoload:repository:%s:id', $repo), kxAutoload :: registerRepository(sprintf('%s/%s/%s', KX_ROOT, 'application/lib', $opts['path']), array (
                'prefix' => $opts['prefix']
            )));
        }
        
        // Set up our input, remove any magic quotes
        if(is_array($_POST) and !empty($_POST)) {
            foreach( $_POST as $BUTTER => $TOAST ) {
                // Skip arrays
                if ( ! is_array( $TOAST ) ) {
                    $_POST[ $BUTTER ] = kxFunc::strip_magic( $TOAST );
                }
            }
        }
        
        // Clean up all of our input (cookies, get/post requests, etc)
        kxFunc::cleanInput( $_GET );  
        kxFunc::cleanInput( $_POST );
        kxFunc::cleanInput( $_COOKIE );
        kxFunc::cleanInput( $_REQUEST );
        
        //Okay NOW let's  parse our input
        $input = kxFunc::parseInput( $_GET, array() );
        
        // Allow $_POST to overwrite $_GET
        self::$request = kxFunc::parseInput( $_POST, $input ) + self::$request;
        
        // Grab our app
        $_application = preg_replace("/[^a-zA-Z0-9\-\_]/", "" , (isset($_REQUEST['app']) && trim($_REQUEST['app']) ? $_REQUEST['app'] : "core" ) );
        
        // Make sure we get (hopefully) a string
        if(is_array($_application)) {
            $_application = array_shift($_application);
        }
        
        define('KX_CURRENT_APP', $_application);
        
        kxEnv::$current_application  = KX_CURRENT_APP;
        kxEnv::$current_module  = ( isset(self::$request['module']) ) ? self::$request['module'] : '';
        kxEnv::$current_section = ( isset(self::$request['section']) ) ? self::$request['section'] : '';
        
        // Cleanup
        kxEnv::$current_module = kxFunc::alphaNum( kxEnv::$current_module );
        kxEnv::$current_section = kxFunc::alphaNum( kxEnv::$current_section );

        // Load the cache
        self::$cache   = kxCache::instance();
    }
    
    private static function mergeWrapper($base, $next) {
        return array_merge_recursive(is_null($base) ? array() : $base, $next);
    }
    
    private static function getConfigFiles($configdir) {
        return glob($configdir . '/*.yml.php');
    }
    
    private static function loadConfigFile($configfile) {
        if (self::isCached($configfile)) {
            return self::loadCached($configfile); 
        }
        else {
            if (function_exists("syck_load")) {
                return syck_load(file_get_contents($configfile));
            }
            else {
                require_once(KX_ROOT."/application/lib/spyc/spyc.php");
                return spyc_load_file($configfile);
            }
        }
    }

    /**
     * Loads kx core configuration class 
     */
    static public function loadCoreConfig() {
      if (!(isset( self::$_coreConfig['core_config_class'] ) AND is_object(self::$_coreConfig['core_config_class']))) {
        self::$_coreConfig['core_config_class'] = new coreConfig();
      }
    }

    /**
     * Loads data from kx core config
     */
    static public function fetchCoreConfig($type) {
      if (!isset(self::$_coreConfig[$type]) || !is_array(self::$_coreConfig[$type])) {
        self::loadCoreConfig();
        $return = self::$_coreConfig['core_config_class']->fetchCaches();
        self::$_coreConfig['cache']       = is_array($return['caches'])    ? $return['caches'] : array();
        self::$_coreConfig['cachetoload'] = is_array($return['cachetoload']) ? $return['cachetoload'] : array();
      }
      return self::$_coreConfig[$type];
    }

    /**
     * Loads the configuration for an application
     *
     * @param string App directory
     */
    static public function loadAppConfig($app) {
      $CACHE = $LOAD = array();
      
      if (!isset( self::$_appConfig[$app])) {
        $file = kxFunc::getAppDir($app) . '/appConfig.php';
        
        if(is_file($file)) {
          require($file);

          self::$_appConfig[$app]['cache']     =  $CACHE;
          self::$_appConfig[$app]['cacheload'] =  $LOAD;
        }
      }
    }

    /**
     * Fetches apps core variable data
     *
     * @param   string  App dir
     * @param   string  Type of variable to return
     * @return  string  Core variable
     */
    static public function fetchAppConfig($app, $type) {
      if ( !isset(self::$_appConfig[$app][$type]) OR !is_array(self::$_appConfig[$app][$type])) {
        self::loadAppConfig($app);
      }

      return isset(self::$_appConfig[$app][$type]) ? self::$_appConfig[$app][$type] : array();
    }
    
    private static function isCached($configfile) {
        return false;
    }
    
    public static function get($path = null, $default = null) {
        // Shortcut for getting stuff from the cache (without having to use the cache object directly)
        if (strpos($path, 'cache') === 0) {
           // Cache doesn't care about $default
          return self::getInstance()->getCache()->get($path);
        }
        return self::getInstance()->getConfig()->get($path, $default);
    }
	
	public static function dumpConfig() {
		return var_dump(self::getInstance()->getConfig(),true);
	}
    
    public static function set($path, $value) {
        // Shortcut for setting the cache (without having to use the cache object directly)
        if (strpos($path, 'cache') === 0) {
          return self::getInstance()->getCache()->set($path, $value);
        }
        self::getInstance()->getConfig()->set($path, $value);
    }
}
