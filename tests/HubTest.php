<?php

declare(strict_types=1);

namespace Tavp\Hub\Tests;

use Tavp\Hub\Resource;
use Tavp\Hub\TableBuilder;
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
}
