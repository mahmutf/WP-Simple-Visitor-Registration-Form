<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://nicklarosa.net
 * @since      1.0.0
 *
 * @package    Simple_Visitor_Registration
 * @subpackage Simple_Visitor_Registration/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Simple_Visitor_Registration
 * @subpackage Simple_Visitor_Registration/public
 * @author     Nick La Rosa <nick@nicklarosa.net>
 */
class Simple_Visitor_Registration_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() { 

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/simple-visitor-registration-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		global $wp;
		global $post; 

  		if($GOOGLE_CAPTCHA_SITE_KEY = getenv('GOOGLE_CAPTCHA_SITE_KEY')){
  		} else {
  			$options = get_option( $this->plugin_name ); 
  			$GOOGLE_CAPTCHA_SITE_KEY = ( isset( $options['google_captcha_site_key'] ) && ! empty( $options['google_captcha_site_key'] ) ) ? esc_attr( $options['google_captcha_site_key'] ) : '';
  		} 
  		if(($GOOGLE_CAPTCHA_SITE_KEY !== '') && ($GOOGLE_CAPTCHA_SITE_KEY !== null)){
  			wp_enqueue_script( "recpatcha", 'https://www.google.com/recaptcha/api.js', [], $this->version, false );   
  		}
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/simple-visitor-registration-public.js', array( 'jquery' ), $this->version, false );
		


  		if($VISITOR_REGISTRATION_NONCE = getenv('VISITOR_REGISTRATION_NONCE')){
		} else {
		    $VISITOR_REGISTRATION_NONCE = 'we_really_3_need_9()_something_stronghere'; 
		}

		wp_localize_script( $this->plugin_name, 
			'wp_ajax', 
			array(
		        'ajax_url' => admin_url( 'admin-ajax.php' ), 
		        '_nonce' => wp_create_nonce( $VISITOR_REGISTRATION_NONCE ), 
      			'google_captcha_site_key' => $GOOGLE_CAPTCHA_SITE_KEY

		    ) 
		); 
	} 
	  

	public function ajax_register_visitor() {
 

		$email = $_POST['email']; 
		$fname = stripcslashes($_POST['fname']);
		$lname = stripcslashes($_POST['lname']); 
		$email = stripcslashes($_POST['email']); 
		$custom1 = stripcslashes($_POST['cfield1']);  
		$phone = stripcslashes($_POST['phone']);    

		if($GOOGLE_CAPTCHA_SITE_KEY = getenv('GOOGLE_CAPTCHA_SITE_KEY')){
  		} else {
  			$options = get_option( 'simple-visitor-registration' ); 
  			$GOOGLE_CAPTCHA_SITE_KEY = ( isset( $options['google_captcha_site_key'] ) && ! empty( $options['google_captcha_site_key'] ) ) ? esc_attr( $options['google_captcha_site_key'] ) : '';
  		}

  		if(($GOOGLE_CAPTCHA_SITE_KEY !== '') || ($GOOGLE_CAPTCHA_SITE_KEY !== null)){
  			try {
			    $testCaptcha = $this->verify_captcha(); 
			} catch (Exception $e) { 
			    echo json_encode(array('status'=>false, 'message'=>__('Caught exception: '.$e->getMessage())));
			    die;
			} 
  			if($testCaptcha != true){
  				echo json_encode(array('status'=>false, 'message'=>__('Looks like recaptcha has failed')));
				die;
  			}
  		} 
		if(strlen($fname) < 2):
			echo json_encode(array('status'=>false, 'message'=>__('Please enter a valid first name')));
			die;
		endif;
		if(strlen($lname) < 2):
			echo json_encode(array('status'=>false, 'message'=>__('Please enter a valid last name')));
			die;
		endif;

		if(strlen($phone) < 2):
			echo json_encode(array('status'=>false, 'message'=>__('Please enter a valid phone number')));
			die;
		endif;

		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		    echo json_encode(array('status'=>false, 'message'=>__('Invalid Email Format')));
			die();
		} 


		if(strlen($custom1) < 1) :
			echo json_encode(array('status'=>false, 'message'=>__('Please ensure all fields are entered in correctly')));
			die;
		endif;

		$logger = new Simple_Visitor_Registration_Logger; 

		  
		$newId = $logger->insert_entry($email, $fname, $lname, $phone, $custom1);
		if($newId):
			echo json_encode(array('status'=>true,  'message'=>__('Success!')));
		else:
			echo json_encode(array('status'=>false,  'message'=>__('Sorry, something went wrong here.')));
		endif;
		die; 
	}


	public function my_simple_crypt( $string, $action = 'e' ) {
	    // you may change these values to your own
	    $secret_key = 'asdfasdfasdfasdfasdfasdfasdfasdfasdf';
	    $secret_iv = 'sdafassadfasdfasdfasdfasdfasdfasdfasd';
	 
	    $output = false;
	    $encrypt_method = "AES-256-CBC";
	    $key = hash( 'sha256', $secret_key );
	    $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
	 
	    if( $action == 'e' ) {
	        $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
	    }
	    else if( $action == 'd' ){
	        $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
	    }
	 
	    return $output;
	}

	/* filter to defer google captcha */
	public function defer_google_captcha_script( $tag, $handle ) {

		if ( 'recpatcha' !== $handle ) {
			return $tag;
		}

		// return str_replace( ' src', 'async defer src', $tag ); // defer the script
		//return str_replace( ' src', ' async src', $tag ); // OR async the script
		return str_replace( ' src', ' async defer src', $tag ); // OR do both!

	}

	public function verify_captcha(){


		if($GOOGLE_CAPTCHA_SECRET_KEY = getenv('GOOGLE_CAPTCHA_SECRET_KEY')){
		} else { 
			$options = get_option( 'simple-visitor-registration' ); 
		    $GOOGLE_CAPTCHA_SECRET_KEY = ( isset( $options['google_captcha_secret_key'] ) && ! empty( $options['google_captcha_secret_key'] ) ) ? esc_attr( $options['google_captcha_secret_key'] ) : ''; 
		}

		$post_data = http_build_query(
		    array(
		        'secret' => $GOOGLE_CAPTCHA_SECRET_KEY,
		        'response' => $_POST['g-recaptcha-response'],
		        'remoteip' => $_SERVER['REMOTE_ADDR']
		    )
		);
		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded',
		        'content' => $post_data
		    )
		);

		$context  = stream_context_create($opts);
		$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
		$result = json_decode($response);
		if (!$result->success) {
		    throw new Exception('Oops! CAPTCHA verification failed. Please let one of our staff know that something is wrong here', 1);
		} else {
			return true;
		}
	}

}