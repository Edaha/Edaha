<?php

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

include 'init.php';

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('GET', '/users', 'get_all_users_handler');
    // {id} must be a number (\d+)
    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
    // The /{title} suffix is optional
    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Routes
/**
 * GET:
 * / --> Default page, classically a news page (news/faq/php)
 * /boards/{dir} --> Index page for a board. May start with a /boards/ subpath but ideally would get to where it could be /{board}/ instead for tradition.
 * /boards/{dir}/{id:\d+} --> Thread view
 * /boards/{dir}/{id:\d}/first50/last100
 * /manage/{module}/{section}/{action}
 *
 * POST:
 * /boards/{dir} --> New post on the board
 * /boards/{dir}/{id:\d+} --> New reply to the thread
 * /manage/{module}/{section}/{action} --> Submit whatever for the action
 *
 * OTHERS:
 * None at this time.
 */

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        break;

    case Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];

        // ... 405 Method Not Allowed
        break;

    case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        // ... call $handler with $vars
        break;
}
