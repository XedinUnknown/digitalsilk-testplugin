<?php

declare(strict_types=1);

use DigitalSilk\DummyJson\DummyJsonModule;
use DigitalSilk\WcImport\WcImportModule;

return function (string $rootDir, string $mainFile): iterable {
    $modules = [
        new DummyJsonModule(),
        new WcImportModule(),
    ];

    return $modules;
};
