<?php

class kxEnv {

	public static $current_application = '';
	public static $current_module      = '';
	public static $current_section     = '';
  
  static public $request	= array();

  private static $instance = null;

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
		self::$request = kxFunc::parseInput( $_POST, $input );

		// Grab our app
		$_application = preg_replace("/[^a-zA-Z0-9\-\_]/", "" , (isset($_REQUEST['app']) && trim($_REQUEST['app']) ? $_REQUEST['app'] : "core" ) );
		
    // Make sure we get (hopefully) a string
    if(is_array($_application)) {
			$_application	= array_shift($_application);
		}

		define('KX_CURRENT_APP', $_application);
    
    kxEnv::$current_application  = KX_CURRENT_APP;
    kxEnv::$current_module  = ( isset(self::$request['module']) ) ? self::$request['module'] : '';
		kxEnv::$current_section = ( isset(self::$request['section']) ) ? self::$request['section'] : '';

		// Cleanup
		kxEnv::$current_module = kxFunc::alphaNum( kxEnv::$current_module );
		kxEnv::$current_section = kxFunc::alphaNum( kxEnv::$current_section );
    
    self::$instance = new self($environment, new kxConfig($configuration));
  }

  private static function mergeWrapper($base, $next) {
    return array_merge_recursive(is_null($base) ? array() : $base, $next);
  }

  private static function getConfigFiles($configdir) {
    return glob($configdir . '/*.yml');
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

  private static function isCached($configfile) {
    return false;
  }

  public static function get($path = null, $default = null) {
    return self::getInstance()->getConfig()->get($path, $default);
  }

  public static function set($path, $value) {
    self::getInstance()->getconfig()->set($path, $value);
  }
}
