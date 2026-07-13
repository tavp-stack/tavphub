<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * Describes a relationship field on a resource.
 *
 * - belongsTo: rendered as a select populated from the related
 *   resource's model (value = foreign key id, label = labelColumn).
 * - hasMany: rendered as a read-only list of related records on
 *   the edit screen.
 */
class Relation
{
    public function __construct(
        public string $name,
        public string $type,          // 'belongsTo' | 'hasMany'
        public string $relatedResource, // resource key
        public ?string $labelColumn = 'name',
        public ?string $foreignKey = null,
        public ?string $localKey = 'id',
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'related_resource' => $this->relatedResource,
            'label_column' => $this->labelColumn,
            'foreign_key' => $this->foreignKey,
            'local_key' => $this->localKey,
        ];
    }
}
