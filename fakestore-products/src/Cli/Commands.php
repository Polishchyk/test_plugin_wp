<?php
/*
 * Copyright (c) 2026.
 * Created by oleksandr
 * Contacts: polishchchyk.a.v@gmail.com
 * All rights reserved.
 */

declare(strict_types=1);

namespace FakestoreProducts\Cli;

use FakestoreProducts\Core\Options;

final class Commands
{
    /**
     * @return void
     */
    public function register(): void
    {
        \WP_CLI::add_command('fakestore queue_status', function () {
            $queue = get_option(Options::QUEUE, []);
            $count = is_array($queue) ? count($queue) : 0;
            \WP_CLI::line("Queue items: {$count}");
        });

        \WP_CLI::add_command('fakestore process_queue', function ($args, $assoc) {
            $limit = isset($assoc['limit']) ? max(1, (int)$assoc['limit']) : 0;

            $processed = 0;

            while (true) {
                $queue = get_option(Options::QUEUE, []);
                if (!is_array($queue) || empty($queue)) {
                    break;
                }

                do_action('fakestore_products_process_queue');
                $processed++;

                if ($limit > 0 && $processed >= $limit) {
                    break;
                }
            }

            \WP_CLI::success("Processed {$processed} item(s).");
        });
    }
}