<?php

use kx\kxEnv;

define('KX_ROOT', realpath(dirname(__FILE__)));
define('KX_BOARD', KX_ROOT);
define('KX_SCRIPT', KX_ROOT);
define('KX_LIB', KX_ROOT.'/application/lib/kx'); // Full path to kx's library files
define('KUSABA_RUNNING', true);

require_once KX_ROOT.'/vendor/autoload.php';


kxEnv::initialize('dev', KX_ROOT.'/config'); // Setup the main environment, make it read config files etc, the lots

// Cleanup global namespace
unset($repository);
