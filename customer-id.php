<?php
/**
 * Plugin Name: Unique Customer ID
 * Plugin URI: https://gist.github.com/snrjosh
 * Description: Generates a unique customer ID for each user.
 * Version: 1.0
 * Author: Said Rajab
 * Author URI: https://gist.github.com/snrjosh
 * Licence: GPLv2 or later
 *
 * @package UCID
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UniqueCustomerId' ) ) {
	class UniqueCustomerId {
		
		function __construct() {

			add_action( 'user_register', array( $this, 'save_unique_id_on_user_register' ) );
			add_action( 'personal_options_update', array( $this, 'save_unique_id_on_profile_update' ) );
			add_action( 'show_user_profile', array( $this, 'show_unique_id_on_user_profile' ) );
			add_shortcode( 'unique_id', array( $this, 'unique_id_shortcode' ) );
		}

		/**
		 * Generate a unique number from current date and time.
		 *
		 * @return 20 character string.
		 */
		function generate_unique_number() {
			$current_date  = date( 'Ymd' );
			$current_time  = date( 'His' );
			$unique_number = $current_date . '-' . $current_time . '-' . substr( wp_rand(), 0, 4 );
			return $unique_number;
		}

		/**
		 * Upon user registration, generate a unique
		 * number and add this to the usermeta table.
		 *
		 * @param $user_id (int) The ID of newly registered user.
		 */
		function save_unique_id_on_user_register( $user_id ) {
			$unique_customer_id = $this->generate_unique_number();
			update_user_meta( $user_id, 'unique_id', $unique_customer_id );
		}
		
		/**
		 * If user already exists but unique_id key doesn't exist,
		 * generate a unique number and add this to the usermeta table.
		 * 
		 * Only fires if current user is editing their own profile.
		 * 
		 * (TO DO) Add admin notice to notify existing users to click
		 * "Update Profile" so as to fire this action.
		 * 
		 * @param $user_id (int) The ID of current user
		 */
		function save_unique_id_on_profile_update( $user_id ) {
			$unique_customer_id = $this->generate_unique_number();
			if ( current_user_can( 'edit_user', $user_id) && empty( is_array( $unique_customer_id ) ) ) {
		    	update_user_meta( $user_id, 'unique_id', $unique_customer_id );
		    }
		}

		/**
		 * Display the unique ID if user is viewing\editing their own profile.
		 *
		 * @param $user WP_User object for the current user.
		 */
		function show_unique_id_on_user_profile( $user ) {
			$unique_customer_id = $this->get_unique_id_from_db($user->ID);
			?>
			<table class="form-table">
				<tr>
					<th><label for="unique_id"><?php _e( 'My Unique ID' ); ?></label></th>
					<td><input type="text" name="unique_id" value="<?php echo esc_attr( $unique_customer_id ); ?>" readonly><p class="description">Use this to claim your discount on membership renewal.</p>
					</td>
				</tr>
			</table>
			<?php 
		}

		//Register a shortcode to display unique ID on posts\pages
		function unique_id_shortcode() {
			$user_id = get_current_user_id();
			return $this->get_unique_id_from_db($user_id);
		}

		//Return unique ID from db
		function get_unique_id_from_db( $user_id ) {
			$unique_customer_id = get_user_meta( $user_id, 'unique_id', true );
			return $unique_customer_id;
		}
	}
}

if ( class_exists( 'UniqueCustomerId' ) ) {
	$customer_id = new UniqueCustomerId();

	// Use this function in your theme template files.
	function to_use_in_theme_files() {
		global $customer_id;
		return $customer_id->unique_id_shortcode();
	}
}
