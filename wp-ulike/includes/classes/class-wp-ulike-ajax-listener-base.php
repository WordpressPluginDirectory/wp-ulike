<?php
/**
 * WP ULike AJAX Listener
 * 
 * @package    wp-ulike
 * @author     TechnoWich 2025
 * @link       https://wpulike.com
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
* Base AJAX class
*/
abstract class wp_ulike_ajax_listener_base{

	/**
	* Form Data
	*/
	public $data;

	/**
	* User info
	*/
	public $user;

	/**
	* Settings Type
	*/
	public $settings_type;

	public function __construct(){
		$this->setUser();
	}

	/**
	 * Set data
	 *
	 * @return void
	 */
    public function setData( $data ) {
        $this->data = $data;
	}

	/**
	 * Set data
	 *
	 * @return void
	 */
    public function getData( $data ) {
        return $this->data;
    }

	/**
	 * Set current user id if exist
	 *
	 * @return void
	 */
	protected function setUser(){
		$this->user = is_user_logged_in() ? get_current_user_id() : false;
	}

	/**
	* Send an Error Response
	*
	* @param string $error
	*/
	protected function sendError( $error = null ){
		$error = ! empty( $error ) ? $error : esc_html__( 'There was an error processing the request.', 'wp-ulike' );
		return wp_send_json_error( $error );
	}

	/**
	 * Send a response
	 *
	 * @param array $response
	 */
	protected function response( $response ){
		return wp_send_json_success( $response );
	}
}