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
use FakestoreProducts\PostType\ProductCPT;
use JsonException;

final class Ajax
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
     * @var ProductCPT
     */
    private ProductCPT $cpt;

    /**
     * @param ApiClient $api
     * @param View $view
     * @param ProductCPT $cpt
     */
    public function __construct(ApiClient $api, View $view, ProductCPT $cpt)
    {
        $this->api = $api;
        $this->view = $view;
        $this->cpt = $cpt;
    }

    /**
     * @return void
     */
    public function register(): void
    {
        add_action('wp_ajax_fakestore_random_product', [$this, 'handleRandom']);
        add_action('wp_ajax_nopriv_fakestore_random_product', [$this, 'handleRandom']);
        add_action('fakestore_products_process_queue', [$this, 'processQueue']);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function handleRandom(): void
    {
        check_ajax_referer('fakestore_random_nonce', 'nonce');

        $mode = Options::getString('mode', 'simple');

        $product = $this->api->getRandomProduct();
        if (empty($product)) {
            wp_send_json_error(['message' => __('API error', 'fakestore-products')], 500);
        }

        if ($mode === 'simple') {
            $postId = $this->cpt->createFromApi($product);
            if ($postId > 0) {
                update_option(Options::LAST_CREATED_AT, current_time('mysql'));
            }

            $html = $this->view->render('front/product-card.php', [
                'product' => $product,
                'permalink' => $postId > 0 ? get_permalink($postId) : '',
                'extraNote' => $postId > 0 ? '' : __('Could not create CPT post.', 'fakestore-products'),
            ]);

            wp_send_json_success(['html' => $html]);
        }

        $queue = get_option(Options::QUEUE, []);
        if (!is_array($queue)) {
            $queue = [];
        }
        $queue[] = [
            'product' => $product,
            'queued_at' => current_time('mysql'),
        ];
        update_option(Options::QUEUE, $queue, false);

        if (!wp_next_scheduled('fakestore_products_process_queue')) {
            wp_schedule_single_event(time() + 30, 'fakestore_products_process_queue');
        }

        wp_remote_post(site_url('/wp-cron.php?doing_wp_cron=' . microtime(true)), [
            'timeout'   => 0.01,
            'blocking'  => false,
            'sslverify' => false,
        ]);

        $html = $this->view->render('front/product-card.php', [
            'product' => $product,
            'permalink' => '',
            'extraNote' => __('Saved will be created in background. You may get an email when done (if configured).', 'fakestore-products'),
        ]);

        wp_send_json_success(['html' => $html]);
    }

    /**
     * @return void
     */
    public function processQueue(): void
    {
        $queue = get_option(Options::QUEUE, []);
        if (!is_array($queue) || empty($queue)) {
            return;
        }

        $item = array_shift($queue);
        update_option(Options::QUEUE, $queue, false);

        $product = $item['product'] ?? [];
        if (!is_array($product) || empty($product)) {
            return;
        }

        $postId = $this->cpt->createFromApi($product);
        if ($postId > 0) {
            update_option(Options::LAST_CREATED_AT, current_time('mysql'));

            $email = Options::getString('notify_email', '');
            if (!empty($email) && is_email($email)) {
                $subject = __('FakeStore product saved', 'fakestore-products');
                $message = sprintf(
                    "Saved post: %s\n",
                    get_permalink($postId)
                );
                wp_mail($email, $subject, $message);
            }
        }

        $queue = get_option(Options::QUEUE, []);
        if (is_array($queue) && !empty($queue)) {
            wp_schedule_single_event(time() + 30, 'fakestore_products_process_queue');

            wp_remote_post(site_url('/wp-cron.php?doing_wp_cron=' . microtime(true)), [
                'timeout'   => 0.01,
                'blocking'  => false,
                'sslverify' => false,
            ]);
        }
    }
}