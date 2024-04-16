<?php
/**
 * Plugin Name: xeno-canto oEmbed.
 * Plugin URI: https://github.com/redundans/xeno-canto-oembed
 * Description: A plugin for embedding xeno-canto sounds with oEmbed.
 *
 * Version: 1.0
 * Author: Redundans
 * Author URI: https://github.com/redundans/
 * License: GPLv2 or later
 * Text Domain: xenocantooembed
 *
 * @package xenocantooembed
 */

/**
 * Add xeno-canto oEmbed endpoint to REST API.
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'xeno-canto-oembed/v1',
			'/embed/',
			array(
				'methods'   => WP_REST_Server::READABLE,
				'callback' => 'xeno_canto_oembed_json',
				'args'     => array(
					'url'   => array(
						'validate_callback' => function( $param ) {
							return wp_http_validate_url( $param );
						},
						'sanitize_callback' => function( $param ) {
							return wp_http_validate_url( $param );
						},
						'required'          => true,
						'description'       => 'The url to xeno-xanto sound.',
					),
				),
			)
		);
	}
);

/**
 * Add plugin oEmbed provider.
 */
add_action(
	'init',
	function(){
		wp_oembed_add_provider(
			'https://xeno-canto.org/*',
			home_url() . '/wp-json/xeno-canto-oembed/v1/embed'
		);
	}
);

/**
 * Return oEmbed JSON data.
 *
 * @param WP_REST_Request $request The request.
 * @return WP_Error|WP_REST_Response
 */
function xeno_canto_oembed_json( WP_REST_Request $request ): WP_REST_Response {
	$embed_url = $request->get_param( 'url' );
	$embed_id  = basename( $embed_url );
	$data      = array(
		'version'       => '1.0',
		'type'          => 'rich',
		'provider_name' => 'xeno-canto',
		'provider_url'  => $embed_url,
		'width'         => 340,
		'height'        => 115,
		'title'         => get_embed_title( $embed_url ) ?? '',
		'html'          => "<iframe src=\"https://www.xeno-canto.org/{$embed_id}/embed?simple=1\" scrolling=\"no\" frameborder=\"0\" width=\"340\" height=\"115\"></iframe>",
	);
	return new WP_REST_Response( $data, 200 );
}

function get_embed_title( string $url ): string {
	$str = file_get_contents($url);
	$str = trim( preg_replace( '/\s+/', ' ', $str ) ); // supports line breaks
	preg_match( '/\<title\>(.*)\<\/title\>/i', $str,$title ); // ignore case
	return $title[1] ?? null;
}

