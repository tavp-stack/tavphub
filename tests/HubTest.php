<?php

declare(strict_types=1);

namespace Tavp\Hub\Tests;

use Tavp\Hub\Action;
use Tavp\Hub\Filter;
use Tavp\Hub\Lens;
use Tavp\Hub\Policy;
use Tavp\Hub\Relation;
use Tavp\Hub\Resource;
use Tavp\Hub\ResourceRegistry;
use Tavp\Hub\TableBuilder;
use Tavp\Hub\ValueMetric;
use PHPUnit\Framework\TestCase;

class PostResource extends Resource
{
    public function label(): string { return 'Posts'; }
    public function model(): string { return 'App\\Post'; }
    public function columns(): array
    {
        return [
            ['key' => 'id', 'label' => 'ID', 'sortable' => true],
            ['key' => 'title', 'label' => 'Title', 'sortable' => true, 'searchable' => true],
            ['key' => 'created_at', 'label' => 'Created', 'sortable' => false],
        ];
    }
    public function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'label' => 'Title'],
            ['name' => 'body', 'type' => 'textarea', 'label' => 'Body'],
        ];
    }
}

class UserResource extends Resource
{
    public function label(): string { return 'Users'; }
    public function model(): string { return 'App\\User'; }
    public function columns(): array
    {
        return [['key' => 'id', 'label' => 'ID'], ['key' => 'name', 'label' => 'Name']];
    }
    public function fields(): array
    {
        return [
            ['name' => 'name', 'type' => 'text', 'label' => 'Name'],
            ['name' => 'role_id', 'type' => 'belongsTo', 'resource' => 'roles', 'label_column' => 'name'],
        ];
    }
    public function searchableColumns(): array { return ['name', 'email']; }
    public function filters(): array
    {
        return [(new Filter('status'))->options(['active', 'inactive'])->type('select')];
    }
    public function metrics(): array
    {
        return [(new ValueMetric('total', 'Total Users'))->aggregate('count')];
    }
    public function actions(): array
    {
        return [new class('export', 'Export') extends Action {
            public function handle(array $ids, string $modelClass): void {}
        }];
    }
    public function lenses(): array
    {
        return [new class('admins', 'Admins') extends Lens {
            public function query($query): void { $query->where('role', 'admin'); }
        }];
    }
    public function relations(): array
    {
        return [new Relation('role_id', 'belongsTo', 'roles', 'name', 'role_id')];
    }
    public function policy(): ?string
    {
        return UserPolicy::class;
    }
}

class UserPolicy extends Policy
{
    public function before(mixed $user): ?bool
    {
        if ($user === 'superadmin') {
            return true;
        }
        return null;
    }
    public function delete(mixed $user, mixed $model): bool
    {
        return $user !== null;
    }
}

class FakeModel
{
    public static function count(): int { return 42; }
    public static function sum(array $p = []): float { return 100.0; }
    public static function average(array $p = []): float { return 5.0; }
}

class HubTest extends TestCase
{
    public function testResourceDefinition(): void
    {
        $resource = new PostResource();
        $def = $resource->definition();
        $this->assertSame('Posts', $def['label']);
        $this->assertCount(3, $def['columns']);
        $this->assertCount(2, $def['fields']);
        $this->assertTrue($def['in_sidebar']);
    }

    public function testTableBuilderFiltersSortableAndSearchable(): void
    {
        $resource = new PostResource();
        $builder = new TableBuilder($resource->columns());

        $this->assertCount(2, $builder->sortable());
        $this->assertCount(1, $builder->searchable());

        $header = $builder->renderHeader();
        $this->assertStringContainsString('<th>ID</th>', $header);
        $this->assertStringContainsString('<th>Title</th>', $header);
    }

    public function testUriKeyDerivedFromClassName(): void
    {
        $this->assertSame('user', (new UserResource())->uriKey());
    }

    public function testResourceRegistryRegisterAndResolve(): void
    {
        ResourceRegistry::clear();
        ResourceRegistry::register(new UserResource());
        $this->assertTrue(ResourceRegistry::has('user'));
        $this->assertInstanceOf(UserResource::class, ResourceRegistry::get('user'));
        ResourceRegistry::clear();
    }

    public function testFromConfigBuildsResource(): void
    {
        $res = Resource::fromConfig([
            'label' => 'Books',
            'model' => 'App\\Book',
            'columns' => [['key' => 'id', 'label' => 'ID']],
            'fields' => [['name' => 'title', 'type' => 'text']],
            'icon' => 'book',
        ], 'books');

        $this->assertSame('books', $res->uriKey());
        $this->assertSame('book', $res->icon());
        $def = $res->definition();
        $this->assertSame('Books', $def['label']);
        $this->assertCount(1, $def['columns']);
    }

    public function testFilterToArray(): void
    {
        $filter = (new Filter('status'))->options(['a', 'b'])->type('select');
        $arr = $filter->toArray();
        $this->assertSame('status', $arr['name']);
        $this->assertSame('select', $arr['type']);
        $this->assertSame(['a', 'b'], $arr['options']);
    }

    public function testValueMetricCalculate(): void
    {
        $metric = (new ValueMetric('total', 'Total'))->aggregate('count');
        $result = $metric->calculate(FakeModel::class);
        $this->assertSame(42, $result['value']);
    }

    public function testValueMetricDelta(): void
    {
        $metric = (new ValueMetric('total', 'Total'))->aggregate('count')
            ->compareTo(static fn () => 21);
        $result = $metric->calculate(FakeModel::class);
        $this->assertSame('+100%', $result['delta']);
        $this->assertSame('green', $result['deltaColor']);
    }

    public function testPolicyBeforeShortCircuits(): void
    {
        $policy = new UserPolicy();
        $res = new UserResource();
        // Resolve policy via resource->policy() and test behavior through the policy object.
        $this->assertTrue($policy->before('superadmin'));
        $this->assertNull($policy->before('editor'));
        $this->assertTrue($policy->delete('editor', null));
        $this->assertFalse($policy->delete(null, null));
    }

    public function testRelationDefinition(): void
    {
        $res = new UserResource();
        $def = $res->definition();
        $this->assertCount(1, $def['relations']);
        $this->assertSame('roles', $def['relations'][0]['related_resource']);
    }

    public function testLensAndActionRegistered(): void
    {
        $res = new UserResource();
        $this->assertCount(1, $res->lenses());
        $this->assertCount(1, $res->actions());
    }
}
