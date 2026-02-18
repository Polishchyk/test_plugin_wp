<?php
/*
 * Copyright (c) 2026.
 * Created by oleksandr
 * Contacts: polishchchyk.a.v@gmail.com
 * All rights reserved.
 */

declare(strict_types=1);

namespace FakestoreProducts\PostType;

use FakestoreProducts\Core\View;
use WP_Post;

final class ProductMeta
{
    /**
     * @var View
     */
    private View $view;

    /**
     * @param View $view
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * @return void
     */
    public function register(): void
    {
        add_action('init', [$this, 'registerMeta']);
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post_' . ProductCPT::POST_TYPE, [$this, 'saveMeta'], 10, 2);
    }

    /**
     * @return void
     */
    public function registerMeta(): void
    {
        $keys = [
            '_api_id' => ['type' => 'integer'],
            '_price' => ['type' => 'number'],
            '_category' => ['type' => 'string'],
            '_image' => ['type' => 'string'],
            '_rating_rate' => ['type' => 'number'],
            '_rating_count' => ['type' => 'integer'],
        ];

        foreach ($keys as $key => $schema) {
            register_post_meta(ProductCPT::POST_TYPE, $key, [
                'type' => $schema['type'],
                'single' => true,
                'show_in_rest' => true,
                'auth_callback' => static function () {
                    return current_user_can('edit_posts');
                },
                'sanitize_callback' => static function ($value) use ($schema) {
                    return match ($schema['type']) {
                        'integer' => (int)$value,
                        'number' => (float)$value,
                        default => sanitize_text_field((string)$value),
                    };
                },
            ]);
        }
    }

    /**
     * @return void
     */
    public function addMetaBoxes(): void
    {
        add_meta_box(
            'fakestore_product_meta',
            __('FakeStore Product Data', 'fakestore-products'),
            [$this, 'renderMetaBox'],
            ProductCPT::POST_TYPE,
            'normal',
            'default'
        );
    }

    /**
     * @param WP_Post $post
     *
     * @return void
     */
    public function renderMetaBox(WP_Post $post): void
    {
        wp_nonce_field('fakestore_product_meta_save', 'fakestore_product_meta_nonce');

        $data = [
            'api_id' => (int)get_post_meta($post->ID, '_api_id', true),
            'price' => (string)get_post_meta($post->ID, '_price', true),
            'category' => (string)get_post_meta($post->ID, '_category', true),
            'image' => (string)get_post_meta($post->ID, '_image', true),
            'rating_rate' => (string)get_post_meta($post->ID, '_rating_rate', true),
            'rating_count' => (string)get_post_meta($post->ID, '_rating_count', true),
        ];

        echo $this->view->render('admin/metabox-product.php', $data);
    }

    /**
     * @param int $postId
     * @param WP_Post $post
     *
     * @return void
     */
    public function saveMeta(int $postId, WP_Post $post): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        $nonce = $_POST['fakestore_product_meta_nonce'] ?? '';
        if (!is_string($nonce) || !wp_verify_nonce($nonce, 'fakestore_product_meta_save')) {
            return;
        }

        if (isset($_POST['_price'])) {
            update_post_meta($postId, '_price', (float)$_POST['_price']);
        }

        if (isset($_POST['_category'])) {
            $cat = sanitize_text_field((string)$_POST['_category']);
            update_post_meta($postId, '_category', $cat);

            if ($cat !== '') {
                $existing = term_exists($cat, ProductCPT::TAXONOMY);
                if (!is_wp_error($existing) && $existing) {
                    $termId = is_array($existing) ? (int)$existing['term_id'] : (int)$existing;
                } else {
                    $created = wp_insert_term($cat, ProductCPT::TAXONOMY);
                    $termId = (!is_wp_error($created) && !empty($created['term_id'])) ? (int)$created['term_id'] : 0;
                }
                if ($termId > 0) {
                    wp_set_object_terms($postId, [$termId], ProductCPT::TAXONOMY, false);
                }
            }
        }

        if (isset($_POST['_image'])) {
            update_post_meta($postId, '_image', esc_url_raw((string)$_POST['_image']));
        }

        if (isset($_POST['_rating_rate'])) {
            update_post_meta($postId, '_rating_rate', (float)$_POST['_rating_rate']);
        }

        if (isset($_POST['_rating_count'])) {
            update_post_meta($postId, '_rating_count', (int)$_POST['_rating_count']);
        }
    }
}
