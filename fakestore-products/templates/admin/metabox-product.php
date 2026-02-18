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

<table class="form-table" role="presentation">
  <tbody>
  <tr>
    <th scope="row"><label><?php echo esc_html__('API ID', 'fakestore-products'); ?></label></th>
    <td>
      <input type="text" class="regular-text" value="<?php echo esc_attr((string)$api_id); ?>" readonly/>
      <p class="description"><?php echo esc_html__('Read-only (source: API).', 'fakestore-products'); ?></p>
    </td>
  </tr>

  <tr>
    <th scope="row"><label for="_price"><?php echo esc_html__('Price', 'fakestore-products'); ?></label></th>
    <td><input name="_price" id="_price" type="text" class="regular-text" value="<?php echo esc_attr((string)$price); ?>"/></td>
  </tr>

  <tr>
    <th scope="row"><label for="_category"><?php echo esc_html__('Category', 'fakestore-products'); ?></label></th>
    <td>
      <input name="_category" id="_category" type="text" class="regular-text" value="<?php echo esc_attr((string)$category); ?>"/>
      <p class="description"><?php echo esc_html__('Updating this will also update taxonomy terms.', 'fakestore-products'); ?></p>
    </td>
  </tr>

  <tr>
    <th scope="row"><label for="_image"><?php echo esc_html__('Image URL', 'fakestore-products'); ?></label></th>
    <td><input name="_image" id="_image" type="url" class="large-text" value="<?php echo esc_attr((string)$image); ?>"/></td>
  </tr>

  <tr>
    <th scope="row"><label for="_rating_rate"><?php echo esc_html__('Rating rate', 'fakestore-products'); ?></label></th>
    <td><input name="_rating_rate" id="_rating_rate" type="text" class="regular-text" value="<?php echo esc_attr((string)$rating_rate); ?>"/></td>
  </tr>

  <tr>
    <th scope="row"><label for="_rating_count"><?php echo esc_html__('Rating count', 'fakestore-products'); ?></label></th>
    <td><input name="_rating_count" id="_rating_count" type="number" class="regular-text" value="<?php echo esc_attr((string)$rating_count); ?>"/></td>
  </tr>
  </tbody>
</table>
