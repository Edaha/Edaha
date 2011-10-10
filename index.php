<?php
DEFINE('IN_MANAGE', false);
include "init.php";

//Load the command resolver
kxCmdResolv::run(kxEnv::getInstance());

exit();
?>