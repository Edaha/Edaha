<?php

use kx\kxCmd\kxCmdResolv;
use kx\kxEnv;

const IN_MANAGE = true;

include 'init.php';

session_start();
// Load the command resolver
kxCmdResolv::run(
    kxEnv::initialize(
        KX_ENVIRONMENT,
        KX_ROOT.'/config'
    )
);

exit;
