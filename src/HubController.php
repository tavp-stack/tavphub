<?php

declare(strict_types=1);

namespace Tavp\Hub;

use Tavp\Cms\Admin\AdminController;
use Tavp\Cms\Bread\BreadManager;
use Tavp\Core\Http\Response;
use Tavp\Core\Routing\Router;

/**
 * TAVPhub admin controller — generic CRUD for any registered Resource.
 *
 * Routes are auto-generated from resource definitions.
 */
class HubController extends AdminController
{
    private array $resources;

    public function __construct(array $resources = [])
    {
        parent::__construct();
        $this->resources = $resources;
    }

    /**
     * Register hub routes.
     */
    public static function routes(Router $router): void
    {
        $router->get('/hub', [self::class, 'dashboard']);
        $router->get('/hub/{resource}', [self::class, 'index']);
        $router->get('/hub/{resource}/create', [self::class, 'create']);
        $router->post('/hub/{resource}', [self::class, 'store']);
        $router->get('/hub/{resource}/{id}/edit', [self::class, 'edit']);
        $router->post('/hub/{resource}/{id}', [self::class, 'update']);
        $router->post('/hub/{resource}/{id}/delete', [self::class, 'destroy']);
    }

    /**
     * Hub dashboard — overview of all resources.
     */
    public function dashboard(): Response
    {
        if ($this->guard()) {
            return $this->redirect('/admin/login');
        }

        $resources = [];

        foreach ($this->resources as $key => $resource) {
            if (!$resource->inSidebar()) {
                continue;
            }

            $count = 0;
            try {
                $bread = app()->getService(BreadManager::class);
                $type = $bread->type($key);
                if ($type) {
                    $count = $bread->count($key);
                }
            } catch (\Throwable) {}

            $resources[] = [
                'key' => $key,
                'label' => $resource->label(),
                'singular' => $resource->singular(),
                'icon' => $resource->icon(),
                'count' => $count,
            ];
        }

        return new Response($this->admin('hub_dashboard', [
            'resources' => $resources,
        ]));
    }

    /**
     * Browse records for a resource.
     */
    public function index(string $resource): Response
    {
        if ($this->guard()) {
            return $this->redirect('/admin/login');
        }

        $res = $this->resolveResource($resource);
        if (!$res) {
            return $this->redirect('/hub');
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = $res->perPage();
        $search = $_GET['search'] ?? '';

        $records = [];
        $total = 0;

        try {
            $bread = app()->getService(BreadManager::class);
            $result = $bread->browse($resource, [
                'page' => $page,
                'per_page' => $perPage,
                'search' => $search,
            ]);
            $records = $result['data'] ?? [];
            $total = $result['total'] ?? 0;
        } catch (\Throwable) {}

        return new Response($this->admin('hub_index', [
            'resource' => $resource,
            'resourceObj' => $res,
            'records' => $records,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'search' => $search,
        ]));
    }

    /**
     * Show create form.
     */
    public function create(string $resource): Response
    {
        if ($this->guard()) {
            return $this->redirect('/admin/login');
        }

        $res = $this->resolveResource($resource);
        if (!$res) {
            return $this->redirect('/hub');
        }

        return new Response($this->admin('hub_form', [
            'resource' => $resource,
            'resourceObj' => $res,
            'record' => null,
            'errors' => $this->getFlash('errors') ?? [],
            'old' => $this->getFlash('old') ?? [],
        ]));
    }

    /**
     * Store a new record.
     */
    public function store(string $resource): Response
    {
        if ($this->guard()) {
            return $this->redirect('/admin/login');
        }

        $res = $this->resolveResource($resource);
        if (!$res) {
            return $this->redirect('/hub');
        }

        try {
            $bread = app()->getService(BreadManager::class);
            $data = $_POST;

            $bread->store($resource, $data);

            $this->flash('success', $res->singular() . ' created successfully.');

            return $this->redirect('/hub/' . $resource);
        } catch (\Throwable $e) {
            $this->flash('errors', ['general' => $e->getMessage()]);
            $this->flash('old', $_POST);

            return $this->redirect('/hub/' . $resource . '/create');
        }
    }

    /**
     * Show edit form.
     */
    public function edit(string $resource, string $id): Response
    {
        if ($this->guard()) {
            return $this->redirect('/admin/login');
        }

        $res = $this->resolveResource($resource);
        if (!$res) {
            return $this->redirect('/hub');
        }

        $record = null;
        try {
            $bread = app()->getService(BreadManager::class);
            $record = $bread->read($resource, (int) $id);
        } catch (\Throwable) {}

        if (!$record) {
            return $this->redirect('/hub/' . $resource);
        }

        return new Response($this->admin('hub_form', [
            'resource' => $resource,
            'resourceObj' => $res,
            'record' => $record,
            'errors' => $this->getFlash('errors') ?? [],
            'old' => $this->getFlash('old') ?? [],
        ]));
    }

    /**
     * Update a record.
     */
    public function update(string $resource, string $id): Response
    {
        if ($this->guard()) {
            return $this->redirect('/admin/login');
        }

        $res = $this->resolveResource($resource);
        if (!$res) {
            return $this->redirect('/hub');
        }

        try {
            $bread = app()->getService(BreadManager::class);
            $bread->update($resource, (int) $id, $_POST);

            $this->flash('success', $res->singular() . ' updated successfully.');

            return $this->redirect('/hub/' . $resource);
        } catch (\Throwable $e) {
            $this->flash('errors', ['general' => $e->getMessage()]);
            $this->flash('old', $_POST);

            return $this->redirect('/hub/' . $resource . '/' . $id . '/edit');
        }
    }

    /**
     * Delete a record.
     */
    public function destroy(string $resource, string $id): Response
    {
        if ($this->guard()) {
            return $this->redirect('/admin/login');
        }

        $res = $this->resolveResource($resource);
        if (!$res) {
            return $this->redirect('/hub');
        }

        try {
            $bread = app()->getService(BreadManager::class);
            $bread->delete($resource, (int) $id);

            $this->flash('success', $res->singular() . ' deleted successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }

        return $this->redirect('/hub/' . $resource);
    }

    private function resolveResource(string $key): ?Resource
    {
        return $this->resources[$key] ?? null;
    }
}
