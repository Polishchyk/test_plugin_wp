<?php
/*
 * Copyright (c) 2026.
 * Created by oleksandr
 * Contacts: polishchchyk.a.v@gmail.com
 * All rights reserved.
 */

declare(strict_types=1);

namespace FakestoreProducts\Core;

final class View
{
    /**
     * @param string $templateRelativePath
     * @param array $data
     *
     * @return string
     */
    public function render(string $templateRelativePath, array $data = []): string
    {
        $file = FAKESTORE_PRODUCTS_PATH . 'templates/' . ltrim($templateRelativePath, '/');

        if (!is_readable($file)) {
            return '';
        }

        ob_start();
        extract($data, EXTR_SKIP);
        include $file;

        return (string)ob_get_clean();
    }
}