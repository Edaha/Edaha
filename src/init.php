<?php
DEFINE('KX_ROOT', realpath(dirname(__FILE__)));
DEFINE('KX_BOARD', KX_ROOT);
DEFINE('KX_SCRIPT', KX_ROOT);
DEFINE('KX_LIB', KX_ROOT . '/application/lib/kx'); // Full path to kx's library files
DEFINE('KUSABA_RUNNING', true);

// require_once (KX_LIB . "/../Twig/Autoloader.php");
require_once (KX_ROOT . "/vendor/autoload.php");
// Twig_Autoloader :: register();
require_once (KX_LIB . '/kxAutoload.class.php');

$repository = kxAutoload :: registerRepository(KX_LIB, array (
    'prefix' => 'kx'
)); // Add the autoloader repository in kx's lib dir, listen only for classes starting with the string 'kx'

kxEnv :: initialize('dev', KX_ROOT . '/config'); // Setup the main environment, make it read config files etc, the lots
kxEnv :: set('kx:autoload:repository:kx:id', $repository); // If we want to unload the kx autoloader at some point, store the id here.

// Cleanup global namespace
unset ($repository);
?>
