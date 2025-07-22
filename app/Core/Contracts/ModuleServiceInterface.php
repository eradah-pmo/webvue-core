<?php

namespace App\Core\Contracts;

use Illuminate\Support\Collection;

interface ModuleServiceInterface
{
    /**
     * Get all available modules
     */
    public function getAllModules(): Collection;

    /**
     * Get active modules only
     */
    public function getActiveModules(): Collection;

    /**
     * Get module by name
     */
    public function getModule(string $name): ?array;

    /**
     * Enable a module
     */
    public function enableModule(string $name): bool;

    /**
     * Disable a module
     */
    public function disableModule(string $name): bool;

    /**
     * Check if module dependencies are met
     */
    public function checkDependencies(array $module): bool;

    /**
     * Get modules that depend on the given module
     */
    public function getDependentModules(string $moduleName): Collection;

    /**
     * Get module navigation items
     */
    public function getModuleNavigation(): Collection;

    /**
     * Clear module cache
     */
    public function clearCache(): void;
}
