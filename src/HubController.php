<?php

declare(strict_types=1);

namespace Tavp\Hub;

use Tavp\Core\Controllers\BaseController;
use Tavp\Core\Http\Response;

/**
 * Base controller for all TAVPhub admin controllers.
 *
 * Provides auth guard, view rendering, redirects, flash messages,
 * resource resolution and policy (authorization) checks.
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

    /**
     * The currently authenticated user (from tavpid session), or null.
     */
    protected function currentUser(): mixed
    {
        return $this->getSessionAuth()?->user() ?? null;
    }

    /**
     * All registered resources (config + auto-discovered) as definitions.
     */
    protected function getResources(): array
    {
        $merged = config('hub.resources', []);

        foreach (ResourceRegistry::all() as $key => $resource) {
            $merged[$key] = $resource->definition();
        }

        return $merged;
    }

    /**
     * Resolve a Resource object by key (registry first, then config).
     */
    protected function resolveResource(string $key): ?Resource
    {
        if (ResourceRegistry::has($key)) {
            return ResourceRegistry::get($key);
        }

        $cfg = config("hub.resources.{$key}");

        return is_array($cfg) ? Resource::fromConfig($cfg, $key) : null;
    }

    /**
     * Resolve the policy instance for a resource, if any.
     */
    protected function policyFor(Resource $resource): ?Policy
    {
        $policyClass = $resource->policy();

        if ($policyClass === null || !class_exists($policyClass)) {
            return null;
        }

        return new $policyClass();
    }

    /**
     * Check an ability against the resource's policy.
     */
    protected function authorize(Resource $resource, string $ability, mixed $model = null): bool
    {
        $policy = $this->policyFor($resource);

        if ($policy === null) {
            return true;
        }

        $user = $this->currentUser();
        $before = $policy->before($user);

        if ($before !== null) {
            return $before;
        }

        return match ($ability) {
            'viewAny' => $policy->viewAny($user),
            'view' => $policy->view($user, $model),
            'create' => $policy->create($user),
            'update' => $policy->update($user, $model),
            'delete' => $policy->delete($user, $model),
            'restore' => $policy->restore($user, $model),
            'forceDelete' => $policy->forceDelete($user, $model),
            default => true,
        };
    }

    private function buildSidebar(): array
    {
        $sidebar = [];
        $prefix = config('hub.admin_prefix', '/admin');

        $sidebar[] = ['label' => 'Media', 'url' => "{$prefix}/media", 'icon' => 'image'];
        $sidebar[] = ['label' => 'Menus', 'url' => "{$prefix}/menus", 'icon' => 'list'];
        $sidebar[] = ['label' => 'Teams', 'url' => "{$prefix}/teams", 'icon' => 'group'];
        $sidebar[] = ['label' => 'Billing', 'url' => "{$prefix}/billing", 'icon' => 'payment'];
        $sidebar[] = ['label' => 'Settings', 'url' => "{$prefix}/settings", 'icon' => 'cog'];

        $resources = $this->getResources();
        foreach ($resources as $key => $config) {
            if (!($config['in_sidebar'] ?? true)) {
                continue;
            }

            $sidebar[] = [
                'label' => $config['label'] ?? ucfirst($key),
                'url' => "{$prefix}/resource/" . $key,
                'icon' => $config['icon'] ?? 'document',
            ];
        }

        return $sidebar;
    }
}
