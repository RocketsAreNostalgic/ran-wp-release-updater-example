<?php

declare(strict_types=1);

function add_action( string $hook_name, callable $callback ): bool { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound,Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- WordPress fixture signature.

	return true;
}

function current_user_can( string $capability ): bool { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound,Generic.CodeAnalysis.UnusedFunctionParameter.Found -- WordPress fixture signature.

	return true;
}

function esc_html__( string $text, string $domain ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound,Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- WordPress fixture signature.

	return $text;
}
