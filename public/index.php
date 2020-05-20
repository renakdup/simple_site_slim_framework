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

// Получаем роутер – объект отвечающий за хранение и обработку маршрутов
$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function (Request $request, Response $response) use ($router) {
    return $response->write('Welcome to Slim!');
});

$app->get('/users', function (Request $request, Response $response, array $args) use ($router) {
    $searchQuery = $request->getQueryParam('search_query', false);

    if ($searchQuery) {
        $users = searchUsers($searchQuery, 'nickname');
    } else {
        $users = getUsers();
    }

    $params = [
        'linkUsers' => $router->urlFor('users'),
        'linkUsersNew' => $router->urlFor('addUser'),
        'users' => $users,
        'searchQuery' => $searchQuery
    ];

    return $this->get('renderer')->render($response, $router->urlFor('users') . '/users.phtml', $params);
})->setName('users');

$app->post('/users', function (Request $request, Response $response, array $args) use ($router) {
    $user = $request->getParsedBodyParam('user', []);
    $errors = validateUserForm($user);

    if (count($errors) === 0) {
        saveUser($user);
        return $response->withRedirect($router->urlFor('users'), 302);
    }

    $params = [
        'linkUsers' => $router->urlFor('users'),
        'linkUsersNew' => $router->urlFor('addUser'),
        'user' => $user,
        'errors' => $errors,
    ];

    return $this->get('renderer')->render($response->withStatus(422), $router->urlFor('users') . '/new.phtml', $params);
});

$app->get('/users/new', function (Request $request, Response $response, array $args) use ($router) {
    $params = [
        'linkUsers' => $router->urlFor('users'),
        'linkUsersNew' => $router->urlFor('addUser'),
        'user' => [
            'nickname' => '',
            'email' => '',
            'id' => '',
        ],
        'errors' => [],
    ];

    return $this->get('renderer')->render($response, '/users/new.phtml', $params);
})->setName('addUser');

$app->get('/users/id/{id:[0-9]+}', function (Request $request, Response $response, array $args) use ($router) {
    return $this->get('renderer')->render($response, '/users/show.phtml', $args);
})->setName('user');

$app->run();
