<?php

declare(strict_types=1);

namespace Tavp\Hub;

use Tavp\Core\Controllers\BaseController;
use Tavp\Core\Http\Response;

/**
 * Base controller for all TAVPhub admin controllers.
 *
 * Provides auth guard, view rendering, redirects, and flash messages.
 */
abstract class HubController extends BaseController
{
    protected function guard(): ?Response
    {
        $auth = $this->getSessionAuth();

        if ($auth === null || !$auth->check()) {
            return $this->redirect(config('hub.admin_prefix', '/admin') . '/login');
        }

        return null;
    }

    protected function view(string $template, array $data = []): string
    {
        $data['__brand'] = config('hub.brand', 'TAVP Admin');
        $data['__sidebar'] = $this->buildSidebar();

        return view($template, $data);
    }

    protected function partial(string $template, array $data = []): string
    {
        return view($template, $data);
    }

    protected function redirect(string $path, int $status = 302): Response
    {
        return redirect($path, $status);
    }

    protected function flash(string $key, mixed $value): void
    {
        $_SESSION['hub_flash'][$key] = $value;
    }

    protected function getFlash(string $key): mixed
    {
        $value = $_SESSION['hub_flash'][$key] ?? null;
        unset($_SESSION['hub_flash'][$key]);

        return $value;
    }

    protected function getSessionAuth(): ?\Tavp\Tavpid\Auth\SessionAuth
    {
        try {
            return app()->getService('hub.session_auth');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function getResources(): array
    {
        return config('hub.resources', []);
    }

    private function buildSidebar(): array
    {
        $sidebar = [];
        $resources = $this->getResources();

        foreach ($resources as $key => $config) {
            $sidebar[] = [
                'label' => $config['label'] ?? ucfirst($key),
                'url' => config('hub.admin_prefix', '/admin') . '/resource/' . $key,
                'icon' => $config['icon'] ?? 'document',
            ];
        }

        return $sidebar;
    }
}
