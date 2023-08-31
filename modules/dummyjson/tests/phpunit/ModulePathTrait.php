<?php

namespace DigitalSilk\DummyJson\Test;

trait ModulePathTrait
{
    /**
     * Retrieves the path to the current module.
     *
     * @return string The path to the module's base directory.
     */
    protected function getModulePath(): string
    {
        $dir = __DIR__;
        $path = "$dir/../../";

        return $path;
    }
}
