<?php

declare(strict_types=1);

namespace Tavp\Hub\Controllers;

use Tavp\Hub\Action;
use Tavp\Hub\FormBuilder;
use Tavp\Hub\Filter;
use Tavp\Hub\HubController;
use Tavp\Hub\Lens;
use Tavp\Hub\Relation;
use Tavp\Hub\Resource;
use Tavp\Hub\UI;
use Tavp\Core\Database\QueryBuilder;
use Tavp\Core\Http\Response;

/**
 * Generic BREAD CRUD controller for any resource, now with filters,
 * search, lenses, actions, relationships and policy checks.
 */
class ResourceController extends HubController
{
    public function index(string $resource): string|Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $res = $this->resolveResource($resource);
        if ($res === null || $r2 = $this->deny($res, 'viewAny')) {
            return $r2 ?? $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        $modelClass = $res->model();
        $query = $this->buildQuery($res);
        $filterValues = $this->applyFilters($query, $res);
        $search = $this->applySearch($query, $res);
        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = $res->perPage();

        $rows = [];
        $pagination = ['current_page' => 1, 'last_page' => 1, 'total' => 0];

        if ($modelClass !== '' && class_exists($modelClass)) {
            $result = $query->paginate($perPage, $page);
            $rows = $this->toRows($result['data']);
            $pagination = [
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
                'total' => $result['total'],
            ];
        }

        $badgeKeys = $this->badgeKeys($res->columns());
        $rows = $this->decorateBadges($rows, $badgeKeys);

        $baseUrl = $this->indexUrl($resource, $filterValues, $search);
        $prefix = config('hub.admin_prefix', '/admin');

        return $this->view('hub::resource.index', [
            'resource' => $res->definition(),
            'resource_key' => $resource,
            'records' => $rows,
            'columns' => $res->columns(),
            'filters' => array_map(static fn ($f) => $f instanceof Filter ? $f->toArray() : $f, $res->filters()),
            'filter_values' => $filterValues,
            'search' => $search,
            'search_html' => UI::searchBar('search', $search, 'Search...'),
            'new_html' => UI::button('+ New ' . $res->singular(), 'primary', "{$prefix}/resource/{$resource}/create"),
            'lenses' => array_map(static fn ($l) => $l instanceof Lens ? $l->toArray() : $l, $res->lenses()),
            'lens_html' => $this->lensDropdown($res, null, $baseUrl),
            'active_lens' => null,
            'actions' => $this->actionList($res),
            'metrics_html' => $this->metricsHtml($res),
            'metrics' => $this->metricCards($res),
            'badge_keys' => $badgeKeys,
            'relation_options' => $this->relationOptions($res),
            'pagination' => $pagination,
            'base_url' => $baseUrl,
            'flash_html' => $this->flashHtml(),
        ]);
    }

    /**
     * Lens view — an alternate, pre-filtered index.
     */
    public function lens(string $resource, string $lens): string|Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $res = $this->resolveResource($resource);
        if ($res === null || $r2 = $this->deny($res, 'viewAny')) {
            return $r2 ?? $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        $lensObj = null;
        foreach ($res->lenses() as $l) {
            if ($l instanceof Lens && $l->name === $lens) {
                $lensObj = $l;
                break;
            }
        }

        if ($lensObj === null) {
            return $this->redirect(config('hub.admin_prefix', '/admin') . '/resource/' . $resource);
        }

        $modelClass = $res->model();
        $query = $this->buildQuery($res);
        $lensObj->query($query);

        $columns = $lensObj->columns() !== [] ? $lensObj->columns() : $res->columns();
        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = $res->perPage();

        $rows = [];
        $pagination = ['current_page' => 1, 'last_page' => 1, 'total' => 0];

        if ($modelClass !== '' && class_exists($modelClass)) {
            $result = $query->paginate($perPage, $page);
            $rows = $this->toRows($result['data']);
            $pagination = [
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
                'total' => $result['total'],
            ];
        }

        $badgeKeys = $this->badgeKeys($columns);
        $rows = $this->decorateBadges($rows, $badgeKeys);
        $prefix = config('hub.admin_prefix', '/admin');

        return $this->view('hub::resource.index', [
            'resource' => $res->definition(),
            'resource_key' => $resource,
            'records' => $rows,
            'columns' => $columns,
            'filters' => [],
            'filter_values' => [],
            'search' => '',
            'search_html' => UI::searchBar('search', '', 'Search...'),
            'new_html' => UI::button('+ New ' . $res->singular(), 'primary', "{$prefix}/resource/{$resource}/create"),
            'lenses' => array_map(static fn ($l) => $l instanceof Lens ? $l->toArray() : $l, $res->lenses()),
            'lens_html' => $this->lensDropdown($res, $lens, "{$prefix}/resource/{$resource}/lens"),
            'active_lens' => $lens,
            'actions' => $this->actionList($res),
            'metrics_html' => '',
            'metrics' => [],
            'badge_keys' => $badgeKeys,
            'relation_options' => $this->relationOptions($res),
            'pagination' => $pagination,
            'base_url' => "{$prefix}/resource/{$resource}/lens/{$lens}",
            'flash_html' => $this->flashHtml(),
        ]);
    }

