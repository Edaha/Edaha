<?php

namespace kx;

use jblond\TwigTrans\Translation;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Extra\String\StringExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

class kxTemplate
{
    private static string $template_dir;

    private static array $data = [];

    private static Environment $instance;

    private function __construct() {}

    public static function init(?string $template_dir = null, ?string $cache_dir = null): void
    {
        if (!isset(self::$instance)) {
            self::$template_dir = $template_dir ?? KX_ROOT.kxEnv::get('kx:templates:dir');

            self::createInstance(
                $cache_dir ?: KX_ROOT.kxEnv::get('kx:templates:cachedir')
            );

            self::addFunctions();
            self::addFilters();
            self::addExtensions();
            self::initializeData();
        }
    }

    // check if a template exists
    public static function templateExists(string $filename): bool
    {
        return file_exists(self::$template_dir.$filename.'.html.twig');
    }

    // outputs a template
    public static function output(string $tpl, array $data = []): void
    {
        self::init();
        if (!self::templateExists($tpl)) {
            throw new \Exception('No template found '.$tpl.'.html.twig from '.self::$template_dir, E_USER_ERROR);
        }

        if (IN_MANAGE && 'login' != kxEnv::$current_module) {
            self::_buildMenu();
        }

        $data = array_merge(self::$data, $data);
        $template = self::$instance->load("{$tpl}.html.twig");
        $template->display($data);
    }

    // returns a string of the parsed and processed template
    public static function get($tpl, $data = [], $bypassManageCheck = false): string
    {
        self::init();
        if (!self::templateExists($tpl)) {
            throw new \Exception('No template found '.$tpl.'.html.twig from '.self::$template_dir, E_USER_ERROR);
        }

        if (IN_MANAGE && 'login' != kxEnv::$current_module) {
            self::_buildMenu();
        }
        $data = array_merge(self::$data, $data);

        if (IN_MANAGE && 'login' != kxEnv::$current_module && !$bypassManageCheck) {
            return '';
        }

        $template = self::$instance->load("{$tpl}.html.twig");

        return $template->render($data);
    }

    public static function assign($name, $value): void
    {
        self::init();
        self::$data[$name] = $value;
    }

    private static function initializeData(): void
    {
        self::$data['_get'] = $_GET;
        self::$data['_post'] = $_POST;
        self::$data['locale'] = kxEnv::Get('kx:misc:locale');

        if (IN_MANAGE) {
            self::$data['current_app'] = '';
            self::$data['current_app'] = match (KX_CURRENT_APP) {
                'core' => kxEnv::$request->get('app'),
                'board' => 'posts' == kxEnv::$current_module ? 'posts' : 'board',
            };

            $baseurl = kxEnv::Get('kx:paths:main:path').'/manage.php?sid='.session_id().'&';
            self::$data['base_url'] = $baseurl;

            // Get our manage username
            if ('' != kxEnv::$request->get('sid')) {
                self::assign('name', kxFunc::getManageUser()['user_name']);
            }
        }
    }

    private static function createInstance(string $cache_dir): void
    {
        $loader = new FilesystemLoader(self::$template_dir);

        self::$instance = new Environment($loader, [
            'cache' => $cache_dir,
            'auto_reload' => true,
            'debug' => true,
        ]);
    }

    private static function addFunctions(): void
    {
        self::$instance->addFunction(new TwigFunction('kxEnv', function ($string) {
            return kxEnv::get('kx:'.$string);
        }));
    }

    private static function addFilters(): void
    {
        self::$instance->addFilter(new TwigFilter(
            'trans',
            function ($context, $string) {
                return Translation::transGetText($string, $context);
            },
            ['needs_context' => true]
        ));
    }

    private static function addExtensions(): void
    {
        self::$instance->addExtension(new DebugExtension());
        self::$instance->addExtension(new StringExtension());
        self::$instance->addExtension(new Translation());
    }

    private static function _buildMenu(): void
    {
        $app = KX_CURRENT_APP;
        if (KX_CURRENT_APP == 'core' && '' != kxEnv::$request->get('module') && '' != kxEnv::$request->get('app')) {
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
