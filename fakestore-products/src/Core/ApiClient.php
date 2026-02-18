<?php
/*
 * Copyright (c) 2026.
 * Created by oleksandr
 * Contacts: polishchchyk.a.v@gmail.com
 * All rights reserved.
 */

declare(strict_types=1);

namespace FakestoreProducts\Core;

use JsonException;

final class ApiClient
{
    private const BASE_URL = 'https://fakestoreapi.com/products/';

    /**
     * @param int $id
     *
     * @return array
     * @throws JsonException
     */
    public function getProduct(int $id): array
    {
        if ($id < 1) {
            return [];
        }

        return $this->request(self::BASE_URL . $id);
    }

    /**
     * @return array
     * @throws JsonException
     */
    public function getRandomProduct(): array
    {
        $id = random_int(1, 20);

        return $this->getProduct($id);
    }

    /**
     * @throws JsonException
     */
    private function request(string $url): array
    {
        $resp = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($resp)) {
            return [];
        }

        $code = (int)wp_remote_retrieve_response_code($resp);
        if ($code < 200 || $code >= 300) {
            return [];
        }

        $body = (string)wp_remote_retrieve_body($resp);
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        return is_array($data) ? $data : [];
    }
}