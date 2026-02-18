<?php
/*
 * Copyright (c) 2026.
 * Created by oleksandr
 * Contacts: polishchchyk.a.v@gmail.com
 * All rights reserved.
 */

declare(strict_types=1);

namespace FakestoreProducts\Front;

use FakestoreProducts\Core\ApiClient;
use FakestoreProducts\Core\Options;
use FakestoreProducts\Core\View;
use JsonException;

final class Shortcodes
{
    /**
     * @var ApiClient
     */
    private ApiClient $api;

    /**
     * @var View
     */
    private View $view;

    /**
     * @param ApiClient $api
     * @param View $view
     */
    public function __construct(ApiClient $api, View $view)
    {
        $this->api = $api;
        $this->view = $view;
    }

    /**
     * @return void
     */
    public function register(): void
    {
        add_shortcode('fakestore_product', [$this, 'shortcodeProduct']);
        add_shortcode('fakestore_random', [$this, 'shortcodeRandom']);
    }

    /**
     * @return string
     * @throws JsonException
     */
    public function shortcodeProduct(): string
    {
        $id = Options::getInt('product_id', 1);
        $product = $this->api->getProduct($id);

        if (empty($product)) {
            return '<div class="notice notice-warning"><p>' . esc_html__('Product not found / API error.', 'fakestore-products') . '</p></div>';
        }

        return $this->view->render('front/product-card.php', [
            'product' => $product,
            'permalink' => '',
            'extraNote' => '',
        ]);
    }

    /**
     * @return string
     */
    public function shortcodeRandom(): string
    {
        if (!wp_script_is('fakestore-products-front', 'registered')) {
            wp_register_script(
                'fakestore-products-front',
                FAKESTORE_PRODUCTS_URL . 'assets/js/front.js',
                ['jquery'],
                FAKESTORE_PRODUCTS_VERSION,
                true
            );
            wp_enqueue_script('fakestore-products-front');
        }

        wp_localize_script('fakestore-products-front', 'FakestoreProducts', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fakestore_random_nonce'),
        ]);

        return '<div class="fakestore-products-random">
            <button type="button" class="button button-primary js-fakestore-random">' . esc_html__('Get random product', 'fakestore-products') . '</button>
            <div class="js-fakestore-result" style="margin-top:12px;"></div>
        </div>';
    }
}