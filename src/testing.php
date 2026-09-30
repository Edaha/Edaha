<?php

require_once __DIR__.'/vendor/autoload.php';

use kx\kxEnv;

const KX_CONFIG_PATH = '../config/edaha/';

$environment = kxEnv::initialize('test');

var_dump($environment);
