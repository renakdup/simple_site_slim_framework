<?php

declare(strict_types=1);

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response;
use DI\Container;
use function Renakdup\Src\validateUserForm;
use function Renakdup\Src\saveUser;
use function Renakdup\Src\getUsers;
use function Renakdup\Src\searchUsers;

define('ABSPATH', dirname(__DIR__));

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) {
    return $response->write('Welcome to Slim!');
});

$app->get('/users', function (Request $request, Response $response, array $args) {
    $searchQuery = $request->getQueryParam('search_query', false);

    if ($searchQuery) {
        $users = searchUsers($searchQuery, 'nickname');
    } else {
        $users = getUsers();
    }

    $params = [
        'users' => $users,
        'searchQuery' => $searchQuery
    ];

    return $this->get('renderer')->render($response, 'users/users.phtml', $params);
});

$app->post('/users', function (Request $request, Response $response, array $args) {
    $user = $request->getParsedBodyParam('user', []);
    $errors = validateUserForm($user);

    if (count($errors) === 0) {
        saveUser($user);
        return $response->withRedirect('/users?added=true', 302);
    }

    $params = [
        'user' => $user,
        'errors' => $errors,
    ];

    return $this->get('renderer')->render($response, '/users/new.phtml', $params);
});

$app->get('/users/new', function (Request $request, Response $response, array $args) {
    $params = [
        'user' => [],
        'errors' => [],
    ];

    return $this->get('renderer')->render($response, '/users/new.phtml', $params);
});


$app->run();
