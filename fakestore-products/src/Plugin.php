<?php
/*
 * Copyright (c) 2026.
 * Created by oleksandr
 * Contacts: polishchchyk.a.v@gmail.com
 * All rights reserved.
 */

declare(strict_types=1);

namespace FakestoreProducts;

use FakestoreProducts\Admin\SettingsPage;
use FakestoreProducts\Cli\Commands;
use FakestoreProducts\Core\ApiClient;
use FakestoreProducts\Core\Options;
use FakestoreProducts\Core\View;
use FakestoreProducts\Front\Ajax;
use FakestoreProducts\Front\Shortcodes;
use FakestoreProducts\PostType\ProductCPT;
use FakestoreProducts\PostType\ProductMeta;

final class Plugin
{
    /**
     * @var View
     */
    private View $view;

    /**
     * @var ApiClient
     */
    private ApiClient $api;

    /**
     * @var ProductCPT
     */
    private ProductCPT $cpt;

    public function __construct()
    {
        $this->view = new View();
        $this->api = new ApiClient();
        $this->cpt = new ProductCPT();
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->cpt->register();

        (new ProductMeta($this->view))->register();
        (new SettingsPage($this->view))->register();
        (new Shortcodes($this->api, $this->view))->register();
        (new Ajax($this->api, $this->view, $this->cpt))->register();

        add_action('wp_enqueue_scripts', function () {
            wp_register_script(
                'fakestore-products-front',
                FAKESTORE_PRODUCTS_URL . 'assets/js/front.js',
                ['jquery'],
                FAKESTORE_PRODUCTS_VERSION,
                true
            );

            wp_register_style(
                'fakestore-products-front',
                FAKESTORE_PRODUCTS_URL . 'assets/css/front.css',
                [],
                FAKESTORE_PRODUCTS_VERSION
            );
        });

        if (defined('WP_CLI') && WP_CLI) {
            (new Commands())->register();
        }
    }

    /**
     * @return void
     */
    public function activate(): void
    {
        $this->createDemoPage();
        flush_rewrite_rules();
    }

    /**
     * @return void
     */
    public function deactivate(): void
    {
        flush_rewrite_rules();
    }

    /**
     * @return void
     */
    private function createDemoPage(): void
    {
        $existing = (int)get_option(Options::PAGE_ID, 0);
        if ($existing > 0 && get_post($existing)) {
            return;
        }

        $pageId = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => 'Fakestore Demo',
            'post_content' => "[fakestore_product]\n\n[fakestore_random]",
        ], true);

        if (!is_wp_error($pageId) && $pageId) {
            update_option(Options::PAGE_ID, (int)$pageId);
        }
    }
}