    public function create(string $resource): string|Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $res = $this->resolveResource($resource);
        if ($res === null || $r2 = $this->deny($res, 'create')) {
            return $r2 ?? $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        return $this->view('hub::resource.form', [
            'resource' => $res->definition(),
            'resource_key' => $resource,
            'record' => [],
            'action' => config('hub.admin_prefix', '/admin') . '/resource/' . $resource,
            'heading' => 'New ' . ($res->singular()),
            'relation_options' => $this->relationOptions($res),
        ]);
    }

    public function store(string $resource): Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $res = $this->resolveResource($resource);
        if ($res === null || $r2 = $this->deny($res, 'create')) {
            return $r2 ?? $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        $modelClass = $res->model();
        $data = $this->collectFields($res);

        if ($modelClass !== '' && class_exists($modelClass)) {
            try {
                $model = new $modelClass();
                $model->fill($data);
                $model->save();
                $this->flash('success', 'Record created successfully.');
            } catch (\Throwable $e) {
                $this->flash('error', $e->getMessage());
                FormBuilder::setOld($data);

                return $this->redirect(config('hub.admin_prefix', '/admin') . '/resource/' . $resource . '/create');
            }
        }

        return $this->redirect(config('hub.admin_prefix', '/admin') . '/resource/' . $resource);
    }

