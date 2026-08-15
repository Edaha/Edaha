<?php

use kx\kxEnv;

const KX_ROOT = realpath(dirname(__FILE__));
const KX_BOARD = KX_ROOT;
const KX_SCRIPT = KX_ROOT;
const KX_LIB = KX_ROOT.'/application/lib/kx';
const KUSABA_RUNNING = true;

require_once KX_ROOT.'/vendor/autoload.php';

kxEnv::initialize('dev', KX_ROOT.'/config'); // Setup the main environment, make it read config files etc, the lots
