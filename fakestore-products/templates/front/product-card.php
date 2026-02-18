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

$title = isset($product['title']) ? (string)$product['title'] : '';
$price = isset($product['price']) ? (string)$product['price'] : '';
$category = isset($product['category']) ? (string)$product['category'] : '';
$image = isset($product['image']) ? (string)$product['image'] : '';
$desc = isset($product['description']) ? (string)$product['description'] : '';
?>

<div class="fakestore-card">
  <div class="fakestore-card__row">
      <?php if (!empty($image)) : ?>
        <img class="fakestore-card__img" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>"/>
      <?php endif; ?>

    <div class="fakestore-card__body">
      <h3 class="fakestore-card__title"><?php echo esc_html($title); ?></h3>
      <p class="fakestore-card__meta">
        <strong><?php echo esc_html__('Price:', 'fakestore-products'); ?></strong> <?php echo esc_html($price); ?>
        &nbsp;|&nbsp;
        <strong><?php echo esc_html__('Category:', 'fakestore-products'); ?></strong> <?php echo esc_html($category); ?>
      </p>

        <?php if (!empty($desc)) : ?>
          <p class="fakestore-card__desc"><?php echo esc_html($desc); ?></p>
        <?php endif; ?>

        <?php if (!empty($permalink)) : ?>
          <p><a href="<?php echo esc_url($permalink); ?>" class="button button-secondary">
                  <?php echo esc_html__('Open saved post', 'fakestore-products'); ?>
            </a></p>
        <?php endif; ?>

        <?php if (!empty($extraNote)) : ?>
          <p class="description"><?php echo wp_kses_post($extraNote); ?></p>
        <?php endif; ?>
    </div>
  </div>
</div>