<?php

declare(strict_types=1);

/** @return array<int, mixed> */
function ran_release_updater_example_run_child( string $ran_release_updater_example_child, string $ran_release_updater_example_plugin ): array {

	$ran_release_updater_example_process = proc_open( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_proc_open -- Isolated PHP bootstrap fixture.
		array( PHP_BINARY, $ran_release_updater_example_child, $ran_release_updater_example_plugin ),
		array(
			1 => array( 'pipe', 'w' ),
			2 => array( 'pipe', 'w' ),
		),
		$ran_release_updater_example_pipes
	);

	if ( ! is_resource( $ran_release_updater_example_process ) ) {

		throw new RuntimeException( 'Unable to start bootstrap fixture.' );
	}

	$ran_release_updater_example_output = stream_get_contents( $ran_release_updater_example_pipes[1] );
	$ran_release_updater_example_error  = stream_get_contents( $ran_release_updater_example_pipes[2] );
	fclose( $ran_release_updater_example_pipes[1] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Fixture stream cleanup.
	fclose( $ran_release_updater_example_pipes[2] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Fixture stream cleanup.

	if ( 0 !== proc_close( $ran_release_updater_example_process ) ) {

		throw new RuntimeException( 'Bootstrap fixture failed: ' . $ran_release_updater_example_error );
	}

	return json_decode( $ran_release_updater_example_output, true, 512, JSON_THROW_ON_ERROR );
}

$ran_release_updater_example_root      = dirname( __DIR__ );
$ran_release_updater_example_temporary = sys_get_temp_dir() . '/ran-release-updater-example-' . bin2hex( random_bytes( 8 ) );
$ran_release_updater_example_plugin    = $ran_release_updater_example_temporary . '/ran-wp-release-updater-example.php';
$ran_release_updater_example_vendor    = $ran_release_updater_example_temporary . '/vendor/ran/wp-release-updater';
$ran_release_updater_example_child     = $ran_release_updater_example_root . '/tests/fixtures/bootstrap-child.php';

mkdir( $ran_release_updater_example_vendor, 0777, true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir -- Isolated fixture directory.
copy( $ran_release_updater_example_root . '/ran-wp-release-updater-example.php', $ran_release_updater_example_plugin );
file_put_contents( $ran_release_updater_example_vendor . '/bootstrap.php', '<?php return new class { public function plugin(...$arguments): object { $GLOBALS["ran_release_updater_example_events"][] = array("plugin", $arguments); return new class { public function register(): void { $GLOBALS["ran_release_updater_example_events"][] = array("register"); } }; } };' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Fake fixture dependency.

$ran_release_updater_example_events = ran_release_updater_example_run_child( $ran_release_updater_example_child, $ran_release_updater_example_plugin );
$ran_release_updater_example_target = array(
	array(
		'plugin',
		array(
			'provider'      => 'github',
			'plugin_file'   => realpath( $ran_release_updater_example_plugin ),
			'repository'    => 'RocketsAreNostalgic/ran-wp-release-updater-example',
			'repository_id' => '1433442769',
			'channel'       => 'stable',
			'update_policy' => 'automatic',
		),
	),
	array( 'register' ),
);

if ( $ran_release_updater_example_target !== $ran_release_updater_example_events ) {

	throw new RuntimeException( 'Bootstrap did not immediately register the expected passive native target.' );
}

unlink( $ran_release_updater_example_vendor . '/bootstrap.php' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- Remove fake fixture dependency.

if ( array( array( 'hook', 'admin_notices' ) ) !== ran_release_updater_example_run_child( $ran_release_updater_example_child, $ran_release_updater_example_plugin ) ) {

	throw new RuntimeException( 'Missing dependency did not register the administrator notice.' );
}

echo "Bootstrap execution passed.\n";
