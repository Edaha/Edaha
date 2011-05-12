<?php

include "init.php";
DEFINE('IN_MANAGE', false);

//Load the command resolver
kxCmdResolv::run(kxEnv::getInstance());

exit();
?>