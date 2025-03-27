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

$posts = Edaha\Entities\Post::getRecentPosts($db);

echo('<pre>');
print_r($posts);
echo('</pre>');


$posts = Edaha\Entities\Post::getRecentPosts($db, 25, 1);

echo('<pre>');
print_r($posts);
echo('</pre>');


$post = Edaha\Entities\Post::loadPostFromDb(15, 212, $db);
echo('<pre>');
print_r($post);
echo('</pre>');

$post->delete();
echo('<pre>');
print_r($post);
echo('</pre>');

$post = null;
$post = Edaha\Entities\Post::loadPostFromDb(15, 188, $db);
echo('<pre>');
print_r($post);
echo('</pre>');

$board = Edaha\Entities\Board::loadBoardFromDb(15, $db);
echo('<pre>');
print_r($board);
echo('</pre>');

echo $board->board_id;
echo $board->board_name;
echo $board->board_default_style;
echo $board->board_up_the_windows;

$board->getAllThreads();
echo('<pre>');
print_r($board);
echo('</pre>');


$thread = Edaha\Entities\Thread::loadThread(8, 168, $db);
$thread->getReplies();

echo('<pre>');
print_r($thread);
echo('</pre>');

$thread->getReplies('last', 5);

echo('<pre>');
print_r($thread);
echo('</pre>');

$thread->getReplies('first', 3);

echo('<pre>');
print_r($thread);
echo('</pre>');

