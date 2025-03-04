<?php
class kxYml {
  
  private static $useSpyc = true;
  private static $init = false;
  
  private static function _init() {
    if (!self::$init) {
      if (function_exists("syck_load")) {
        self::$useSpyc = false;
      }
    }
    
    self::$init = true;
    
    return self::$init;
  }
  
  public static function loadFile($file) {
    self::_init();
    
    return self::$useSpyc ? Spyc::YAMLLoad($file) : syck_load(file_get_contents($file));
  }
  
  public static function loadString($string) {
    self::_init();
    
    return self::$useSpyc ? Spyc::YAMLLoadString($string) : syck_load($string);
  }
  
  public static function dump($array) {
    self::_init();
    
    return self::$useSpyc ? Spyc::YAMLDump($array) : syck_dump($array);
  }
  
}
?>