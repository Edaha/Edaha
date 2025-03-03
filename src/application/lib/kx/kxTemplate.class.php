<?php
include_once 'application/lib/gettext/gettext.inc.php';
_bindtextdomain();
class kxTemplate {
    private static $template_dir;
    private static $debug_flag = false;
    
    private static $data = array();
    private static $instance=null;
    
    // Manage wrapper
    private static $manage;
    
    // Manage sidebar menu extras
    public static $menu_extra = '';
    
    private function __construct(){}
    
    public static function init($template_dir = null, $compiled_dir = null, $cache_dir = null) {
        if (self::$instance == null) {
            //echo "<p>init() called!</p>";
            if($template_dir != null){
                self::$template_dir = $template_dir;
            }
            else{
                self::$template_dir = KX_ROOT . kxEnv::get("kx:templates:dir");
            }
            // $loader = new Twig_Loader_Filesystem(self::$template_dir);
            $loader = new \Twig\Loader\FilesystemLoader(self::$template_dir);
            
            if($cache_dir == null){
                $cache_dir = KX_ROOT . kxEnv::get("kx:templates:cachedir");
            }
            self::$instance = new \Twig\Environment($loader, array(
                'cache' => $cache_dir,
                'auto_reload' => true,
                'debug' => true
            ));
            // Load our extensions
            // self::$instance->addExtension(new Twig_Extensions_Extension_I18n());
            // self::$instance->addExtension(new Twig_Extensions_Extension_kxEnv());
            // self::$instance->addExtension(new Twig_Extensions_Extension_DateFormat());
            // self::$instance->addExtension(new Twig_Extensions_Extension_Text());
            // self::$instance->addExtension(new Twig_Extensions_Extension_Round());
            // self::$instance->addExtension(new Twig_Extensions_Extension_Strip());
            // self::$instance->addExtension(new Twig_Extensions_Extension_Debug());
            // self::$instance->addExtension(new Twig_Extensions_Extension_PHP());

            // Add twig functions
            $function = new \Twig\TwigFunction('kxEnv', function ($string) {
                return kxEnv::get('kx:'.$string);
            });

            self::$instance->addFunction($function);

            $filter = new \Twig\TwigFilter(
                'trans', 
                function ($context, $string) {
                    return jblond\TwigTrans\Translation::transGetText($string, $context);
                }, 
                ['needs_context' => true]
            );
            self::$instance->addFilter($filter);
            self::$instance->addExtension(new jblond\TwigTrans\Translation());

            // Supply Twig with our GET/POST variables
            self::$data['_get'] = $_GET;
            self::$data['_post'] = $_POST;
            
            // Supply Twig with the default locale
            self::$data['locale'] = kxEnv::Get('kx:misc:locale');
            // Are we in manage? Load up the manage wrapper
            if (IN_MANAGE) {
                self::$data['current_app'] = "";
                if (KX_CURRENT_APP == "core") {
                    // Load up some variables for tabbing/menu purposes
                    if (isset(kxEnv::$request['app'])) {
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
                
				$baseurl = kxEnv::Get('kx:paths:main:path') . '/manage.php?sid=' . ( isset(kxEnv::$request['sid']) ? kxEnv::$request['sid'] : '') . '&';
                self::$data['base_url']=$baseurl;
 
                // Get our manage username
                if (isset(kxEnv::$request['sid'])) {
                  $result = kxDB::getinstance()->select('staff', 'stf')
                                               ->fields('stf', array('user_name'));
                  $result->innerJoin("manage_sessions", "ms", "ms.session_staff_id = stf.user_id");
                  self::assign('name', $result->condition('session_id', kxEnv::$request['sid'])
                                               ->execute()
                                               ->fetchField());                
                }
                
            }// else {
			//	die('Not IN_MANAGE!');
			//}
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
        if (IN_MANAGE && kxEnv::$current_module != 'login') {
            self::_buildMenu();
		}
        //echo "<h2>Pre-merge</h2>";
		//var_dump(self::$data);
        $data = array_merge(self::$data,$data);
		//echo "<h2>Post-merge</h2>";
		//var_dump($data);
		// $template=self::$instance->loadTemplate($tpl);
		$template=self::$instance->load($tpl);
        //echo "<pre>";
		//print_r($template);
		//echo "</pre>";
		$template->display($data);
    }
    
    // returns a string of the parsed and processed template
    public static function get($tpl, $data = array(), $bypassManageCheck=false){
        self::init();
        if (is_string($tpl)) {
            
            if(file_exists(self::$template_dir . $tpl . '.tpl')){
                $tpl = $tpl . '.tpl';
            }
            else{
                throw new Exception('No template found ' . $tpl .'.tpl from ' . self::$template_dir, E_USER_ERROR);
            }
        }
		
        if (IN_MANAGE && kxEnv::$current_module != 'login') {
            self::_buildMenu();
		}
        $data = array_merge(self::$data,$data);
		
        //echo "<pre>";
		//print_r(self::$data);
		//echo "</pre>";
        if (IN_MANAGE && kxEnv::$current_module != 'login' && !$bypassManageCheck) {
            return "";
        }
		
		$template=self::$instance->load($tpl);
        //echo "<pre>";
		//print_r($template);
		//echo "</pre>";
		return $template->render($data);
    }
    
    public static function assign($name, $value){
        self::init();
        self::$data[$name] = $value;
    }
    
    private static function _buildMenu() {
        $app = KX_CURRENT_APP;
        if (KX_CURRENT_APP == 'core' && !isset(kxEnv::$request['module']) && !isset(kxEnv::$request['app'])) {
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
		//print_r($modules);
        foreach ($modules as $module) {
            $_file = kxFunc::getAppDir( $app ) . "/modules/manage/" . $module['module_file'] . '/menu.yml';
			//echo "<p>Getting menu from {$_file}</p>";
            if (file_exists($_file)) {
                $menu[$module['module_file']] = kxYml::loadFile($_file);
                self::assign('menu', $menu);
                self::assign('module', $module['module_file']);
            }
        }
    }
}