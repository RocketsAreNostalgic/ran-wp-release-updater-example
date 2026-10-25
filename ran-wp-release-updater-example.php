<?php
/**
 * Plugin Name: RAN WP Release Updater Example
 * Plugin URI: https://github.com/RocketsAreNostalgic/ran-wp-release-updater-example
 * Description: Minimal developer example for ran/wp-release-updater.
 * // x-release-please-start-version
 * Version: 0.1.0
 * // x-release-please-end
 * Requires at least: 7.0
 * Requires PHP: 8.4
 * Author: Rockets Are Nostalgic
 * License: GPL-2.0-or-later
 * Update URI: https://github.com/RocketsAreNostalgic/ran-wp-release-updater-example
 * Text Domain: ran-wp-release-updater-example
 */

declare(strict_types=1);

namespace Ran\ReleaseUpdaterExample; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Public package namespace is part of the example contract.

if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

$ran_release_updater_example_bootstrap = __DIR__ . '/vendor/ran/wp-release-updater/bootstrap.php';

if ( ! is_readable( $ran_release_updater_example_bootstrap ) ) {

	if ( ! add_action(
		'admin_notices',
		static function (): void {
			if ( ! current_user_can( 'activate_plugins' ) ) {

				return;
			}

			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'RAN WP Release Updater Example is missing its production dependencies. Install the distributed plugin ZIP.', 'ran-wp-release-updater-example' );
			echo '</p></div>';
		}
	) ) {

		return;
	}

	return;
}

$ran_release_updater_example_registrar = require $ran_release_updater_example_bootstrap;
$ran_release_updater_example_target    = $ran_release_updater_example_registrar->plugin(
	provider: 'github',
	plugin_file: __FILE__,
	repository: 'RocketsAreNostalgic/ran-wp-release-updater-example',
	repository_id: '1433442769',
	channel: 'stable',
	update_policy: 'automatic'
);

$ran_release_updater_example_target->register();
