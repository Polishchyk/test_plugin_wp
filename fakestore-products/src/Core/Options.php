<?php
/*
 * Copyright (c) 2026.
 * Created by oleksandr
 * Contacts: polishchchyk.a.v@gmail.com
 * All rights reserved.
 */

declare(strict_types=1);

namespace FakestoreProducts\Core;

final class Options
{
    public const OPTION_KEY = 'fakestore_products_options';
    public const LAST_CREATED_AT = 'fakestore_products_last_created_at';
    public const PAGE_ID = 'fakestore_products_page_id';
    public const QUEUE = 'fakestore_products_queue';

    /**
     * @return array
     */
    public static function getAll(): array
    {
        $opts = get_option(self::OPTION_KEY, []);

        return is_array($opts) ? $opts : [];
    }

    /**
     * @param string $key
     * @param int $default
     *
     * @return int
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $all = self::getAll();

        return isset($all[$key]) ? max(0, (int)$all[$key]) : $default;
    }

    /**
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public static function getString(string $key, string $default = ''): string
    {
        $all = self::getAll();

        return isset($all[$key]) ? (string)$all[$key] : $default;
    }
}