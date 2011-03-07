<?php

class kxTemplate {
  private static $template_dir;
  private static $debug_flag = false;

  private static $data = array();
  private static $instance;
  
  // Manage wrapper
  private static $manage;
  
  // Manage sidebar menu extras
  public static $menu_extra = '';

  private function __construct(){}

  public static function init($template_dir = null, $compiled_dir = null, $cache_dir = null) {
    if (!self::$instance instanceof Twig_Environment) {
      
      if($template_dir != null){
              self::$template_dir = $template_dir;
      }
      else{
              self::$template_dir = KX_ROOT . kxEnv::get("kx:templates:dir");
      }
      $loader = new Twig_Loader_Filesystem(self::$template_dir);

      if($cache_dir == null){
        $cache_dir = KX_ROOT . kxEnv::get("kx:templates:cachedir");
      }
      self::$instance = new Twig_Environment($loader, array(
  'cache' => $cache_dir,
  'auto_reload' => true
));
      // Load our extensions
      self::$instance->addExtension(new Twig_Extensions_Extension_I18n());
      self::$instance->addExtension(new Twig_Extensions_Extension_kxEnv());
      self::$instance->addExtension(new Twig_Extensions_Extension_DateFormat());
      self::$instance->addExtension(new Twig_Extensions_Extension_Text());
      self::$instance->addExtension(new Twig_Extensions_Extension_Round());
      self::$instance->addExtension(new Twig_Extensions_Extension_Strip());
      // Supply Twig with our GET/POST variables
      self::$data['_get'] = $_GET;
      self::$data['_post'] = $_POST;
      // Are we in manage? Load up the manage wrapper
      if (IN_MANAGE) {
        $data['current_app'] = "";
      	if (KX_CURRENT_APP == "core") {
        	// Load up some variables for tabbing/menu purposes
        	if (kxEnv::$request['app']) {
          	self::$data['current_app'] = kxEnv::$request['app'];
          }
        }
        else if (KX_CURRENT_APP == "board") {
        	if (kxEnv::$current_module == "posts") {
          	self::$data['current_app'] = "posts";
          }
          else {
          	self::$data['current_app'] = "board";
          }
        }
        
        self::assign('base_url', kxEnv::Get('kx:paths:main:path') . '/manage.php?sid=' . kxEnv::$request['sid'] . '&');
          
      }
    }
  }

  // check if a template exists
  public static function templateExists($name){
    return file_exists(self::$template_dir . $name . '.tpl');
  }


  // create a template from a template file name
  public static function templateFromFile($name){
    self::init();
    
    if(file_exists(self::$template_dir . $name . '.tpl')){
        $file = $name . '.tpl';
    }
    else{
      throw new Exception('No template found ' . $name .'.tpl from ' . self::$template_dir, E_USER_ERROR);
    }
    if (!self::$instance->getLoader() instanceof Twig_Loader_Filesystem) {
      self::$instance->setLoader(new Twig_Loader_Filesystem(self::$template_dir));
    }    
    return self::$instance->loadTemplate($file);
  }

  // create a template from a template as a string, great for loading
  // templates from a database
  public static function templateFromString($template){
    self::init();

    if (!self::$instance->getLoader() instanceof Twig_Loader_String) {
      self::$instance->setLoader(new Twig_Loader_String());
    } 
    return self::$instance->loadTemplate($template);
  }

  // outputs a template
  public static function output($tpl, $data = array()){
    self::init();
    if (is_string($tpl)) {
      if(file_exists(self::$template_dir . $tpl . '.tpl')){
          $tpl = $tpl . '.tpl';
      }
      else{
        throw new Exception('No template found ' . $tpl .'.tpl from ' . self::$template_dir, E_USER_ERROR);
      }
    }
    
    $data = array_merge(self::$data,$data);
    if (IN_MANAGE && kxEnv::$current_module != 'login') {
      self::_buildMenu();
      $content = self::$instance->loadTemplate($tpl)->display(array_merge(self::$data,$data));
    }
		else {
    	self::$instance->loadTemplate($tpl)->display(array_merge(self::$data,$data));
    }
  }

  // returns a string of the parsed and processed template
  public static function get($tpl, $data = array()){
    self::init();
    if (is_string($tpl)) {
    
      if(file_exists(self::$template_dir . $tpl . '.tpl')){
          $tpl = $tpl . '.tpl';
      }
      else{
        throw new Exception('No template found ' . $tpl .'.tpl from ' . self::$template_dir, E_USER_ERROR);
      }
    }
    $data = array_merge(self::$data,$data);
    self::$data = array();
    if (IN_MANAGE && kxEnv::$current_module != 'login') {
      return $return;
    }
    return self::$instance->loadTemplate($tpl)->render(array_merge(self::$data,$data));
  }

  public static function assign($name, $value){
    self::init();
    self::$data[$name] = $value;
  }
  
  private static function _buildMenu() {
  	$app = KX_CURRENT_APP;
    if (KX_CURRENT_APP == 'core' && !kxEnv::$request['module'] && !kxEnv::$request['app']) {
        $modules = Array(Array('module_file' => 'index'));
    }
    else {
      $modules = kxDB::getinstance()->select("modules", "", array('fetch' => PDO::FETCH_ASSOC))
                                    ->fields("modules", array("module_name", "module_file"))
                                    ->condition("module_application", $app)
                                    ->condition("module_manage", 1)
                                    ->orderBy("module_position")
                                    ->execute()
                                    ->fetchAll();
    }
    foreach ($modules as $module) {
      $_file = kxFunc::getAppDir( $app ) . "/modules/manage/" . $module['module_file'] . '/menu.yml';
      if (file_exists($_file)) {
        if (function_exists("syck_load")) {
          $menu = syck_load(file_get_contents($_file));
        }
        else {
          $menu = spyc_load_file($_file);
        }
        self::assign('menu', $menu);
        self::assign('module', $module['module_file']);
      }
    }
  }
}