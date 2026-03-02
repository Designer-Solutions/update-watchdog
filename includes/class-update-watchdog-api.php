<?php
/**
 * Registers and handles the REST API endpoint /wp-json/update-watchdog/v1/status.
 * Authorization via header: Authorization: Bearer {TOKEN}
 *
 * @package WP_Watchdog
 */

defined( 'ABSPATH' ) || exit;

class Update_Watchdog_API {

	/**
	 * @var Update_Watchdog_Updater
	 */
	private $updater;

	public function __construct(Update_Watchdog_Updater $updater ) {
		$this->updater = $updater;
	}

	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route(
			'update-watchdog/v1',
			'/status',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_status' ),
				// Authorization is handled manually in the callback using a Bearer token.
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Callback for GET /wp-json/update-watchdog/v1/status
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_status( WP_REST_Request $request ) {
		$auth_header = $request->get_header( 'authorization' );

		if ( empty( $auth_header ) ) {
			return new WP_REST_Response(
				array( 'error' => __( 'Missing Authorization header.', 'update-watchdog' ) ),
				401
			);
		}

		if ( ! preg_match( '/^Bearer\s+(\S+)$/i', $auth_header, $matches ) ) {
			return new WP_REST_Response(
				array( 'error' => __( 'Invalid Authorization header format. Expected: Bearer {TOKEN}', 'update-watchdog' ) ),
				401
			);
		}

		$provided_token = $matches[1];
		$stored_token   = get_option( Update_Watchdog_Admin::TOKEN_OPTION );

		if ( ! $stored_token || ! hash_equals( $stored_token, $provided_token ) ) {
			return new WP_REST_Response(
				array( 'error' => __( 'Invalid token.', 'update-watchdog' ) ),
				403
			);
		}

		try {
			$status = $this->updater->get_status();
			return new WP_REST_Response( $status, 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response(
				array( 'error' => __( 'Internal error while fetching update data.', 'update-watchdog' ) ),
				500
			);
		}
	}
}
