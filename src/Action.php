<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * Base class for TAVPhub resource actions (Nova-style "Actions").
 *
 * Actions run against a set of selected record ids. `handle()` receives
 * the selected ids and the resource's model class. Use `only()` to
 * restrict an action to specific resource keys.
 */
abstract class Action
{
    public string $name;
    public string $label;
    public bool $confirm = true;
    public string $buttonClass = 'secondary';
    public array $only = [];

    public function __construct(string $name, ?string $label = null)
    {
        $this->name = $name;
        $this->label = $label ?? ucwords(str_replace(['_', '-'], ' ', $name));
    }

    public function confirm(bool $confirm): static
    {
        $this->confirm = $confirm;

        return $this;
    }

    public function only(array $resourceKeys): static
    {
        $this->only = $resourceKeys;

        return $this;
    }

    /**
     * Run the action against the selected record ids.
     *
     * @param array<int|string> $ids
     */
    abstract public function handle(array $ids, string $modelClass): void;

    public function appliesTo(string $resourceKey): bool
    {
        return $this->only === [] || in_array($resourceKey, $this->only, true);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'confirm' => $this->confirm,
            'button_class' => $this->buttonClass,
        ];
    }
}
