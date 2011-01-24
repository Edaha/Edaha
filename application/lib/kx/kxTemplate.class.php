<?php

class kxTemplate {
  private static $template_dir;
  private static $debug_flag = false;

  private static $data = array();
  private static $instance;

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
    self::$data = array();
    self::$instance->output($tpl, array_merge(self::$data,$data));
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
    return self::$instance->get($tpl, $data);
  }

  public static function assign($name, $value){
    self::init();
    self::$data[$name] = $value;
  }

}
