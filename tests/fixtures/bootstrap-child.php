<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- WordPress fixture constant.

$ran_release_updater_example_events = array();

function add_action( string $hook_name, callable $callback ): bool { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound,Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- WordPress fixture signature.

	$GLOBALS['ran_release_updater_example_events'][] = array( 'hook', $hook_name );

	return true;
}

function current_user_can( string $capability ): bool { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound,Generic.CodeAnalysis.UnusedFunctionParameter.Found -- WordPress fixture signature.

	return true;
}

function esc_html__( string $text, string $domain ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound,Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- WordPress fixture signature.

	return $text;
}

function wp_remote_get( string $url ): never { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound,Generic.CodeAnalysis.UnusedFunctionParameter.Found -- WordPress fixture signature.

	throw new RuntimeException( 'Bootstrap performed provider HTTP.' );
}

if ( ! isset( $argv[1] ) ) {

	throw new RuntimeException( 'Plugin path argument is required.' );
}

require $argv[1];

echo json_encode( $ran_release_updater_example_events, JSON_THROW_ON_ERROR ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Standalone JSON-only child fixture.
