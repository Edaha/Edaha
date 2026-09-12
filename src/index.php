<?php

use kx\kxCmd\kxCmdResolv;
use kx\kxConfig;
use kx\kxEnv;

const IN_MANAGE = false;

include 'init.php';

// Load the command resolver
kxCmdResolv::run(
    kxEnv::initialize(
        KX_ENVIRONMENT,
        kxConfig::loadConfigFromDirectory(
            KX_ENVIRONMENT,
            KX_ROOT.'/config'
        )
    )
);

exit;
