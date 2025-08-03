<?php

use App\Domain\Exception\UnauthorizedException;
use App\Domain\Role\Role;
use App\Infrastructure\Auth\AuthContext;
use App\Infrastructure\Container;
use App\Shared\Response;
use Bramus\Router\Router;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/bootstrap/helpers.php';

Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();

$router = new Router();


$router->post('api/login', fn() => dispatch([Container::loginController(), 'login']));

$router->get('/api/users', fn() => dispatch([Container::userController(), 'index']));
$router->get('/api/users/(\d+)', fn($id) => dispatch([Container::userController(), 'get'],[$id]));
$router->post('/api/users', fn() => dispatch([Container::userController(), 'store']));
$router->patch('/api/users/(\d+)', fn($id) => dispatch([Container::userController(), 'update'],[$id]));
$router->delete('/api/users/(\d+)', fn($id) => dispatch([Container::userController(), 'delete'],[$id]));

$router->get('/api/vacation-requests', fn() => dispatch([Container::vacationRequestController(), 'index']));
$router->post('/api/vacation-requests', fn() => dispatch([Container::vacationRequestController(), 'store']));
$router->delete('/api/vacation-requests/(\d+)', fn($id) => dispatch([Container::vacationRequestController(), 'delete'],[$id]));
$router->patch('/api/vacation-requests/(\d+)/status', fn($id) => dispatch([Container::vacationRequestController(), 'updateStatus'],[$id]));


$router->set404(function () {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Route not found']);
});

$router->before('GET|POST|PATCH|PUT|DELETE', '/api/.*', function () {
    if (str_starts_with($_SERVER['REQUEST_URI'], '/api/login')) {
        return;
    }
    AuthContext::getInstance()->requireAuth();
});

$router->before('GET|POST|PATCH|PUT|DELETE', '/api/users.*', function () {
    AuthContext::getInstance()->requireRole(Role::MANAGER);
});
$router->before('PATCH', '/api/vacation-requests/(\d+)/status', function () {
    AuthContext::getInstance()->requireAuth();
    AuthContext::getInstance()->requireRole(Role::MANAGER);
});
$router->before('DELETE', '/api/vacation-requests/{id}', function () {
    AuthContext::getInstance()->requireRole(Role::EMPLOYEE);
});


try {
    $router->run();
} catch (UnauthorizedException $e) {
    Response::error($e->getMessage(), 401)->send();
} catch (\Throwable $e) {
    Response::error($e->getMessage(), 500)->send();
}
