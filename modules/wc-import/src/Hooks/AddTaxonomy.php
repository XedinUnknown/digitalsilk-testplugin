<?php

declare(strict_types=1);

namespace DigitalSilk\WcImport\Hooks;

/**
 * Adds a single taxonomy.
 */
class AddTaxonomy
{
    protected string $name;
    protected array $args;
    protected string $objectType;

    /**
     * @param array<mixed> $args
     */
    public function __construct(string $name, array $args, string $objectType)
    {
        $this->name = $name;
        $this->args = $args;
        $this->objectType = $objectType;
    }

    public function __invoke(): void
    {
        register_taxonomy($this->name, $this->objectType, $this->args);
        register_taxonomy_for_object_type($this->name, $this->objectType);
    }
}
