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
    if (!self::$instance instanceof Dwoo) {
      if($template_dir != null){
              self::$template_dir = $template_dir;
      }
      else{
              self::$template_dir = KX_ROOT . kxEnv::get("kx:templates:dir");
      }
      self::$instance = new Dwoo(self::$template_dir);

      if($cache_dir != null){
              self::$instance->setCacheDir($cache_dir);
      }
      else {
        self::$instance->setCacheDir(KX_ROOT . kxEnv::get("kx:templates:cachedir"));
      }

      if($compiled_dir != null){
              self::$instance->setCompileDir($compiled_dir);
      }
      else {
        self::$instance->setCompileDir(KX_ROOT . kxEnv::get("kx:templates:cachedir"));
      }
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
        
        self::assign('base_url', kxEnv::Get('kx:paths:main:path') . '/manage.php?sid=' . kxEnv::$request['sid'] . '&amp;');
          
      }
    }
  }

  // check if a template exists
  public static function templateExists($name){
    return file_exists(self::$template_dir . $name . '.dwoo');
  }


  // create a template from a template file name
  public static function templateFromFile($name, $cache_time = null, $cache_id = null, $compile_id = null){
    self::init();

    if(is_null($compile_id)){
      $compile_id = str_replace('/', '_', $name);
    }

    if(file_exists(self::$template_dir . $name . '.tpl')){
        $file = self::$template_dir . $name . '.tpl';
    }
    else{
      throw new Exception('No template found ' . $name .'.tpl from ' . self::$template_dir, E_USER_ERROR);
    }
    return new Dwoo_Template_File($file, $cache_time, $cache_id, $compile_id);
  }

  // create a template from a template as a string, great for loading
  // templates from a database
  public static function templateFromString($template, $cache_time = null, $cache_id = null, $compile_id = null){
    self::init();

    if(is_null($compile_id)){
      $compile_id = str_replace('/', '_', $name);
    }

    return new Dwoo_Template_String($template, $cache_time, $cache_id, $compile_id);
  }

  // outputs a template
  public static function output($tpl, $data = array()){
    self::init();
    if (is_string($tpl)) {
      if(file_exists(self::$template_dir . $tpl . '.tpl')){
          $tpl = self::$template_dir . $tpl . '.tpl';
      }
      else{
        throw new Exception('No template found ' . $tpl .'.tpl from ' . self::$template_dir, E_USER_ERROR);
      }
    }
    
    $data = array_merge(self::$data,$data);
    if (IN_MANAGE && kxEnv::$current_module != 'login') {
    	// Are we in manage? add our wrapper.
      /*if($tpl instanceof Dwoo_ITemplate) {
        $content = str_replace("<%CONTENT%>", $tpl->template, self::$manage);
      }
      else {
        $content = str_replace("<%CONTENT%>", file_get_contents($tpl), self::$manage);
      }*/
      $content = self::$instance->get($tpl, array_merge(self::$data,$data));
      $content = str_replace("<%MENU%>", self::_buildMenu(), $content);
      $content = str_replace("<%MENUEXTRA%>", self::$menu_extra, $content);
      self::$instance->output(new Dwoo_Template_String($content), array_merge(self::$data,$data));
    }
		else {
    	self::$instance->output($tpl, array_merge(self::$data,$data));
    }
  }

  // returns a string of the parsed and processed template
  public static function get($tpl, $data = array()){
    self::init();
    if (is_string($tpl)) {
    
      if(file_exists(self::$template_dir . $tpl . '.tpl')){
          $tpl = self::$template_dir . $tpl . '.tpl';
      }
      else{
        throw new Exception('No template found ' . $tpl .'.tpl from ' . self::$template_dir, E_USER_ERROR);
      }
    }
    $data = array_merge(self::$data,$data);
    self::$data = array();
    if (IN_MANAGE && kxEnv::$current_module != 'login') {
    	// Are we in manage? add our wrapper.
    	/*$return = str_replace("<%CONTENT%>", self::$instance->get($tpl, $data), self::$manage);*/
      $return = str_replace("<%MENU%>", self::_buildMenu(), $return);
      $return = str_replace("<%MENUEXTRA%>", self::$menu_extra, $return);
      return $return;
    }
    return self::$instance->get($tpl, $data);
  }

  public static function assign($name, $value){
    self::init();
    self::$data[$name] = $value;
  }
  
  private static function _buildMenu() {
  	$app = KX_CURRENT_APP;
    $return = "";
    if (KX_CURRENT_APP == 'core' && !kxEnv::$request['module']) {
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
        $data['menu'] = $menu;
        $data['module'] = $module['module_file'];
        $return .= self::$instance->get(self::$template_dir . 'manage/menu.tpl', array_merge(self::$data,$data));
      }
    }
    return $return;
  }
}