<?php
/*
 * Copyright (c) 2026.
 * Created by oleksandr
 * Contacts: polishchchyk.a.v@gmail.com
 * All rights reserved.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$optsKey = 'fakestore_products_options';
$lastKey = 'fakestore_products_last_created_at';
$pageIdKey = 'fakestore_products_page_id';
$queueKey = 'fakestore_products_queue';

$postType = 'fakestore_product';
$taxonomy = 'fakestore_category';
$termMarker = '_fakestore_created_term';

$posts = get_posts([
    'post_type' => $postType,
    'post_status' => 'any',
    'numberposts' => -1,
    'fields' => 'ids',
    'no_found_rows' => true,
    'suppress_filters' => true,
]);

if (is_array($posts)) {
    foreach ($posts as $id) {
        wp_delete_post((int)$id, true);
    }
}

$pageId = (int)get_option($pageIdKey, 0);
if ($pageId > 0 && get_post($pageId)) {
    wp_delete_post($pageId, true);
}

$terms = get_terms([
    'taxonomy' => $taxonomy,
    'hide_empty' => false,
]);

if (!is_wp_error($terms) && is_array($terms)) {
    foreach ($terms as $term) {
        $flag = (int)get_term_meta((int)$term->term_id, $termMarker, true);
        if ($flag === 1) {
            wp_delete_term((int)$term->term_id, $taxonomy);
        }
    }
}

delete_option($optsKey);
delete_option($lastKey);
delete_option($pageIdKey);
delete_option($queueKey);