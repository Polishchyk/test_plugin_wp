<?php
/*
 * Copyright (c) 2026.
 * Created by oleksandr
 * Contacts: polishchchyk.a.v@gmail.com
 * All rights reserved.
 */

declare(strict_types=1);

namespace FakestoreProducts\PostType;

final class ProductCPT
{
    public const POST_TYPE = 'fakestore_product';
    public const TAXONOMY = 'fakestore_category';
    private const TERM_META_MARKER = '_fakestore_created_term';

    /**
     * @return void
     */
    public function register(): void
    {
        add_action('init', function () {
            register_post_type(self::POST_TYPE, [
                'labels' => [
                    'name' => __('FakeStore Products', 'fakestore-products'),
                    'singular_name' => __('FakeStore Product', 'fakestore-products'),
                ],
                'public' => true,
                'show_in_menu' => true,
                'menu_icon' => 'dashicons-cart',
                'supports' => ['title', 'editor', 'thumbnail'],
                'has_archive' => true,
                'rewrite' => ['slug' => 'fakestore-products'],
                'show_in_rest' => true,
                'taxonomies' => [self::TAXONOMY],
            ]);

            register_taxonomy(self::TAXONOMY, [self::POST_TYPE], [
                'labels' => [
                    'name' => __('Product Categories', 'fakestore-products'),
                    'singular_name' => __('Product Category', 'fakestore-products'),
                ],
                'public' => true,
                'hierarchical' => true,
                'show_admin_column' => true,
                'show_in_rest' => true,
                'rewrite' => ['slug' => 'fakestore-category'],
            ]);
        });
    }

    /**
     * @param array $product
     *
     * @return int
     */
    public function createFromApi(array $product): int
    {
        $apiId = isset($product['id']) ? (int)$product['id'] : 0;
        if ($apiId < 1) {
            return 0;
        }

        $title = isset($product['title']) ? (string)$product['title'] : ('Product #' . $apiId);
        $content = isset($product['description']) ? (string)$product['description'] : '';

        $existingId = $this->findPostIdByApiId($apiId);

        if ($existingId > 0) {
            wp_update_post([
                'ID'           => $existingId,
                'post_title'   => wp_strip_all_tags($title),
                'post_content' => $content,
            ]);

            $postId = $existingId;
        } else {
            $postId = wp_insert_post([
                'post_type'    => self::POST_TYPE,
                'post_status'  => 'publish',
                'post_title'   => wp_strip_all_tags($title),
                'post_content' => $content,
            ], true);

            if (is_wp_error($postId)) {
                return 0;
            }

            $postId = (int)$postId;
            update_post_meta($postId, '_api_id', $apiId);
        }

        update_post_meta(
            (int)$postId, '_price',
            isset($product['price']) ? (float)$product['price'] : 0.0
        );
        update_post_meta(
            (int)$postId,
            '_category',
            isset($product['category']) ? (string)$product['category'] : ''
        );
        update_post_meta(
            (int)$postId,
            '_image',
            isset($product['image']) ? (string)$product['image'] : ''
        );

        if (isset($product['rating']) && is_array($product['rating'])) {
            update_post_meta(
                (int)$postId,
                '_rating_rate',
                isset($product['rating']['rate']) ? (float)$product['rating']['rate'] : 0.0
            );
            update_post_meta(
                (int)$postId,
                '_rating_count',
                isset($product['rating']['count']) ? (int)$product['rating']['count'] : 0
            );
        }

        $catName = isset($product['category']) ? trim((string)$product['category']) : '';
        if ($catName !== '') {
            $this->assignCategoryByName((int)$postId, $catName);
        }

        return (int)$postId;
    }

    /**
     * @param int $postId
     * @param string $name
     *
     * @return void
     */
    private function assignCategoryByName(int $postId, string $name): void
    {
        $existing = term_exists($name, self::TAXONOMY);

        if (is_array($existing) && !empty($existing['term_id'])) {
            wp_set_object_terms($postId, [(int)$existing['term_id']], self::TAXONOMY, false);

            return;
        }

        if (is_int($existing) && $existing > 0) {
            wp_set_object_terms($postId, [$existing], self::TAXONOMY, false);

            return;
        }

        $created = wp_insert_term($name, self::TAXONOMY);
        if (is_wp_error($created) || empty($created['term_id'])) {
            return;
        }

        $termId = (int)$created['term_id'];

        update_term_meta($termId, self::TERM_META_MARKER, 1);

        wp_set_object_terms($postId, [$termId], self::TAXONOMY, false);
    }

    /**
     * @param int $apiId
     *
     * @return int
     */
    private function findPostIdByApiId(int $apiId): int
    {
        $ids = get_posts([
            'post_type' => self::POST_TYPE,
            'post_status' => 'any',
            'numberposts' => 1,
            'fields' => 'ids',
            'no_found_rows' => true,
            'meta_query' => [
                [
                    'key' => '_api_id',
                    'value' => $apiId,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ],
            ],
        ]);

        return (!empty($ids) && is_array($ids)) ? (int)$ids[0] : 0;
    }
}
