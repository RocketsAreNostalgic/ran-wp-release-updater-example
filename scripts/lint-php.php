<?php

declare(strict_types=1);

$ran_release_updater_example_root     = dirname( __DIR__ );
$ran_release_updater_example_iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $ran_release_updater_example_root, FilesystemIterator::SKIP_DOTS ) );

foreach ( $ran_release_updater_example_iterator as $ran_release_updater_example_file ) {
	$ran_release_updater_example_path = $ran_release_updater_example_file->getPathname();
	if ( ! str_ends_with( $ran_release_updater_example_path, '.php' ) || str_contains( $ran_release_updater_example_path, '/vendor/' ) || str_contains( $ran_release_updater_example_path, '/node_modules/' ) || str_contains( $ran_release_updater_example_path, '/build/' ) || str_contains( $ran_release_updater_example_path, '/.git/' ) ) {

		continue;
	}

	passthru( escapeshellarg( PHP_BINARY ) . ' -l ' . escapeshellarg( $ran_release_updater_example_path ), $ran_release_updater_example_status ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_passthru -- Dedicated local syntax checker.
	if ( 0 !== $ran_release_updater_example_status ) {

		exit( $ran_release_updater_example_status ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI exit status, not rendered output.
	}
}
