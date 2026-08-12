<?php

namespace kx\kxCmd;

use kx\Exceptions\kxException;
use kx\kxBans;
use kx\kxEnv;
use kx\kxFunc;
use kx\kxOrm;
use ReflectionClass;

/**
 * kxCmdResolv
 * Takes incoming data and parses it.
 */
class kxCmdResolv
{
    /**
     * Important strings.
     *
     * @var string
     */
    private static $baseCmd;
    private static $defaultCmd;
    private static $class_dir = 'public';

    /**
     * Constructor.
     */
    public function __construct()
    {
        self::$baseCmd = new ReflectionClass(kxCmd::class);
        self::$defaultCmd = new kxCmd_default();
        self::$class_dir = (IN_MANAGE) ? 'manage' : 'public';
    }

    /**
     * Constructor.
     */
    public static function run(kxEnv $environment)
    {
        $instance = new kxCmdResolv();
        $cmd = $instance->getCmd($environment);
        $cmd->execute($environment);
    }

    /**
     * Retreive our command.
     *
     * @param	object		kxEnv reference
     *
     * @return object
     */
    public function getCmd(kxEnv $environment)
    {
        $module = kxEnv::$current_module;
        $section = kxEnv::$current_section;
        // No module?
        if (!$module) {
            if (IN_MANAGE && kxEnv::$request->get('app') == '') {
                $module = 'index';
            } else {
                // Get the first module in the DB
                $module = kxOrm::getEntityManager()->getRepository('Edaha\Entities\Module')
                    ->getCoreModules()
                ;
                $module = $module ? $module[0]->class : 'index';
            }
        }
        $moduledir = kxFunc::getAppDir(KX_CURRENT_APP).'/modules/'.self::$class_dir.'/'.$module.'/';
        // No section?
        if (!$section) {
            if (file_exists($moduledir.'default_section.php')) {
                $defaultSection = '';

                require $moduledir.'default_section.php';
                if ($defaultSection) {
                    $section = $defaultSection;
                }
            }
        }

        // Load the logging class here because we'll probably need it anyway in pretty much any manage function
        // require_once kxFunc::getAppDir('core').'/classes/logging.php';
        // $environment->set('kx:classes:core:logging:id', new logging($environment));

        // Are we in manage?
        if (IN_MANAGE) {
            $validSession = kxFunc::getManageSession();
            if (
                (
                    $environment::$request->get('module') == ''
                    || (
                        $environment::$request->get('module') != ''
                        && 'login' != $environment::$request->get('module')
                    )
                ) 
                && (!$validSession)) {
                // Force login if we have an invalid session

                kxEnv::$current_module = 'login';

                require_once kxFunc::getAppDir('core').'/modules/manage/login/login.php';
                $login = new \manage_core_login_login($environment);
                $login->execute($environment);

                exit;
            }
        }

        // Ban check ( may as well do it here before we do any further processing)
        $boardName = '';
        if (KX_CURRENT_APP == 'core' && 'post' == $module && 'post' == $section) {
            if (isset($environment->request, $environment->request['board'])) {
                $boardName = $environment->{$request}['board'];
            }
        }

        kxBans::banCheck($_SERVER['REMOTE_ADDR'], $boardName);

        $className = self::$class_dir.'_'.KX_CURRENT_APP.'_'.$module.'_'.$section;
        if (file_exists($moduledir.$section.'.php')) {
            require_once $moduledir.$section.'.php';
        }

        if (class_exists($className)) {
            $cmd_class = new ReflectionClass($className);

            if ($cmd_class->isSubClassOf(self::$baseCmd)) {
                return $cmd_class->newInstance();
            }

            throw new kxException("{$section} in {$module} does not exist!");
        }

        // If we somehow made it here, let's just use the default command
        return clone self::$defaultCmd;
    }
}
