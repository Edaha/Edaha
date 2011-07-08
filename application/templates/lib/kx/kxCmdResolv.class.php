<?php
/*
 * This file is part of kusaba.
 *
 * kusaba is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * kusaba is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *  
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
/*
 * Primary command controller
 * Last Updated: $Date: 2011-05-12 17:46:09 -0500 (Thu, 12 May 2011) $
 
 * @author 		$Author: jmyeom $

 * @package		kusaba

 * @version		$Revision: 288 $
 *
 */

/**
* kxCmdResolv
* Takes incoming data and parses it
*
*/
class kxCmdResolv {
	/**
	 * Important strings
	 *
	 * @access	private
	 * @var		string
	 */
	private static $baseCmd;
	private static $defaultCmd;
	private static $class_dir    = 'public';
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct() {
			self::$baseCmd    = new ReflectionClass( 'kxCmd' );
			self::$defaultCmd = new kxCmd_default();
			self::$class_dir   = ( IN_MANAGE ) ? 'manage' : 'public';
	}

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public static function run(kxEnv $environment) {
		$instance = new kxCmdResolv();
		$cmd = $instance->getCmd($environment);
    $cmd->execute( $environment );
	}
  
	/**
	 * Retreive our command
	 *
	 * @access	public
	 * @param	object		kxEnv reference
	 * @return	object
	 */
	public function getCmd( kxEnv $environment ) {
	
		$module    = kxEnv::$current_module;
		$section   = kxEnv::$current_section;
    // No module?
    if (!$module) {
      if (IN_MANAGE && !isset(kxEnv::$request['app'])) {
        $module = 'index';
      }
      else {
      // Get the first module in the DB
      $module = kxDB::getInstance()->select("modules")
                                   ->fields("modules", array("module_file"))
                                   ->condition("module_application", KX_CURRENT_APP)
                                   ->condition("module_manage", IN_MANAGE)
                                   ->orderBy("module_position")
                                   ->execute()
                                   ->fetchField();
      }
    }
		$moduledir  = kxFunc::getAppDir( KX_CURRENT_APP ) . '/modules/' . self::$class_dir . '/' . $module . '/';
    // No section?
    if (!$section) {
      if (file_exists($moduledir.'default_section.php')){
        $defaultSection = "";
        require($moduledir.'default_section.php');
        if ($defaultSection) {
          $section = $defaultSection;
        }
      }
    }

    // Are we in manage?
    if (IN_MANAGE) {
      // Load the logging class here because we'll probably need it anyway in pretty much any manage function
      require_once( kxFunc::getAppDir('core') .'/classes/logging.php' );
      $environment->set('kx:classes:core:logging:id', new logging( $environment ) );
      
			$validSession  = kxFunc::getManageSession();
			if(($environment->request['module'] != 'login') && (!$validSession))
			{
          // Force login if we have an invalid session

					$environment->request['module'] = 'login';
 					kxEnv::$current_module = 'login';
					require_once( kxFunc::getAppDir( 'core' ) . "/modules/manage/login/login.php" );
					$login = new manage_core_login_login( $environment ); 
					$login->execute( $environment ); 

					exit();
			}
		}
    
    // Ban check ( may as well do it here before we do any further processing)
    $boardName = "";
    if (KX_CURRENT_APP == "core" && $module == "post" && $section == "post") {
      if (isset($environment->$request['board'])) {
        $boardName = $environment->$request['board'];
      }
    }
    kxBans::banCheck($_SERVER['REMOTE_ADDR'], $boardName);
    

		$className = self::$class_dir . '_' .  KX_CURRENT_APP . '_' . $module . '_' . $section;
		if (file_exists($moduledir . $section . '.php')) {
			require_once($moduledir . $section . '.php');
		}

		if (class_exists($className)) {
			$cmd_class = new ReflectionClass($className);

			if ( $cmd_class->isSubClassOf( self::$baseCmd ) ) {
				return $cmd_class->newInstance();
			}
			else {
				throw new kxException( "$section in $module does not exist!" );
			}
		}
		//If we somehow made it here, let's just use the default command
		return clone self::$defaultCmd;
	}
}

abstract class kxCmd
{
	/**
	 * Environment Shortcuts
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $environment;
	protected $db;


	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	final public function __construct() {
	}

	/**
	 * Make shortcuts for kxEnv and kxDB
	 *
	 * @access	public
	 * @param	object	kxEnv reference
	 * @return	void
	 */
	public function makeRegistryShortcuts( kxEnv $environment ) {
		$this->environment   =  $environment;
		$this->db            =  kxDB::getinstance();
    $this->request       =  kxEnv::$request;
	}

	/**
	 * Wrapper for makeRegistryShortcuts() and exec()
	 *
	 * @access	public
	 * @param	object	kxEnv reference
	 * @return	void
	 */
	public function execute( kxEnv $environment )
	{
		$this->makeRegistryShortcuts( $environment );
		$this->exec( $environment );
	}

	/**
	 * Do execute method (must be overriden)
	 *
	 * @access	protected
	 * @param	object	kxEnv reference
	 * @return	void
	 */
	protected abstract function exec( kxEnv $environment );
}

/**
* kxCmd_default
* For if we don't have a valid command, just load the index
*
*/
class kxCmd_default extends kxCmd {
	/**
	 * Do execute method
	 *
	 * @access	protected
	 * @param	object	kxCmd reference
	 * @return	void
	 */
	protected function exec( kxEnv $environment )
	{
    @header( "Location: ".kxEnv::Get('kx:paths:main:path').kxEnv::Get('kx:paths:main:folder'));
	}
}