    public function edit(string $resource, string $id): string|Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $res = $this->resolveResource($resource);
        if ($res === null || $r2 = $this->deny($res, 'viewAny')) {
            return $r2 ?? $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        $modelClass = $res->model();
        $record = null;

        if ($modelClass !== '' && class_exists($modelClass)) {
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

        if ($r3 = $this->deny($res, 'view', $record)) {
            return $r3;
        }

        return $this->view('hub::resource.form', [
            'resource' => $res->definition(),
            'resource_key' => $resource,
            'record' => is_array($record) ? $record : (array) $record,
            'action' => config('hub.admin_prefix', '/admin') . '/resource/' . $resource . '/' . $id,
            'heading' => 'Edit ' . ($res->singular()),
            'relation_options' => $this->relationOptions($res, is_array($record) ? $record : (array) $record),
        ]);
    }

    public function update(string $resource, string $id): Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $res = $this->resolveResource($resource);
        if ($res === null || $r2 = $this->deny($res, 'viewAny')) {
            return $r2 ?? $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        $modelClass = $res->model();
        $data = $this->collectFields($res);

        if ($modelClass !== '' && class_exists($modelClass)) {
            try {
                $record = $modelClass::findFirst($id);
                if ($record !== null) {
                    if ($r3 = $this->deny($res, 'update', $record)) {
                        return $r3;
                    }
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

    public function destroy(string $resource, string $id): Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $res = $this->resolveResource($resource);
        if ($res === null || $r2 = $this->deny($res, 'viewAny')) {
            return $r2 ?? $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        $modelClass = $res->model();

        if ($modelClass !== '' && class_exists($modelClass)) {
            try {
                $record = $modelClass::findFirst($id);
                if ($record !== null) {
                    if ($r3 = $this->deny($res, 'delete', $record)) {
                        return $r3;
                    }
                    $record->delete();
                    $this->flash('success', 'Record deleted successfully.');
                }
            } catch (\Throwable $e) {
                $this->flash('error', $e->getMessage());
            }
        }

        return $this->redirect(config('hub.admin_prefix', '/admin') . '/resource/' . $resource);
    }

    /**
     * Run a bulk/row action against selected record ids.
     */
    public function runAction(string $resource, string $action): Response
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $res = $this->resolveResource($resource);
        if ($res === null || $r2 = $this->deny($res, 'viewAny')) {
            return $r2 ?? $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        $actionObj = null;
        foreach ($res->actions() as $a) {
            if ($a instanceof Action && $a->name === $action) {
                $actionObj = $a;
                break;
            }
        }

        if ($actionObj === null) {
            $this->flash('error', 'Unknown action.');

            return $this->redirect(config('hub.admin_prefix', '/admin') . '/resource/' . $resource);
        }

        $ids = $this->request->input('ids', []);
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $modelClass = $res->model();

        try {
            $actionObj->handle($ids, $modelClass);
            $this->flash('success', 'Action "' . $actionObj->label . '" completed.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }

        return $this->redirect(config('hub.admin_prefix', '/admin') . '/resource/' . $resource);
    }

    // ------------------------------------------------------------------
    // Internal helpers
    // ------------------------------------------------------------------

    private function deny(Resource $res, string $ability, mixed $model = null): ?Response
    {
        if (!$this->authorize($res, $ability, $model)) {
            $this->flash('error', 'You are not authorized to perform this action.');

            return $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        return null;
    }

    private function buildQuery(Resource $res): QueryBuilder
    {
        $modelClass = $res->model();

        return new QueryBuilder(new $modelClass());
    }

    /**
     * @return array<string, mixed>
     */
    private function applyFilters(QueryBuilder $query, Resource $res): array
    {
        $values = [];
        $modelClass = $res->model();

        foreach ($res->filters() as $filter) {
            if (!$filter instanceof Filter) {
                continue;
            }

            $value = $this->request->input($filter->name, $filter->default);
            $values[$filter->name] = $value;

            if ($modelClass !== '' && class_exists($modelClass)) {
                $filter->apply($query, $value);
            }
        }

        return $values;
    }

    private function applySearch(QueryBuilder $query, Resource $res): string
    {
        $search = (string) $this->request->input('search', '');
        $search = trim($search);

        if ($search === '') {
            return '';
        }

        $columns = $res->searchableColumns();
        if ($columns === []) {
            return $search;
        }

        foreach ($columns as $column) {
            $query->orWhere($column, 'LIKE', '%' . $search . '%');
        }

        return $search;
    }

    private function indexUrl(string $resource, array $filterValues, string $search): string
    {
        $prefix = config('hub.admin_prefix', '/admin');
        $params = [];
        foreach ($filterValues as $k => $v) {
            if ($v !== null && $v !== '' && $v !== 'all') {
                $params[] = $k . '=' . urlencode((string) $v);
            }
        }
        if ($search !== '') {
            $params[] = 'search=' . urlencode($search);
        }

        $url = "{$prefix}/resource/{$resource}";

        return $params === [] ? $url : $url . '?' . implode('&', $params);
    }

    /**
     * @param mixed $data
     * @return array<int, array<string, mixed>>
     */
    private function toRows(mixed $data): array
    {
        $rows = [];
        if ($data === null) {
            return $rows;
        }

        foreach ($data as $model) {
            $rows[] = method_exists($model, 'toArray') ? $model->toArray() : (array) $model;
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function actionList(Resource $res): array
    {
        $out = [];
        foreach ($res->actions() as $a) {
            $out[] = $a instanceof Action ? $a->toArray() : $a;
        }

        return $out;
    }

    /**
     * Replace status-ish cell values with pre-rendered badge HTML.
     *
     * @param array<int, array<string, mixed>> $rows
     * @param string[] $badgeKeys
     * @return array<int, array<string, mixed>>
     */
    private function decorateBadges(array $rows, array $badgeKeys): array
    {
        if ($badgeKeys === []) {
            return $rows;
        }

        foreach ($rows as &$row) {
            foreach ($badgeKeys as $key) {
                if (!array_key_exists($key, $row)) {
                    continue;
                }
                $row[$key] = UI::badge((string) $row[$key], $this->badgeColor($row[$key]));
            }
        }

        return $rows;
    }

    /**
     * Build the lens switcher dropdown (tavpblocks Dropdown when available).
     */
    private function lensDropdown(Resource $res, ?string $active, string $baseUrl): string
    {
        $prefix = config('hub.admin_prefix', '/admin');
        $items = [['label' => 'All', 'url' => "{$prefix}/resource/{$res->uriKey()}"]];

        foreach ($res->lenses() as $lens) {
            if (!$lens instanceof Lens) {
                continue;
            }
            $items[] = ['label' => $lens->label, 'url' => "{$baseUrl}/{$lens->name}"];
        }

        return UI::dropdown('Lenses', $items);
    }

    /**
     * Render the resource's metric cards (+ trend charts) as HTML.
     */
    private function metricsHtml(Resource $res): string
    {
        $cards = '';
        $charts = '';

        foreach ($res->metrics() as $metric) {
            if (!is_object($metric) || !method_exists($metric, 'calculate')) {
                continue;
            }
            $computed = $metric->calculate($res->model());
            $cards .= UI::statCard(
                $metric->label,
                $computed['value'] ?? 0,
                $computed['delta'] ?? '',
                $computed['deltaColor'] ?? 'gray'
            );

            if ($metric instanceof \Tavp\Hub\TrendMetric) {
                $charts .= UI::chart($metric->label, $computed['series'] ?? [], 'line', 90);
            }
        }

        $html = '';
        if ($cards !== '') {
            $html .= '<div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">' . $cards . '</div>';
        }
        if ($charts !== '') {
            $html .= '<div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">' . $charts . '</div>';
        }

        return $html;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function metricCards(Resource $res): array
    {
        $out = [];
        $modelClass = $res->model();

        foreach ($res->metrics() as $m) {
            if (!is_object($m) || !method_exists($m, 'calculate')) {
                continue;
            }
            $computed = $m->calculate($modelClass);
            $out[] = [
                'label' => $m->label,
                'value' => $computed['value'] ?? 0,
                'delta' => $computed['delta'] ?? '',
                'delta_color' => $computed['deltaColor'] ?? 'gray',
                'icon' => $m->icon,
            ];
        }

        return $out;
    }

    /**
     * Build belongsTo option lists for form selects.
     *
     * @return array<string, array<int, array{value:mixed, label:string}>>
     */
    private function relationOptions(Resource $res, array $record = []): array
    {
        $options = [];

        foreach ($res->fields() as $field) {
            if (($field['type'] ?? '') === 'belongsTo' && isset($field['resource'])) {
                $options[$field['name']] = $this->loadOptions($field['resource'], $field['label_column'] ?? 'name');
            }
        }

        foreach ($res->relations() as $rel) {
            if ($rel instanceof Relation && $rel->type === 'belongsTo') {
                $options[$rel->name] = $this->loadOptions($rel->relatedResource, $rel->labelColumn ?? 'name');
            }
        }

        return $options;
    }

    /**
     * @return array<int, array{value:mixed, label:string}>
     */
    private function loadOptions(string $relatedKey, string $labelColumn): array
    {
        $related = $this->resolveResource($relatedKey);
        if ($related === null) {
            return [];
        }

        $modelClass = $related->model();
        if ($modelClass === '' || !class_exists($modelClass)) {
            return [];
        }

        try {
            $rows = $modelClass::find();
        } catch (\Throwable) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'value' => $row->id ?? null,
                'label' => $row->{$labelColumn} ?? ($row->id ?? ''),
            ];
        }

        return $out;
    }

    /**
     * Collect submitted field values, including belongsTo relations.
     *
     * @return array<string, mixed>
     */
    private function collectFields(Resource $res): array
    {
        $data = [];

        foreach ($res->fields() as $field) {
            $name = $field['name'] ?? $field['field'] ?? '';
            if ($name === '') {
                continue;
            }

            if (($field['type'] ?? '') === 'checkbox' || ($field['type'] ?? '') === 'toggle') {
                $data[$name] = $this->request->input($name, '0');
                continue;
            }

            $value = $this->request->input($name);
            if ($value !== null) {
                $data[$name] = $value;
            }
        }

        foreach ($res->relations() as $rel) {
            if ($rel instanceof Relation && $rel->type === 'belongsTo') {
                $value = $this->request->input($rel->name);
                if ($value !== null) {
                    $data[$rel->name] = $value;
                }
            }
        }

        return $data;
    }
}
