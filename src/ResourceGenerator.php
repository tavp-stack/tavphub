<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * Resource generator — auto-generate CRUD resources from content type definitions.
 *
 * Bridges tavp-cms content types with tavphub's admin panel. Reads the
 * BREAD content type schema and produces Resource + FormBuilder configs.
 */
class ResourceGenerator
{
    /**
     * Generate a Resource from a content type definition.
     *
     * @param array<string,mixed> $contentType e.g. from config('cms.content_types.page')
     */
    public static function fromContentType(string $name, array $contentType): Resource
    {
        return new class($name, $contentType) extends Resource
        {
            private string $name;
            private array $contentType;

            public function __construct(string $name, array $contentType)
            {
                $this->name = $name;
                $this->contentType = $contentType;
            }

            public function label(): string
            {
                return $this->contentType['label'] ?? ucfirst($this->name) . 's';
            }

            public function model(): string
            {
                return 'Tavp\\Cms\\Content\\' . ucfirst($this->name);
            }

            public function columns(): array
            {
                $columns = [];
                foreach ($this->contentType['fields'] ?? [] as $field) {
                    $columns[] = [
                        'key' => $field['name'],
                        'label' => ucwords(str_replace(['_', '-'], ' ', $field['name'])),
                        'sortable' => !in_array($field['type'] ?? '', ['richtext', 'blocks', 'json']),
                    ];
                }
                return $columns;
            }

            public function fields(): array
            {
                $fields = [];
                foreach ($this->contentType['fields'] ?? [] as $field) {
                    $fields[] = array_merge($field, [
                        'label' => ucwords(str_replace(['_', '-'], ' ', $field['name'])),
                    ]);
                }
                return $fields;
            }

            public function inSidebar(): bool
            {
                return true;
            }
        };
    }

    /**
     * Generate a Taxonomy Resource for the admin panel.
     */
    public static function taxonomyResource(string $termType): Resource
    {
        return new class($termType) extends Resource
        {
            private string $termType;

            public function __construct(string $termType)
            {
                $this->termType = $termType;
            }

            public function label(): string
            {
                return ucfirst($this->termType) . 's';
            }

            public function model(): string
            {
                return 'Tavp\\Cms\\Taxonomy\\Term';
            }

            public function columns(): array
            {
                return [
                    ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                    ['key' => 'slug', 'label' => 'Slug', 'sortable' => true],
                    ['key' => 'description', 'label' => 'Description', 'sortable' => false],
                    ['key' => 'sort', 'label' => 'Sort', 'sortable' => true],
                ];
            }

            public function fields(): array
            {
                return [
                    ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'required' => true],
                    ['name' => 'slug', 'type' => 'text', 'label' => 'Slug'],
                    ['name' => 'description', 'type' => 'textarea', 'label' => 'Description'],
                    ['name' => 'sort', 'type' => 'number', 'label' => 'Sort Order', 'value' => 0],
                    ['name' => 'parent_id', 'type' => 'number', 'label' => 'Parent ID', 'value' => 0],
                ];
            }

            public function inSidebar(): bool
            {
                return true;
            }
        };
    }

    /**
     * Generate a Revision Resource (read-only, for viewing history).
     */
    public static function revisionResource(): Resource
    {
        return new class() extends Resource
        {
            public function label(): string
            {
                return 'Revisions';
            }

            public function model(): string
            {
                return 'Tavp\\Cms\\Revisions\\RevisionStore';
            }

            public function columns(): array
            {
                return [
                    ['key' => 'created_at', 'label' => 'Date', 'sortable' => true],
                    ['key' => 'author', 'label' => 'Author', 'sortable' => true],
                    ['key' => 'note', 'label' => 'Note', 'sortable' => false],
                    ['key' => 'content_type', 'label' => 'Type', 'sortable' => true],
                    ['key' => 'content_id', 'label' => 'Record', 'sortable' => true],
                ];
            }

            public function fields(): array
            {
                return [];
            }

            public function inSidebar(): bool
            {
                return false; // accessed from content record, not sidebar
            }
        };
    }
}
