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
     * Render flash messages (success/error) as HTML (tavpblocks Alert
     * when available). Print inside a Volt `{% autoescape false %}` block.
     */
    protected function flashHtml(): string
    {
        $html = '';
        $success = $_SESSION['hub_flash']['success'] ?? null;
        $error = $_SESSION['hub_flash']['error'] ?? null;
        unset($_SESSION['hub_flash']['success'], $_SESSION['hub_flash']['error']);

        if (is_string($success) && $success !== '') {
            $html .= \Tavp\Hub\UI::alert($success, 'success');
        }
        if (is_string($error) && $error !== '') {
            $html .= \Tavp\Hub\UI::alert($error, 'error');
        }

        return $html;
    }

    /**
     * Column keys that should render as status badges.
     *
     * @param array<int, array<string, mixed>> $columns
     * @return string[]
     */
    protected function badgeKeys(array $columns): array
    {
        $statusish = ['status', 'state', 'active', 'is_active', 'published', 'enabled', 'visible', 'approved'];
        $keys = [];
        foreach ($columns as $col) {
            $key = $col['key'] ?? $col['field'] ?? null;
            if ($key === null) {
                continue;
            }
            if (($col['badge'] ?? false) || in_array(strtolower((string) $key), $statusish, true)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Pick a badge color for a status-ish value.
     */
    protected function badgeColor(mixed $value): string
    {
        $v = strtolower((string) $value);
        $green = ['active', 'published', 'enabled', 'visible', 'approved', '1', 'true', 'yes', 'open', 'paid'];
        $red = ['inactive', 'draft', 'banned', 'disabled', '0', 'false', 'no', 'closed', 'unpaid', 'trash'];
        $yellow = ['pending', 'review', 'waiting', 'processing'];

        if (in_array($v, $green, true)) {
            return 'green';
        }
        if (in_array($v, $red, true)) {
            return 'red';
        }
        if (in_array($v, $yellow, true)) {
            return 'yellow';
        }

        return 'gray';
    }

    /**
     * The currently authenticated user (from tavpid session), or null.
     */
    protected function currentUser(): mixed
    {
        return $this->getSessionAuth()?->user() ?? null;
    }

    /**
     * Run resource auto-discovery once, based on config('hub.discovery').
     */
    protected function ensureDiscovery(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        $cfg = config('hub.discovery');
        if ($cfg === null || $cfg === false) {
            return;
        }
        if (is_array($cfg) && ($cfg['enabled'] ?? true) === false) {
            return;
        }

        $path = is_array($cfg) ? ($cfg['path'] ?? null) : null;
        $ns = is_array($cfg) ? ($cfg['namespace'] ?? null) : null;

        if ($path === null) {
            $path = function_exists('app_path') ? app_path('Resources') : getcwd() . '/app/Resources';
        }
        if ($ns === null) {
            $ns = 'App\\Resources';
        }

        ResourceRegistry::discover($path, $ns);
    }

    /**
     * All registered resources (config + auto-discovered) as definitions.
     */
    protected function getResources(): array
    {
        $this->ensureDiscovery();

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
        $this->ensureDiscovery();

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
