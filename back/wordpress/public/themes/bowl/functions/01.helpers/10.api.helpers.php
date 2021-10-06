<?php

/**
 * Registers a JSON API.
 * Default methods are GET and POST.
 *
 * Get parameters from XHR request with $request->get_params();
 * URLs are : $base/wp-json/$namespace/$endpoint.
 *
 * All returned data will be converted to JSON
 *
 * @param string $namespace API Namespace, usually API version (v1, v2 ...)
 * @param string $endpoint Endpoint of API
 * @param array $options Options for register_rest_route
 * @param string|callback $callback Callable (
 */
function register_api_endpoint ( $namespace, $endpoint, $options, $callback ) {
	add_action( 'rest_api_init', function () use ($namespace, $endpoint, $options, $callback) {
		register_rest_route( $namespace, $endpoint, array_merge([
			'methods' => ['GET', 'POST'],
			'callback' => $callback,
			'permission_callback' => '__return_true',
		], $options));
	});
}