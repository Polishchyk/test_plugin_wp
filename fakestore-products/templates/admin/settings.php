<?php
/*
 * Copyright (c) 2026.
 * Created by oleksandr
 * Contacts: polishchchyk.a.v@gmail.com
 * All rights reserved.
 */

if (!defined('ABSPATH')) {
  exit;
}
?>

<div class="wrap">
  <h1><?php echo esc_html__('FakeStore Products Settings', 'fakestore-products'); ?></h1>

  <?php if (!empty($lastCreatedAt)) : ?>
    <div class="notice notice-success inline">
      <p>
        <?php echo esc_html__('Last successful product saved at:', 'fakestore-products'); ?>
        <strong><?php echo esc_html($lastCreatedAt); ?></strong>
      </p>
    </div>
  <?php else: ?>
    <div class="notice notice-info inline">
      <p><?php echo esc_html__('No saved products yet.', 'fakestore-products'); ?></p>
    </div>
  <?php endif; ?>

  <?php if (!empty($pageUrl)) : ?>
    <div class="notice notice-info inline">
      <p>
        <?php echo esc_html__('Frontend demo page:', 'fakestore-products'); ?>
        <a href="<?php echo esc_url($pageUrl); ?>" target="_blank" rel="noopener noreferrer">
          <?php echo esc_html($pageUrl); ?>
        </a>
      </p>
    </div>
  <?php else: ?>
    <div class="notice notice-warning inline">
      <p><?php echo esc_html__('Demo page not found. Re-activate plugin to recreate it.', 'fakestore-products'); ?></p>
    </div>
  <?php endif; ?>

  <?php if (!empty($archiveUrl)) : ?>
    <div class="notice notice-info inline">
      <p>
        <?php echo esc_html__('CPT archive:', 'fakestore-products'); ?>
        <a href="<?php echo esc_url($archiveUrl); ?>" target="_blank" rel="noopener noreferrer">
          <?php echo esc_html($archiveUrl); ?>
        </a>
      </p>
    </div>
  <?php endif; ?>

  <form method="post" action="options.php">
    <?php
      settings_fields('fakestore_products_group');
      do_settings_sections('fakestore-products');
      submit_button();
    ?>
  </form>

  <hr/>

  <h2><?php echo esc_html__('Shortcodes', 'fakestore-products'); ?></h2>
  <ul class="ul-disc">
    <li><code>[fakestore_product]</code> — <?php echo esc_html__('renders product by ID from plugin settings', 'fakestore-products'); ?></li>
    <li><code>[fakestore_random]</code> — <?php echo esc_html__('button + AJAX random product (and saves to CPT in simple mode)', 'fakestore-products'); ?></li>
  </ul>
</div>
