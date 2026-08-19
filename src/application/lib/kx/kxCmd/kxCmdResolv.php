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
    /**
     * baseCmd holds the lowest-level class that our "Command" extends.
     *
     * __construct() sets this to new ReflectionClass(kxCmd::class)
     */
    private static \ReflectionClass $baseCmd;

    /**
     * $defaultCmd is the fallback handler in case we aren't able to load a valid command for the request.
     */
    private static kxCmd $defaultCmd;

    /**
     * The directory within the application's modules path that our Command Class lives in.
     */
    private static string $class_dir = 'public';

    /**
     * Initiates our static class variables $baseCmd, $defaultCmd, $class_dir.
     */
    private function __construct()
    {
        self::$baseCmd = new \ReflectionClass(kxCmd::class);
        self::$defaultCmd = new kxCmd_default();
        self::$class_dir = (IN_MANAGE) ? 'manage' : 'public';
    }

    /**
     * Takes our environment, gets the command from it, and executes the command.
     */
    public static function run(kxEnv $environment): void
    {
        $instance = new kxCmdResolv();
        $cmd = $instance->getCmd($environment);
        $cmd->execute($environment);
    }

    /**
     * Retreive our command.
     */
    public function getCmd(kxEnv $environment): kxCmd
    {
        $module = self::getModule($environment);
        $moduledir = kxFunc::getAppDir(KX_CURRENT_APP).'/modules/'.self::$class_dir.'/'.$module.'/';

        $section = self::getSection($environment, $moduledir);

        self::checkManageSession($environment);

        self::checkBan($environment, $module, $section);

        self::requireSection($moduledir, $section);

        $className = self::buildCommandClassName(self::$class_dir, KX_CURRENT_APP, $module, $section);
        if (class_exists($className)) {
            $cmd_class = new \ReflectionClass($className);

            if ($cmd_class->isSubClassOf(self::$baseCmd)) {
                return $cmd_class->newInstance();
            }

            throw new kxException("{$section} in {$module} does not exist!");
        }

        // If we somehow made it here, let's just use the default command
        return clone self::$defaultCmd;
    }

    /**
     * Gets and returns the name of the current module, falling back to a default if needed.
     */
    private function getModule(kxEnv $environment): string
    {
        $module = $environment::$current_module;
        // No module?
        if (!$module) {
            if (IN_MANAGE && '' == $environment::$request->get('app')) {
                $module = 'index';
            } else {
                // Get the first module in the DB
                $module = kxOrm::getEntityManager()->getRepository('Edaha\Entities\Module')
                    ->getCoreModules()
                ;
                $module = $module ? $module[0]->class : 'index';
            }
        }

        return $module;
    }

    /**
     * Returns the name of the section, and includes the module's default_section.php if needed.
     */
    private static function getSection(kxEnv $environment, string $module_path): string
    {
        $section = $environment::$current_section;
        if (!$section) {
            if (file_exists($module_path.'default_section.php')) {
                $defaultSection = '';

                require $module_path.'default_section.php';
                if ($defaultSection) {
                    $section = $defaultSection;
                }
            }
        }

        return $section;
    }

    /**
     * If we're in manage, checks if there's a valid manage session and forces login if not.
     */
    private static function checkManageSession(kxEnv $environment): void
    {
        if (IN_MANAGE) {
            $validSession = kxFunc::getManageSession();
            if (
                (
                    '' == $environment::$request->get('module')
                    || (
                        '' != $environment::$request->get('module')
                        && 'login' != $environment::$request->get('module')
                    )
                )
                && (!$validSession)) {
                // Force login if we have an invalid session

                $environment::$current_module = 'login';

                require_once kxFunc::getAppDir('core').'/modules/manage/login/login.php';
                $login = new \manage_core_login_login();
                $login->execute($environment);

                exit;
            }
        }
    }

    /**
     * Checks if the user is banned, if we're viewing a board.
     */
    private static function checkBan(kxEnv $environment, string $module, string $section): void
    {
        $boardName = '';
        if (KX_CURRENT_APP == 'core' && 'post' == $module && 'post' == $section) {
            if (isset($environment->request)) {
                $boardName = $environment::$request->get('board');
            }
        }

        kxBans::banCheck($_SERVER['REMOTE_ADDR'], $boardName);
    }

    /**
     * Requires the class defined by the module path.
     */
    private static function requireSection(string $module_path, string $section): void
    {
        $full_path = "{$module_path}{$section}.php";
        if (file_exists($full_path)) {
            require_once $full_path;
        }
    }

    private static function buildCommandClassName(string $path, string $app, string $module, string $section): string
    {
        return "{$path}_{$app}_{$module}_{$section}";
    }
}
