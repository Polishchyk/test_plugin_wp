<?php
/*
 * Copyright (c) 2026.
 * Created by oleksandr
 * Contacts: polishchchyk.a.v@gmail.com
 * All rights reserved.
 */

declare(strict_types=1);

namespace FakestoreProducts\Core;

final class Autoloader
{
    /**
     * @var array
     */
    private array $prefixes = [];

    /**
     * @param string $prefix
     * @param string $baseDir
     *
     * @return void
     */
    public function addNamespace(string $prefix, string $baseDir): void
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->prefixes[$prefix] = $baseDir;
    }

    /**
     * @return void
     */
    public function register(): void
    {
        spl_autoload_register([$this, 'autoload']);
    }

    /**
     * @param string $class
     *
     * @return void
     */
    private function autoload(string $class): void
    {
        foreach ($this->prefixes as $prefix => $baseDir) {
            if (!str_starts_with($class, $prefix)) {
                continue;
            }

            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';

            if (is_readable($file)) {
                require $file;
            }
        }
    }
}