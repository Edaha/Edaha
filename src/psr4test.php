<?php
DEFINE('IN_MANAGE', false);
include "init.php";

$db = kxDb::getInstance();
$post = ("Edaha\\" . "Entities\\" . "Post")::loadPostFromDb(1, 120, $db);

echo('<pre>');
print_r($post);

print((int) $post->is_deleted);
echo('</pre>');

$thread = Edaha\Entities\Thread::loadThread(8, 168, $db);
$thread->getAllReplies();

echo('<pre>');
print_r($thread);
echo('</pre>');

$thread = Edaha\Entities\Thread::loadThread(69, 69, $db);
if ($thread) { 
    echo 'true'; 
} else 
{ 
    echo 'false';
}
