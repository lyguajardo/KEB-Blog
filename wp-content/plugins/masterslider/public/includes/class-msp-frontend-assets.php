<?php // 
/**
 * Load frontend scripts and styles
 *
 * @package   Master Slider
 * @author    averta [averta.net]
 * @license   LICENSE.txt
 * @link      http://masterslider.com
 * @copyright Copyright © 2014 averta
 */

/**
* Constructor
*/
class MSP_Frontend_Assets {

	// Name prefixe for assets
	public $prefix = 'ms-';
	// frontend assets directory
	public $assets_dir = '';
	// default assets version
	public $version = '1.0.0';


	/**
	 * Construct
	 */
	public function __construct() {
		
		$this->assets_dir = MSWP_AVERTA_PUB_URL . '/assets';
		$this->version    = MSWP_AVERTA_VERSION;

		add_action( 'wp_enqueue_scripts', array( $this, 'load_assets'  ), 15 );
		add_action( 'wp_print_scripts'	, array( $this, 'check_jquery' ), 25 );
		add_action( 'wp_head'			, array( $this, 'inline_css_fallback' ) );
	}

	/**
	 * Admin hooks to load front end assets in admin area for previewing slider
	 * @return void
	 */
	public function admin_hooks() {
		
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets'  ), 15 );
		add_action( 'admin_print_scripts'  , array( $this, 'check_jquery' ), 25 );
		add_action( 'admin_head'		   , array( $this, 'inline_css_fallback' ) );
	}
	
	/**
	 * Register and load frontend scripts
	 * @return void
	 */
	public function load_assets(){
		global $wp_scripts;

		// Filter base front end assets directory
		$this->assets_dir = apply_filters( 'masterslider_frontend_assets_dir', $this->assets_dir );
		
		
		// JS //////////////////////////////////////////////////////////////////////////////
		
		wp_register_script( 'jquery-easing'	, 
		                     $this->assets_dir . '/js/jquery.easing.min.js' , 
		                     array( 'jquery' ), $this->version, true );

		wp_register_script( 'masterslider-core' , 
		                     $this->assets_dir . '/js/masterslider.min.js'	, 
		                     array( 'jquery', 'jquery-easing' ), $this->version, true );

		wp_register_script( 'masterslider-flickr'	, 
		                     $this->assets_dir . '/js/masterslider.flickr.min.js', 
		                     array( 'masterslider-core' ), $this->version, true );
		

		// Print JS Object //////////////////////////////////////////////////////////////////
		
		wp_localize_script( 'masterslider', 'masterslider_js_params', apply_filters( 'masterslider_js_params', array(
			'ajax_url'        => admin_url( 'admin-ajax.php' )
		) ) );
		

		// CSS //////////////////////////////////////////////////////////////////////////////
		$enqueue_styles = $this->get_styles();

		// Load Css files
		if ( $enqueue_styles ) {
			foreach ( $enqueue_styles as $handle => $args )
				wp_enqueue_style( $handle, $args['src'], $args['deps'], $args['version'] );
		}


		// load custom.css if the directory is writable. else use inline css fallback
	    $inline_css = msp_get_option( 'custom_inline_style', '' );
	    if( empty( $inline_css ) ) {
	    	$custom_css_ver = get_option( 'masterslider_custom_css_ver', '1.0' );
	        wp_enqueue_style ( $this->prefix . 'custom'  , MSWP_AVERTA_URL . '/assets/custom.css', array($this->prefix . 'main'), $custom_css_ver );
	    }

	}



	/**
	 * Get styles for the frontend
	 * 
	 * @return array
	 */
	public function get_styles() {

		return apply_filters( 'masterslider_enqueue_styles', array(

			$this->prefix . 'main' => array(
				'src'     => $this->assets_dir . '/css/masterslider.main.css' ,
				'deps'    => array(),
				'version' => $this->version
			)
		
		) );
	}


	/**
	 * Print custom styles in page header if inline custom css is set.
	 */
	function inline_css_fallback(){

	    $inline_css = msp_get_option( 'custom_inline_style', '' );
	    
	    // if custom.css is not writable, print css styles in page header
	    if( ! empty( $inline_css ) ) {
	    	if( current_user_can( 'manage_options' ) )
	    		printf( "<!-- Note for admin: The custom.css file in [%s] is not writeable, so masterslider uses inline css callback instead. -->\n", MSWP_AVERTA_URL . '/assets/custom.css' );
	    	printf( "<style>%s</style>\n", $inline_css );
	    }
	}

	/**
	 * Make sure jquery version 1.8 or higher will be loaded
	 * 
	 * @access public
	 * @return void
	 */
	public function check_jquery() {
		global $wp_scripts;
		
		// Enforce minimum version of jQuery
		if ( ! empty( $wp_scripts->registered['jquery']->ver ) && ! empty( $wp_scripts->registered['jquery']->src ) && 0 >= version_compare( $wp_scripts->registered['jquery']->ver, '1.8' ) ) {

			wp_deregister_script( 'jquery' );
			wp_register_script( 'jquery', '/wp-includes/js/jquery/jquery.js', array(), '1.8' );
			wp_enqueue_script( 'jquery' );

		}
	}

}
return new MSP_Frontend_Assets();