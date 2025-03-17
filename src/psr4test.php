<?php
DEFINE('IN_MANAGE', false);
include "init.php";

$post = ("Edaha\\" . "Entities\\" . "Post")::LoadPost(1, 120, kxDb::getInstance());

print_r($post);

print((int) $post->is_deleted);
