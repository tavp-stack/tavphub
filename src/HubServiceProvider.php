<?php

declare(strict_types=1);

namespace Tavp\Hub;

use Tavp\Core\Module\ServiceProvider;

/**
 * Service provider for TAVPhub admin panel.
 *
 * Registers resource registry, wires CMS content types,
 * and provides the admin controller.
 */
class HubServiceProvider implements ServiceProvider
{
    private array $resources = [];

    public function register(): void
    {
        $app = app();

        // --- Resource Registry -----------------------------------------------
        $app->bind('hub.resources', function () {
            $this->registerCmsResources();
            return $this->resources;
        });

        // --- Hub Controller --------------------------------------------------
        $app->bind(HubController::class, fn () => new HubController(
            resources: $app->getService('hub.resources'),
        ));
    }

    public function boot(): void
    {
        $this->registerCmsResources();
    }

    public function loadRoutes(): void
    {
        if (isset($router)) {
            HubController::routes($router);
        }
    }

    public function loadMigrations(): void {}

    public function loadViews(): void {}

    /**
     * Register CMS content types as Hub resources.
     */
    private function registerCmsResources(): void
    {
        $contentTypes = (array) config('cms.content_types', []);

        foreach ($contentTypes as $name => $contentType) {
            $this->resources[$name] = ResourceGenerator::fromContentType($name, $contentType);
        }

        // Taxonomy resources
        $taxonomyTypes = (array) config('cms.taxonomy.types', ['category', 'tag']);
        foreach ($taxonomyTypes as $type) {
            $this->resources['taxonomy_' . $type] = ResourceGenerator::taxonomyResource($type);
        }
    }

    /**
     * Get all registered resources.
     *
     * @return Resource[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * Get a resource by URI key.
     */
    public function getResource(string $key): ?Resource
    {
        return $this->resources[$key] ?? null;
    }

    /**
     * Get resources for sidebar display.
     *
     * @return Resource[]
     */
    public function getSidebarResources(): array
    {
        return array_filter($this->resources, fn (Resource $r) => $r->inSidebar());
    }
}
