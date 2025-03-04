<?php
DEFINE('IN_MANAGE', true);
include "init.php";

//Load the command resolver
kxCmdResolv::run(kxEnv::getInstance());
exit();
?>