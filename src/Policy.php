<?php

declare(strict_types=1);

namespace Tavp\Hub;

/**
 * Base class for TAVPhub authorization policies (nova-style "Policies").
 *
 * Receives the current user (from tavpid session, may be null for
 * guests) and the target model instance where relevant. Return `false`
 * to deny, `true` to allow. `before()` can short-circuit everything.
 */
abstract class Policy
{
    /**
     * Short-circuit. Return true/false to allow/deny everything,
     * or null to fall through to the specific methods.
     */
    public function before(mixed $user): ?bool
    {
        return null;
    }

    public function viewAny(mixed $user): bool { return true; }

    public function view(mixed $user, mixed $model): bool { return true; }

    public function create(mixed $user): bool { return true; }

    public function update(mixed $user, mixed $model): bool { return true; }

    public function delete(mixed $user, mixed $model): bool { return true; }

    public function restore(mixed $user, mixed $model): bool { return true; }

    public function forceDelete(mixed $user, mixed $model): bool { return true; }
}
