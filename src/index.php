<?php

use kx\kxCmd\kxCmdResolv;
use kx\kxEnv;

const IN_MANAGE = false;

include 'init.php';

// Load the command resolver
kxCmdResolv::run(kxEnv::getInstance());

exit;
