<?php

declare(strict_types=1);

namespace Tavp\Hub;

use Tavp\Core\Routing\Router;

/**
 * Registers all TAVPhub admin routes.
 *
 * Call from your app's routes file:
 *   \Tavp\Hub\HubModule::routes($router);
 */
class HubModule
{
    public static function routes(Router $router): void
    {
        $prefix = config('hub.admin_prefix', '/admin');

        // Auth
        $router->get("{$prefix}/login", [\Tavp\Hub\Controllers\AuthController::class, 'showLogin']);
        $router->post("{$prefix}/login", [\Tavp\Hub\Controllers\AuthController::class, 'sendOtp']);
        $router->get("{$prefix}/verify", [\Tavp\Hub\Controllers\AuthController::class, 'showVerify']);
        $router->post("{$prefix}/verify", [\Tavp\Hub\Controllers\AuthController::class, 'verify']);
        $router->post("{$prefix}/logout", [\Tavp\Hub\Controllers\AuthController::class, 'logout']);

        // Dashboard
        $router->get($prefix, [\Tavp\Hub\Controllers\DashboardController::class, 'index']);

        // Resource CRUD
        $router->get("{$prefix}/resource/{resource}", [\Tavp\Hub\Controllers\ResourceController::class, 'index']);
        $router->get("{$prefix}/resource/{resource}/create", [\Tavp\Hub\Controllers\ResourceController::class, 'create']);
        $router->post("{$prefix}/resource/{resource}", [\Tavp\Hub\Controllers\ResourceController::class, 'store']);

        // Lenses (must precede {id} routes so the literal segment wins)
        $router->get("{$prefix}/resource/{resource}/lens/{lens}", [\Tavp\Hub\Controllers\ResourceController::class, 'lens']);

        // Bulk/row actions
        $router->post("{$prefix}/resource/{resource}/action/{action}", [\Tavp\Hub\Controllers\ResourceController::class, 'runAction']);

        $router->get("{$prefix}/resource/{resource}/{id}/edit", [\Tavp\Hub\Controllers\ResourceController::class, 'edit']);
        $router->post("{$prefix}/resource/{resource}/{id}", [\Tavp\Hub\Controllers\ResourceController::class, 'update']);
        $router->post("{$prefix}/resource/{resource}/{id}/delete", [\Tavp\Hub\Controllers\ResourceController::class, 'destroy']);
    }
}
