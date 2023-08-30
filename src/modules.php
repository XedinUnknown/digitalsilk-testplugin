<?php

declare(strict_types=1);

use DigitalSilk\DummyJson\DummyJsonModule;

return function (string $rootDir, string $mainFile): iterable {
    $modules = [
        new DummyJsonModule(),
    ];

    return $modules;
};
