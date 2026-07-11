<?php

use jblond\TwigTrans\Translation;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Extra\String\StringExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

class kxTemplate
{
    // Manage sidebar menu extras
    public static $menu_extra = '';
    private static $template_dir;
    private static $debug_flag = false;

    private static $data = [];
    private static $instance;

    // Manage wrapper
    private static $manage;

    private function __construct() {}

    public static function init($template_dir = null, $compiled_dir = null, $cache_dir = null)
    {
        if (null == self::$instance) {
            // echo "<p>init() called!</p>";
            if (null != $template_dir) {
                self::$template_dir = $template_dir;
            } else {
                self::$template_dir = KX_ROOT.kxEnv::get('kx:templates:dir');
            }
            // $loader = new Twig_Loader_Filesystem(self::$template_dir);
            $loader = new FilesystemLoader(self::$template_dir);

            if (null == $cache_dir) {
                $cache_dir = KX_ROOT.kxEnv::get('kx:templates:cachedir');
            }
            self::$instance = new Environment($loader, [
                'cache' => $cache_dir,
                'auto_reload' => true,
                'debug' => true,
            ]);
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
            $function = new TwigFunction('kxEnv', function ($string) {
                return kxEnv::get('kx:'.$string);
            });

            self::$instance->addFunction($function);

            $filter = new TwigFilter(
                'trans',
                function ($context, $string) {
                    return Translation::transGetText($string, $context);
                },
                ['needs_context' => true]
            );
            self::$instance->addFilter($filter);
            // TODO Only include when set to debug
            self::$instance->addExtension(new DebugExtension());
            self::$instance->addExtension(new StringExtension());
            self::$instance->addExtension(new Translation());

            // Supply Twig with our GET/POST variables
            self::$data['_get'] = $_GET;
            self::$data['_post'] = $_POST;

            // Supply Twig with the default locale
            self::$data['locale'] = kxEnv::Get('kx:misc:locale');
            // Are we in manage? Load up the manage wrapper
            if (IN_MANAGE) {
                self::$data['current_app'] = '';
                if (KX_CURRENT_APP == 'core') {
                    // Load up some variables for tabbing/menu purposes
                    if (isset(kxEnv::$request['app'])) {
                        self::$data['current_app'] = kxEnv::$request['app'];
                    }
                } elseif (KX_CURRENT_APP == 'board') {
                    if ('posts' == kxEnv::$current_module) {
                        self::$data['current_app'] = 'posts';
                    } else {
                        self::$data['current_app'] = 'board';
                    }
                }

                $baseurl = kxEnv::Get('kx:paths:main:path').'/manage.php?sid='.(kxEnv::$request['sid'] ?? '').'&';
                self::$data['base_url'] = $baseurl;

                // Get our manage username
                if (isset(kxEnv::$request['sid'])) {
                    self::assign('name', kxFunc::getManageUser()['user_name']);
                }
            }// else {
            //	die('Not IN_MANAGE!');
            // }
        }
    }

    // check if a template exists
    public static function templateExists($name)
    {
        return file_exists(self::$template_dir.$name.'.html.twig');
    }

    // create a template from a template file name
    public static function templateFromFile($name)
    {
        self::init();

        if (file_exists(self::$template_dir.$name.'.html.twig')) {
            $file = $name.'.html.twig';
        } else {
            throw new Exception('No template found '.$name.'.html.twig from '.self::$template_dir, E_USER_ERROR);
        }
        if (!self::$instance->getLoader() instanceof Twig_Loader_Filesystem) {
            self::$instance->setLoader(new Twig_Loader_Filesystem(self::$template_dir));
        }

        return self::$instance->loadTemplate($file);
    }

    // create a template from a template as a string, great for loading
    // templates from a database
    public static function templateFromString($template)
    {
        self::init();

        if (!self::$instance->getLoader() instanceof Twig_Loader_String) {
            self::$instance->setLoader(new Twig_Loader_String());
        }

        return self::$instance->loadTemplate($template);
    }

    // outputs a template
    public static function output($tpl, $data = [])
    {
        self::init();
        if (is_string($tpl)) {
            if (file_exists(self::$template_dir.$tpl.'.html.twig')) {
                $tpl = $tpl.'.html.twig';
            } else {
                throw new Exception('No template found '.$tpl.'.html.twig from '.self::$template_dir, E_USER_ERROR);
            }
        }

        if (IN_MANAGE && 'login' != kxEnv::$current_module) {
            self::_buildMenu();
        }

        $data = array_merge(self::$data, $data);
        $template = self::$instance->load($tpl);
        $template->display($data);
    }

    // returns a string of the parsed and processed template
    public static function get($tpl, $data = [], $bypassManageCheck = false)
    {
        self::init();
        if (is_string($tpl)) {
            if (file_exists(self::$template_dir.$tpl.'.html.twig')) {
                $tpl = $tpl.'.html.twig';
            } else {
                throw new Exception('No template found '.$tpl.'.html.twig from '.self::$template_dir, E_USER_ERROR);
            }
        }

        if (IN_MANAGE && 'login' != kxEnv::$current_module) {
            self::_buildMenu();
        }
        $data = array_merge(self::$data, $data);

        if (IN_MANAGE && 'login' != kxEnv::$current_module && !$bypassManageCheck) {
            return '';
        }

        $template = self::$instance->load($tpl);

        return $template->render($data);
    }

    public static function assign($name, $value)
    {
        self::init();
        self::$data[$name] = $value;
    }

    private static function _buildMenu()
    {
        $app = KX_CURRENT_APP;
        if (KX_CURRENT_APP == 'core' && !isset(kxEnv::$request['module']) && !isset(kxEnv::$request['app'])) {
            $modules = [(object) ['class' => 'index']];
        } else {
            $modules = kxOrm::getEntityManager()->getRepository('Edaha\Entities\Module')
                ->getManagementModules()
            ;
        }
        foreach ($modules as $module) {
            $_file = kxFunc::getAppDir($app).'/modules/manage/'.$module->class.'/menu.yml';
            if (file_exists($_file)) {
                $menu[$module->class] = kxYml::loadFile($_file);
                self::assign('menu', $menu);
                self::assign('module', $module->class);
            }
        }
    }
}
