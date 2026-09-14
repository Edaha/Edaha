<?php

use kx\kxCmd\kxCmdResolv;
use kx\kxConfig;
use kx\kxEnv;

const IN_MANAGE = false;
const KX_ENVIRONMENT = 'dev';
const KX_ROOT = '/var/html/edaha';
const KX_CONFIG_PATH = KX_ROOT.'config/';

const KX_BOARD = KX_ROOT;
const KX_SCRIPT = KX_ROOT;
const KX_LIB = KX_ROOT.'/application/lib/kx';

require_once KX_ROOT.'/vendor/autoload.php';

// Load the command resolver
kxCmdResolv::run(
    kxEnv::initialize(
        KX_ENVIRONMENT,
        kxConfig::loadConfigFromDirectory(
            KX_ENVIRONMENT,
            KX_CONFIG_PATH
        )
    )
);

exit;
