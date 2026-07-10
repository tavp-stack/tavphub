<?php

declare(strict_types=1);

namespace Tavp\Hub\Controllers;

use Tavp\Hub\HubController;
use Tavp\Hub\FormBuilder;
use Tavp\Hub\TableBuilder;
use Tavp\Core\Http\Response;

/**
 * Generic BREAD CRUD controller for any resource.
 *
 * Reads the resource definition and auto-generates index/create/edit/delete.
 */
class ResourceController extends HubController
{
    /**
     * List all records.
     */
    public function index(string $resource): string|Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $config = $this->getResourceConfig($resource);
        if ($config === null) {
            return $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        $modelClass = $config['model'] ?? null;
        $records = [];

        if ($modelClass && class_exists($modelClass)) {
            try {
                $records = $modelClass::find();
            } catch (\Throwable) {
                $records = [];
            }
        }

        return $this->view('hub::resource.index', [
            'resource' => $config,
            'resource_key' => $resource,
            'records' => $records,
            'columns' => $config['columns'] ?? [],
        ]);
    }

    /**
     * Show create form.
     */
    public function create(string $resource): string|Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $config = $this->getResourceConfig($resource);
        if ($config === null) {
            return $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        FormBuilder::flush();

        return $this->view('hub::resource.form', [
            'resource' => $config,
            'resource_key' => $resource,
            'record' => [],
            'action' => config('hub.admin_prefix', '/admin') . '/resource/' . $resource,
            'heading' => 'New ' . ($config['singular'] ?? $config['label'] ?? ucfirst($resource)),
        ]);
    }

    /**
     * Store a new record.
     */
    public function store(string $resource): Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $config = $this->getResourceConfig($resource);
        if ($config === null) {
            return $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        $modelClass = $config['model'] ?? null;
        $data = $this->collectFields($config);

        if ($modelClass && class_exists($modelClass)) {
            try {
                $modelClass::create($data);
                $this->flash('success', 'Record created successfully.');
            } catch (\Throwable $e) {
                $this->flash('error', $e->getMessage());
                FormBuilder::setOld($data);

                return $this->redirect(config('hub.admin_prefix', '/admin') . '/resource/' . $resource . '/create');
            }
        }

        return $this->redirect(config('hub.admin_prefix', '/admin') . '/resource/' . $resource);
    }

    /**
     * Show edit form.
     */
    public function edit(string $resource, string $id): string|Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $config = $this->getResourceConfig($resource);
        if ($config === null) {
            return $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        $modelClass = $config['model'] ?? null;
        $record = null;

        if ($modelClass && class_exists($modelClass)) {
            try {
                $record = $modelClass::findFirst($id);
            } catch (\Throwable) {
                $record = null;
            }
        }

        if ($record === null) {
            $this->flash('error', 'Record not found.');

            return $this->redirect(config('hub.admin_prefix', '/admin') . '/resource/' . $resource);
        }

        FormBuilder::flush();

        return $this->view('hub::resource.form', [
            'resource' => $config,
            'resource_key' => $resource,
            'record' => is_array($record) ? $record : (array) $record,
            'action' => config('hub.admin_prefix', '/admin') . '/resource/' . $resource . '/' . $id,
            'heading' => 'Edit ' . ($config['singular'] ?? $config['label'] ?? ucfirst($resource)),
        ]);
    }

    /**
     * Update a record.
     */
    public function update(string $resource, string $id): Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $config = $this->getResourceConfig($resource);
        if ($config === null) {
            return $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        $modelClass = $config['model'] ?? null;
        $data = $this->collectFields($config);

        if ($modelClass && class_exists($modelClass)) {
            try {
                $record = $modelClass::findFirst($id);
                if ($record !== null) {
                    $record->assign($data);
                    $record->save();
                    $this->flash('success', 'Record updated successfully.');
                }
            } catch (\Throwable $e) {
                $this->flash('error', $e->getMessage());
                FormBuilder::setOld($data);

                return $this->redirect(config('hub.admin_prefix', '/admin') . '/resource/' . $resource . '/' . $id . '/edit');
            }
        }

        return $this->redirect(config('hub.admin_prefix', '/admin') . '/resource/' . $resource);
    }

    /**
     * Delete a record.
     */
    public function destroy(string $resource, string $id): Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $config = $this->getResourceConfig($resource);
        if ($config === null) {
            return $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        $modelClass = $config['model'] ?? null;

        if ($modelClass && class_exists($modelClass)) {
            try {
                $record = $modelClass::findFirst($id);
                if ($record !== null) {
                    $record->delete();
                    $this->flash('success', 'Record deleted successfully.');
                }
            } catch (\Throwable $e) {
                $this->flash('error', $e->getMessage());
            }
        }

        return $this->redirect(config('hub.admin_prefix', '/admin') . '/resource/' . $resource);
    }

    private function getResourceConfig(string $resource): ?array
    {
        $resources = $this->getResources();

        return $resources[$resource] ?? null;
    }

    private function collectFields(array $config): array
    {
        $data = [];
        $fields = $config['fields'] ?? [];

        foreach ($fields as $field) {
            $name = $field['name'] ?? $field['field'] ?? '';
            if ($name !== '') {
                $value = $this->request->input($name);
                if ($value !== null) {
                    $data[$name] = $value;
                }
            }
        }

        return $data;
    }
}
