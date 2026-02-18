<?php
/**
 * Plugin Name: Test FakeStore Products
 * Description: Test plugin: settings + shortcodes + AJAX + CPT (optional advanced).
 * Version: 1.0.0
 * Requires PHP: 8.0
 * Author: Oleksandr P.
 * Author URI: https://www.linkedin.com/in/oleksandr-polishchuk-4web/
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('FAKESTORE_PRODUCTS_PATH', plugin_dir_path(__FILE__));
define('FAKESTORE_PRODUCTS_URL', plugin_dir_url(__FILE__));
define('FAKESTORE_PRODUCTS_VERSION', '1.0.0');

require_once FAKESTORE_PRODUCTS_PATH . 'src/Core/Autoloader.php';

$autoloader = new FakestoreProducts\Core\Autoloader();
$autoloader->register();
$autoloader->addNamespace('FakestoreProducts\\', FAKESTORE_PRODUCTS_PATH . 'src/');

register_activation_hook(__FILE__, function () {
    (new FakestoreProducts\Plugin())->activate();
});

register_deactivation_hook(__FILE__, function () {
    (new FakestoreProducts\Plugin())->deactivate();
});

(new FakestoreProducts\Plugin())->boot();
