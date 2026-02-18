<?php
/*
 * Copyright (c) 2026.
 * Created by oleksandr
 * Contacts: polishchchyk.a.v@gmail.com
 * All rights reserved.
 */

declare(strict_types=1);

namespace FakestoreProducts\Admin;

use FakestoreProducts\Core\Options;
use FakestoreProducts\Core\View;
use FakestoreProducts\PostType\ProductCPT;

final class SettingsPage
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
        add_action('admin_menu', function () {
            add_options_page(
                __('FakeStore Products', 'fakestore-products'),
                __('FakeStore Products', 'fakestore-products'),
                'manage_options',
                'fakestore-products',
                [$this, 'render']
            );
        });

        add_action('admin_init', function () {
            register_setting('fakestore_products_group', Options::OPTION_KEY, [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitizeOptions'],
                'default' => [],
            ]);

            add_settings_section(
                'fakestore_products_main',
                __('Main Settings', 'fakestore-products'),
                function () {
                    echo '<p class="description">' . esc_html__('Configure product ID and mode.', 'fakestore-products') . '</p>';
                },
                'fakestore-products'
            );

            add_settings_field(
                'product_id',
                __('Product ID', 'fakestore-products'),
                [$this, 'fieldProductId'],
                'fakestore-products',
                'fakestore_products_main'
            );

            add_settings_field(
                'mode',
                __('Mode', 'fakestore-products'),
                [$this, 'fieldMode'],
                'fakestore-products',
                'fakestore_products_main'
            );

            add_settings_field(
                'notify_email',
                __('Notify Email (optional)', 'fakestore-products'),
                [$this, 'fieldNotifyEmail'],
                'fakestore-products',
                'fakestore_products_main'
            );
        });
    }

    /**
     * @param $input
     *
     * @return array
     */
    public function sanitizeOptions($input): array
    {
        $out = [];

        $out['product_id'] = isset($input['product_id']) ? max(1, (int)$input['product_id']) : 1;

        $mode = isset($input['mode']) ? (string)$input['mode'] : 'simple';
        $out['mode'] = in_array($mode, ['simple', 'async'], true) ? $mode : 'simple';

        $email = isset($input['notify_email']) ? sanitize_email((string)$input['notify_email']) : '';
        $out['notify_email'] = is_email($email) ? $email : '';

        return $out;
    }

    /**
     * @return void
     */
    public function fieldProductId(): void
    {
        $opts = Options::getAll();
        $val = isset($opts['product_id']) ? (int)$opts['product_id'] : 1;

        echo '<input type="number" class="regular-text" min="1" max="20" name="' . esc_attr(Options::OPTION_KEY) . '[product_id]" value="' . esc_attr((string)$val) . '" />';
        echo '<p class="description">' . esc_html__('1..20 (FakeStore API demo has 20 products).', 'fakestore-products') . '</p>';
    }

    /**
     * @return void
     */
    public function fieldMode(): void
    {
        $mode = Options::getString('mode', 'simple');

        echo '<fieldset>';
        echo '<label><input type="radio" name="' . esc_attr(Options::OPTION_KEY) . '[mode]" value="simple" ' . checked('simple', $mode, false) . ' /> ' . esc_html__('Simple (save immediately)', 'fakestore-products') . '</label><br />';
        echo '<label><input type="radio" name="' . esc_attr(Options::OPTION_KEY) . '[mode]" value="async" ' . checked('async', $mode, false) . ' /> ' . esc_html__('Async (queue + cron/CLI + email)', 'fakestore-products') . '</label>';
        echo '</fieldset>';
    }

    /**
     * @return void
     */
    public function fieldNotifyEmail(): void
    {
        $email = Options::getString('notify_email', '');

        echo '<input type="email" class="regular-text" name="' . esc_attr(Options::OPTION_KEY) . '[notify_email]" value="' . esc_attr($email) . '" />';
        echo '<p class="description">' . esc_html__('Used only for async mode notification.', 'fakestore-products') . '</p>';
    }

    /**
     * @return void
     */
    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $last = (string)get_option(Options::LAST_CREATED_AT, '');

        $pageId = (int) get_option(Options::PAGE_ID, 0);
        $pageUrl = ($pageId > 0 && get_post($pageId)) ? get_permalink($pageId) : '';

        $archiveUrl = get_post_type_archive_link(ProductCPT::POST_TYPE);
        $archiveUrl = is_string($archiveUrl) ? $archiveUrl : '';

        echo $this->view->render('admin/settings.php', [
            'lastCreatedAt' => $last,
            'pageUrl'       => $pageUrl,
            'archiveUrl'    => $archiveUrl,
        ]);
    }
}
