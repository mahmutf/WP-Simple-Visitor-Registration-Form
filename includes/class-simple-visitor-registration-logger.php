<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://nicklarosa.net
 * @since      1.0.0
 *
 * @package    Simple_Visitor_Registration
 * @subpackage Simple_Visitor_Registration/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Simple_Visitor_Registration
 * @subpackage Simple_Visitor_Registration/includes
 * @author     Nick La Rosa <nick@nicklarosa.net>
 */
class Simple_Visitor_Registration_Logger {

	/**
	 * Lookup entries by postid
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function search_by_livestream_id($post_id) {
		global $wpdb; 
	    $table = $wpdb->prefix . 'visitor_registration_logger'; 
	     
	    $recent_entry = $wpdb->get_results( "SELECT * FROM $table WHERE post_id=$post_id order by id asc", ARRAY_A );
 
    	return $recent_entry;
	    
	   
	}


	/**
	 * Lookup entry add if not found
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function lookup_entry($user_id, $post_id, $ip_address, $user_agent) {
		global $wpdb; 
	    $table = $wpdb->prefix . 'visitor_registration_logger';

	    $date = date('Y-m-d H:i:s', strtotime('-70 second'));
	    $recent_entry = $wpdb->get_results( "SELECT * FROM $table WHERE user_id=$user_id AND post_id=$post_id AND last_seen > '$date'", ARRAY_A );

	    if($recent_entry){
	    	// $newEntry = self::update_row_in_db($recent_entry[0]['id'], $user_id, $post_id, $ip_address, $user_agent);
	    	return $recent_entry;
	    } else {
	    	$newEntry = self::insert_entry(  $user_id, $post_id, $ip_address, $user_agent); 
	    	return $newEntry;
	    }
	   
	}

	/**
	 * Lookup entry add if not found
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function update_entry($un ) {
		global $wpdb; 
	    $table = $wpdb->prefix . 'visitor_registration_logger'; 
	    $date = date('Y-m-d H:i:s', strtotime('-70 second'));
	    $recent_entry = $wpdb->get_results( "SELECT * FROM $table WHERE id=$un AND last_seen > '$date'", ARRAY_A );

	    if($recent_entry){
	    	$newEntry = self::update_row_in_db($un);
	    	return $recent_entry;
	    } else { 
	    	return false;
	    }
	   
	}

	/**
	 * Lookup entry add if not found
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function return_all_entries( ) {
		global $wpdb; 
	    $table = $wpdb->prefix . 'visitor_registration_logger'; 
	   
	    $all_entries = $wpdb->get_results( "SELECT * FROM $table ORDER by entered_at DESC", ARRAY_A ); 
	    if($all_entries){ 
	    	return $all_entries;
	    } else { 
	    	return false;
	    }
	   
	}


	/**
	 * Add entry to database
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function insert_entry($email, $fname, $lname, $phone, $custom1) {
 
		global $wpdb; 
	    $table = $wpdb->prefix . 'visitor_registration_logger';
 
	    $date = date('Y-m-d H:i:s', strtotime('now'));
	    $data = array( 
	        'entered_at'     => $date, 
	        'email'      => $email,
	        'fname'      => $fname,
	        'lname'      => $lname,
	        'phone'      => $phone,
	        'custom_field_1'      => $custom1 
	    );
 
	    $format = array( '%s','%s', '%s', '%s', '%s', '%s' );

	    $wpdb->insert( $table, $data, $format );

	    return $wpdb->insert_id;
	}


	// public function update_row_in_db($id) {
	//     global $wpdb;

	//     $table = $wpdb->prefix . 'visitor_registration_logger';
	//     $date = date('Y-m-d H:i:s', strtotime('now'));

	//     $data = array(
	//         'last_seen'     => $date
	//     );
	//     $format = array( '%s' );
	//     $where = array(
	//         'id' => $id
	//     );
	//     $where_format = array(
	//         '%d'
	//     );

	//     return $wpdb->update( $table, $data, $where, $format, $where_format );

	// }

	public function delete_row_from_db() {
	    global $wpdb;

	    $table = $wpdb->prefix . 'visitor_registration_logger';

	    $where = array(
	        'ID' => 1
	    );
	    $where_format = array(
	        '%d'
	    );

	    return $wpdb->delete( $table, $where, $where_format );

	}

	// public function use_prepare_db_query() {
	//     global $wpdb;

	//     $sql = "UPDATE $wpdb->posts SET post_parent = %d WHERE ID = %d AND post_status = %s";

	//     return $wpdb->query( $wpdb->prepare( $sql, 7, 15, 'static' ) );

	// }

}
