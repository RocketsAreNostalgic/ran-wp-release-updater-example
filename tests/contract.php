<?php

declare(strict_types=1);

$ran_release_updater_example_plugin  = (string) file_get_contents( dirname( __DIR__ ) . '/ran-wp-release-updater-example.php' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local source contract.
$ran_release_updater_example_version = json_decode( (string) file_get_contents( dirname( __DIR__ ) . '/.release-please-manifest.json' ), true, 512, JSON_THROW_ON_ERROR )['.']; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local release contract.
$ran_release_updater_example_checks  = array(
	'Plugin Name: RAN WP Release Updater Example',
	'// x-release-please-start-version',
	'Version: ' . $ran_release_updater_example_version,
	'Requires at least: 7.0',
	'Requires PHP: 8.4',
	'Update URI: https://github.com/RocketsAreNostalgic/ran-wp-release-updater-example',
	"repository_id: '1433442769'",
	"channel: 'stable'",
	"update_policy: 'automatic'",
	'->register();',
);

foreach ( $ran_release_updater_example_checks as $ran_release_updater_example_check ) {
	if ( ! str_contains( $ran_release_updater_example_plugin, $ran_release_updater_example_check ) ) {

		fwrite( STDERR, 'Missing bootstrap contract: ' . $ran_release_updater_example_check . PHP_EOL ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI failure output.
		exit( 1 );
	}
}

if ( false !== strpos( $ran_release_updater_example_plugin, 'plugins_loaded' ) ) {

	fwrite( STDERR, 'Updater registration must not be deferred to plugins_loaded.' . PHP_EOL ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI failure output.
	exit( 1 );
}

foreach ( array( 'wp_remote_', 'curl_', 'file_get_contents( \'http' ) as $ran_release_updater_example_forbidden ) {
	if ( false !== strpos( $ran_release_updater_example_plugin, $ran_release_updater_example_forbidden ) ) {

		fwrite( STDERR, 'Bootstrap must not perform HTTP: ' . $ran_release_updater_example_forbidden . PHP_EOL ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI failure output.
		exit( 1 );
	}
}

if ( ! str_contains( $ran_release_updater_example_plugin, 'is_readable( $ran_release_updater_example_bootstrap )' ) || ! str_contains( $ran_release_updater_example_plugin, "'admin_notices'" ) ) {

	fwrite( STDERR, 'Missing-vendor handling must stay non-fatal and administrator-visible.' . PHP_EOL ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI failure output.
	exit( 1 );
}

echo "Bootstrap contract passed.\n";
