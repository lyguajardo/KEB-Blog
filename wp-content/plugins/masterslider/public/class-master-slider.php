<?php
/**
 * Master Slider.
 *
 * @package   MasterSlider
 * @author    averta [averta.net]
 * @license   LICENSE.txt
 * @link      http://masterslider.com
 * @copyright Copyright © 2014 averta
 */

if ( ! class_exists( 'Master_Slider' ) ) :

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 */
class Master_Slider {

	/**
	 * Unique identifier for your plugin.
	 *
	 * The variable name is used as the text domain when internationalizing strings of text.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'masterslider';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;


	/**
	 * Instance of Master_Slider_Admin class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	public $admin = null;



	/**
	 * Initialize the plugin
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$this->includes();

		add_action( 'init', array( $this, 'init' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Loaded action
		do_action( 'masterslider_loaded' );
	}


	/**
	 * 
	 * @return [type] [description]
	 */
	private function includes() {

		// load common functionalities
		include_once( MSWP_AVERTA_INC_DIR . '/index.php' );
			

		// Dashboard and Administrative Functionality
		if ( is_admin() ) {

			// Load AJAX spesific codes on demand 
			if ( defined('DOING_AJAX') && DOING_AJAX ){
				include_once( MSWP_AVERTA_ADMIN_DIR . '/includes/classes/class-msp-admin-ajax.php');
				include_once( MSWP_AVERTA_ADMIN_DIR . '/includes/msp-admin-functions.php');
			}
			
			// Load admin spesific codes 
			else {
				$this->admin = include( MSWP_AVERTA_ADMIN_DIR . '/class-master-slider-admin.php' );
			}

		// Load Frontend Functionality
		} else {

			include_once( 'includes/class-msp-frontend-assets.php' );
		}

	}


	/**
	 * Init Masterslider when WordPress Initialises.
	 * 	
	 * @return void
	 */
	public function init(){

		// Before init action
		do_action( 'before_masterslider_init' );

		// Load plugin text domain
		$this->load_plugin_textdomain();

		// Init action
		do_action( 'masterslider_init' );
	}


	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		
		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}
		
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {

		global $mspdb;
		$mspdb->create_tables();

		// add masterslider custom caps
		self::assign_custom_caps();
		do_action( 'masterslider_activated', get_current_blog_id() );
	}

	/**
	 * Assign masterslider custom capabilities to main roles
	 * @return void
	 */
	public static function assign_custom_caps( $force_update = false ){

		// check if custom capabilities are added before or not
		$is_added = get_option( 'masterslider_capabilities_added', 0 );

		// add caps if they are not already added
		if( ! $is_added || $force_update ) {

			// assign masterslider capabilities to following roles
			$roles = array( 'administrator', 'editor' );

			foreach ( $roles as $role ) {
				$role = get_role( $role );
				$role->add_cap( 'access_masterslider'  ); 
				$role->add_cap( 'publish_masterslider' ); 
				$role->add_cap( 'delete_masterslider'  ); 
				$role->add_cap( 'create_masterslider'  );
				$role->add_cap( 'export_masterslider'  );
				$role->add_cap( 'duplicate_masterslider'  );
			}

			update_option( 'masterslider_capabilities_added', 1 );
		}
	}


	/**
	 * Set default options
	 *
	 * @since    1.3.0
	 */
	public static function set_default_options( $force_update = false ){

		// check if default options are added before or not
		$is_added = get_option( 'masterslider_default_options_added', 0 );

		// add caps if they are not already added
		if( ! $is_added || $force_update ) {

			msp_update_option( 'preset_effect', 'eyJtZXRhIjp7IlByZXNldEVmZmVjdCFpZHMiOiI2LDcsOCw5LDEwLDExLDEyLDEzLDE0LDE1LDE2LDE3LDE4LDE5LDIwLDIxLDIyLDIzLDI0LDI1LDI2LDI3LDI4LDI5LDMwLDMxIiwiUHJlc2V0RWZmZWN0IW5leHRJZCI6MzJ9LCJNU1BhbmVsLlByZXNldEVmZmVjdCI6eyI2Ijoie1wiaWRcIjo2LFwibmFtZVwiOlwiUmlnaHQgc2hvcnRcIixcInR5cGVcIjpcInByZXNldFwiLFwiZmFkZVwiOnRydWUsXCJ0cmFuc2xhdGVYXCI6MTUwfSIsIjciOiJ7XCJpZFwiOjcsXCJuYW1lXCI6XCJMZWZ0IHNob3J0XCIsXCJ0eXBlXCI6XCJwcmVzZXRcIixcImZhZGVcIjp0cnVlLFwidHJhbnNsYXRlWFwiOi0xNTB9IiwiOCI6IntcImlkXCI6OCxcIm5hbWVcIjpcIlRvcCBzaG9ydFwiLFwidHlwZVwiOlwicHJlc2V0XCIsXCJmYWRlXCI6dHJ1ZSxcInRyYW5zbGF0ZVlcIjotMTUwfSIsIjkiOiJ7XCJpZFwiOjksXCJuYW1lXCI6XCJCb3R0b20gc2hvcnRcIixcInR5cGVcIjpcInByZXNldFwiLFwiZmFkZVwiOnRydWUsXCJ0cmFuc2xhdGVZXCI6MTUwfSIsIjEwIjoie1wiaWRcIjoxMCxcIm5hbWVcIjpcIlJpZ2h0IGxvbmdcIixcInR5cGVcIjpcInByZXNldFwiLFwiZmFkZVwiOnRydWUsXCJ0cmFuc2xhdGVYXCI6NTAwfSIsIjExIjoie1wiaWRcIjoxMSxcIm5hbWVcIjpcIkxlZnQgbG9uZ1wiLFwidHlwZVwiOlwicHJlc2V0XCIsXCJmYWRlXCI6dHJ1ZSxcInRyYW5zbGF0ZVhcIjotNTAwfSIsIjEyIjoie1wiaWRcIjoxMixcIm5hbWVcIjpcIlRvcCBsb25nXCIsXCJ0eXBlXCI6XCJwcmVzZXRcIixcImZhZGVcIjp0cnVlLFwidHJhbnNsYXRlWVwiOi01MDB9IiwiMTMiOiJ7XCJpZFwiOjEzLFwibmFtZVwiOlwiQm90dG9tIGxvbmdcIixcInR5cGVcIjpcInByZXNldFwiLFwiZmFkZVwiOnRydWUsXCJ0cmFuc2xhdGVZXCI6NTAwfSIsIjE0Ijoie1wiaWRcIjoxNCxcIm5hbWVcIjpcIjNEIEZyb250IHNob3J0XCIsXCJ0eXBlXCI6XCJwcmVzZXRcIixcImZhZGVcIjp0cnVlLFwidHJhbnNsYXRlWlwiOjUwMH0iLCIxNSI6IntcImlkXCI6MTUsXCJuYW1lXCI6XCIzRCBCYWNrIHNob3J0XCIsXCJ0eXBlXCI6XCJwcmVzZXRcIixcImZhZGVcIjp0cnVlLFwidHJhbnNsYXRlWlwiOi01MDB9IiwiMTYiOiJ7XCJpZFwiOjE2LFwibmFtZVwiOlwiM0QgRnJvbnQgbG9uZ1wiLFwidHlwZVwiOlwicHJlc2V0XCIsXCJmYWRlXCI6dHJ1ZSxcInRyYW5zbGF0ZVpcIjoxNTAwfSIsIjE3Ijoie1wiaWRcIjoxNyxcIm5hbWVcIjpcIjNEIEJhY2sgbG9uZ1wiLFwidHlwZVwiOlwicHJlc2V0XCIsXCJmYWRlXCI6dHJ1ZSxcInRyYW5zbGF0ZVpcIjotMTUwMH0iLCIxOCI6IntcImlkXCI6MTgsXCJuYW1lXCI6XCJSb3RhdGUgMTgwXCIsXCJ0eXBlXCI6XCJwcmVzZXRcIixcImZhZGVcIjp0cnVlLFwicm90YXRlXCI6MTgwfSIsIjE5Ijoie1wiaWRcIjoxOSxcIm5hbWVcIjpcIlJvdGF0ZSAzNjBcIixcInR5cGVcIjpcInByZXNldFwiLFwiZmFkZVwiOnRydWUsXCJyb3RhdGVcIjozNjB9IiwiMjAiOiJ7XCJpZFwiOjIwLFwibmFtZVwiOlwiUm90YXRlIDkwXCIsXCJ0eXBlXCI6XCJwcmVzZXRcIixcImZhZGVcIjp0cnVlLFwicm90YXRlXCI6OTB9IiwiMjEiOiJ7XCJpZFwiOjIxLFwibmFtZVwiOlwiUm90YXRlIC05MFwiLFwidHlwZVwiOlwicHJlc2V0XCIsXCJmYWRlXCI6dHJ1ZSxcInJvdGF0ZVwiOi05MH0iLCIyMiI6IntcImlkXCI6MjIsXCJuYW1lXCI6XCIzRCBSb3RhdGUgbGVmdFwiLFwidHlwZVwiOlwicHJlc2V0XCIsXCJmYWRlXCI6dHJ1ZSxcInRyYW5zbGF0ZVhcIjotMjUwLFwicm90YXRlWVwiOjI1MH0iLCIyMyI6IntcImlkXCI6MjMsXCJuYW1lXCI6XCIzRCBSb3RhdGUgcmlnaHRcIixcInR5cGVcIjpcInByZXNldFwiLFwiZmFkZVwiOnRydWUsXCJ0cmFuc2xhdGVYXCI6MjUwLFwicm90YXRlWVwiOi0yNTB9IiwiMjQiOiJ7XCJpZFwiOjI0LFwibmFtZVwiOlwiM0QgUm90YXRlIHRvcFwiLFwidHlwZVwiOlwicHJlc2V0XCIsXCJmYWRlXCI6dHJ1ZSxcInRyYW5zbGF0ZVlcIjotMjUwLFwicm90YXRlWFwiOjE1MH0iLCIyNSI6IntcImlkXCI6MjUsXCJuYW1lXCI6XCIzRCBSb3RhdGUgYm90dG9tXCIsXCJ0eXBlXCI6XCJwcmVzZXRcIixcImZhZGVcIjp0cnVlLFwidHJhbnNsYXRlWVwiOjI1MCxcInJvdGF0ZVhcIjotMTUwfSIsIjI2Ijoie1wiaWRcIjoyNixcIm5hbWVcIjpcIlNrZXcgbGVmdFwiLFwidHlwZVwiOlwicHJlc2V0XCIsXCJmYWRlXCI6dHJ1ZSxcInRyYW5zbGF0ZVhcIjotMjUwLFwic2tld1hcIjotMjV9IiwiMjciOiJ7XCJpZFwiOjI3LFwibmFtZVwiOlwiU2tldyByaWdodFwiLFwidHlwZVwiOlwicHJlc2V0XCIsXCJmYWRlXCI6dHJ1ZSxcInRyYW5zbGF0ZVhcIjoyNTAsXCJza2V3WFwiOjI1fSIsIjI4Ijoie1wiaWRcIjoyOCxcIm5hbWVcIjpcIlNrZXcgdG9wXCIsXCJ0eXBlXCI6XCJwcmVzZXRcIixcImZhZGVcIjp0cnVlLFwidHJhbnNsYXRlWVwiOi0yNTAsXCJza2V3WVwiOi0yNX0iLCIyOSI6IntcImlkXCI6MjksXCJuYW1lXCI6XCJTa2V3IGJvdHRvbVwiLFwidHlwZVwiOlwicHJlc2V0XCIsXCJmYWRlXCI6dHJ1ZSxcInRyYW5zbGF0ZVlcIjoyNTAsXCJza2V3WVwiOi0yNX0iLCIzMCI6IntcImlkXCI6MzAsXCJuYW1lXCI6XCJSb3RhdGUgZnJvbnRcIixcInR5cGVcIjpcInByZXNldFwiLFwiZmFkZVwiOnRydWUsXCJ0cmFuc2xhdGVaXCI6MTUwMCxcInJvdGF0ZVwiOjI1MH0iLCIzMSI6IntcImlkXCI6MzEsXCJuYW1lXCI6XCJSb3RhdGUgYmFja1wiLFwidHlwZVwiOlwicHJlc2V0XCIsXCJmYWRlXCI6dHJ1ZSxcInRyYW5zbGF0ZVpcIjotMTUwMCxcInJvdGF0ZVwiOjI1MH0ifX0=' );

			update_option( 'masterslider_default_options_added', 1 );			
		}


		// check if default buttons are added before or not
		$is_added = get_option( 'masterslider_default_buttons_added', 0 );

		// add caps if they are not already added
		if( ! $is_added || $force_update ) {

			msp_update_option( 'buttons_style', 'eyJtZXRhIjp7IkJ1dHRvblN0eWxlIWlkcyI6Ijg0LDg1LDg2LDg3LDg4LDg5LDkwLDkxLDkyLDkzLDk0LDk1LDk2LDk3LDk4LDk5LDEwMCwxMDEsMTAyLDEwMywxMDQsMTA1LDEwNiwxMDcsMTA4LDEwOSwxMTAsMTExLDExMiwxMTMsMTE0LDExNSwxMTYsMTE3LDExOCwxMTksMTIwLDEyMSwxMjIsMTIzLDEyNCwxMjUsMTI2LDEyNywxMjgsMTI5LDEzMCwxMzEsMTMyLDEzMywxMzQsMTM1LDEzNiwxMzcsMTM4LDEzOSwxNDAsMTQxLDE0MiwxNDMsMTQ0LDE0NSwxNDYsMTQ3LDE0OCwxNDksMTUwLDE1MSwxNTIsMTUzLDE1NCwxNTUsMTU2LDE1NywxNTgiLCJCdXR0b25TdHlsZSFuZXh0SWQiOjE1OX0sIk1TUGFuZWwuQnV0dG9uU3R5bGUiOnsiODQiOiJ7XCJpZFwiOjg0LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi04NFwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjYjk3ZWJiO1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6I2NhODljYztcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1ib3hcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiODUiOiJ7XCJpZFwiOjg1LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi04NVwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjYjk3ZWJiO1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6I2NhODljYztcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCI4NiI6IntcImlkXCI6ODYsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTg2XCIsXCJub3JtYWxcIjpcImNvbG9yOiAjYjk3ZWJiO1xcbmJvcmRlcjpzb2xpZCAxcHggI2I5N2ViYjtcIixcImhvdmVyXCI6XCJib3JkZXItY29sb3I6I2NhODljYztcXG5jb2xvcjojY2E4OWNjXCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiODciOiJ7XCJpZFwiOjg3LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi04N1wiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjYjk3ZWJiO1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6I2NhODljYztcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1jaXJjbGVcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiODgiOiJ7XCJpZFwiOjg4LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi04OFwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjYjk3ZWJiO1xcbmNvbG9yOiAjZmZmO1xcbmJveC1zaGFkb3c6MCA1cHggIzlhNjk5YztcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiNjYTg5Y2M7XFxuYm94LXNoYWRvdzowIDRweCAjOWE2OTljO1xcbnRvcDoxcHg7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcImJveC1zaGFkb3c6MCAycHggIzlhNjk5YztcXG50b3A6M3B4O1wiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCI4OSI6IntcImlkXCI6ODksXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTg5XCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICM1NDcyRDI7XFxuY29sb3I6ICNmZmY7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojNWQ3ZmU5O1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLWJveFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCI5MCI6IntcImlkXCI6OTAsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTkwXCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICM1NDcyRDI7XFxuY29sb3I6ICNmZmY7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojNWQ3ZmU5O1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLXJvdW5kXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjkxIjoie1wiaWRcIjo5MSxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tOTFcIixcIm5vcm1hbFwiOlwiY29sb3I6ICM1NDcyRDI7XFxuYm9yZGVyOnNvbGlkIDFweCAjNTQ3MkQyO1wiLFwiaG92ZXJcIjpcImJvcmRlci1jb2xvcjojNWQ3ZmU5O1xcbmNvbG9yOiM1ZDdmZTlcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCI5MiI6IntcImlkXCI6OTIsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTkyXCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICM1NDcyRDI7XFxuY29sb3I6ICNmZmY7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojNWQ3ZmU5O1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLWNpcmNsZVwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCI5MyI6IntcImlkXCI6OTMsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTkzXCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICM1NDcyRDI7XFxuY29sb3I6ICNmZmY7XFxuYm94LXNoYWRvdzowIDVweCAjNGM2OGJlO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6IzVkN2ZlOTtcXG5ib3gtc2hhZG93OjAgNHB4ICM0YzY4YmU7XFxudG9wOjFweDtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwiYm94LXNoYWRvdzowIDJweCAjNGM2OGJlO1xcbnRvcDozcHg7XCIsXCJzdHlsZVwiOlwibXMtYnRuLXJvdW5kXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjk0Ijoie1wiaWRcIjo5NCxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tOTRcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogIzAwYzFjZjtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiMwMUQ0RTQ7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tYm94XCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjk1Ijoie1wiaWRcIjo5NSxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tOTVcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogIzAwYzFjZjtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiMwMUQ0RTQ7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiOTYiOiJ7XCJpZFwiOjk2LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi05NlwiLFwibm9ybWFsXCI6XCJjb2xvcjogIzAwYzFjZjtcXG5ib3JkZXI6c29saWQgMXB4ICMwMGMxY2Y7XCIsXCJob3ZlclwiOlwiYm9yZGVyLWNvbG9yOiMwMUQ0RTQ7XFxuY29sb3I6IzAxRDRFNFwiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLXJvdW5kXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjk3Ijoie1wiaWRcIjo5NyxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tOTdcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogIzAwYzFjZjtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiMwMUQ0RTQ7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tY2lyY2xlXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjk4Ijoie1wiaWRcIjo5OCxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tOThcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogIzAwYzFjZjtcXG5jb2xvcjogI2ZmZjtcXG5ib3gtc2hhZG93OjAgNXB4ICMwMGFmYmM7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojMDFENEU0O1xcbmJveC1zaGFkb3c6MCA0cHggIzAwYWZiYztcXG50b3A6MXB4O1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJib3gtc2hhZG93OjAgMnB4ICMwMGFmYmM7XFxudG9wOjNweDtcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiOTkiOiJ7XCJpZFwiOjk5LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi05OVwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjNGNhZGM5O1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6IzYzYjJjOTtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1ib3hcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTAwIjoie1wiaWRcIjoxMDAsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTEwMFwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjNGNhZGM5O1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6IzYzYjJjOTtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxMDEiOiJ7XCJpZFwiOjEwMSxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTAxXCIsXCJub3JtYWxcIjpcImNvbG9yOiAjNGNhZGM5O1xcbmJvcmRlcjpzb2xpZCAxcHggIzRjYWRjOTtcIixcImhvdmVyXCI6XCJib3JkZXItY29sb3I6IzYzYjJjOTtcXG5jb2xvcjojNjNiMmM5XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTAyIjoie1wiaWRcIjoxMDIsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTEwMlwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjNGNhZGM5O1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6IzYzYjJjOTtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1jaXJjbGVcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTAzIjoie1wiaWRcIjoxMDMsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTEwM1wiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjNGNhZGM5O1xcbmNvbG9yOiAjZmZmO1xcbmJveC1zaGFkb3c6MCA1cHggIzFhYTJjOTtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiM2M2IyYzk7XFxuYm94LXNoYWRvdzowIDRweCAjMWFhMmM5O1xcbnRvcDoxcHg7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcImJveC1zaGFkb3c6MCAycHggIzFhYTJjOTtcXG50b3A6M3B4O1wiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxMDQiOiJ7XCJpZFwiOjEwNCxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTA0XCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICNjZWMyYWI7XFxuY29sb3I6ICNmZmY7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojY2ViZDlkO1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLWJveFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxMDUiOiJ7XCJpZFwiOjEwNSxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTA1XCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICNjZWMyYWI7XFxuY29sb3I6ICNmZmY7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojY2ViZDlkO1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLXJvdW5kXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjEwNiI6IntcImlkXCI6MTA2LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xMDZcIixcIm5vcm1hbFwiOlwiY29sb3I6ICNjZWMyYWI7XFxuYm9yZGVyOnNvbGlkIDFweCAjY2VjMmFiO1wiLFwiaG92ZXJcIjpcImJvcmRlci1jb2xvcjojY2ViZDlkO1xcbmNvbG9yOiNjZWJkOWRcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxMDciOiJ7XCJpZFwiOjEwNyxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTA3XCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICNjZWMyYWI7XFxuY29sb3I6ICNmZmY7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojY2ViZDlkO1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLWNpcmNsZVwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxMDgiOiJ7XCJpZFwiOjEwOCxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTA4XCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICNjZWMyYWI7XFxuY29sb3I6ICNmZmY7XFxuYm94LXNoYWRvdzowIDVweCAjQzJCN0EyO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6I2NlYmQ5ZDtcXG5ib3gtc2hhZG93OjAgNHB4ICNDMkI3QTI7XFxudG9wOjFweDtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwiYm94LXNoYWRvdzowIDJweCAjQzJCN0EyO1xcbnRvcDozcHg7XCIsXCJzdHlsZVwiOlwibXMtYnRuLXJvdW5kXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjEwOSI6IntcImlkXCI6MTA5LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xMDlcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogIzUwNDg1YjtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiM2YTYxNzY7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tYm94XCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjExMCI6IntcImlkXCI6MTEwLFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xMTBcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogIzUwNDg1YjtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiM2YTYxNzY7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTExIjoie1wiaWRcIjoxMTEsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTExMVwiLFwibm9ybWFsXCI6XCJjb2xvcjogIzUwNDg1YjtcXG5ib3JkZXI6c29saWQgMXB4ICM1MDQ4NWI7XCIsXCJob3ZlclwiOlwiYm9yZGVyLWNvbG9yOiM2YTYxNzY7XFxuY29sb3I6IzZhNjE3NlwiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLXJvdW5kXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjExMiI6IntcImlkXCI6MTEyLFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xMTJcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogIzUwNDg1YjtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiM2YTYxNzY7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tY2lyY2xlXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjExMyI6IntcImlkXCI6MTEzLFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xMTNcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogIzUwNDg1YjtcXG5jb2xvcjogI2ZmZjtcXG5ib3gtc2hhZG93OjAgNXB4ICM0MTJkNWI7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojNmE2MTc2O1xcbmJveC1zaGFkb3c6MCA0cHggIzQxMmQ1YjtcXG50b3A6MXB4O1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJib3gtc2hhZG93OjAgMnB4ICM0MTJkNWI7XFxudG9wOjNweDtcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTE0Ijoie1wiaWRcIjoxMTQsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTExNFwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjOGQ2ZGM0O1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6Izk3N2NjNDtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1ib3hcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTE1Ijoie1wiaWRcIjoxMTUsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTExNVwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjOGQ2ZGM0O1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6Izk3N2NjNDtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxMTYiOiJ7XCJpZFwiOjExNixcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTE2XCIsXCJub3JtYWxcIjpcImNvbG9yOiAjOGQ2ZGM0O1xcbmJvcmRlcjpzb2xpZCAxcHggIzhkNmRjNDtcIixcImhvdmVyXCI6XCJib3JkZXItY29sb3I6Izk3N2NjNDtcXG5jb2xvcjojOTc3Y2M0XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTE3Ijoie1wiaWRcIjoxMTcsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTExN1wiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjOGQ2ZGM0O1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6Izk3N2NjNDtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1jaXJjbGVcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTE4Ijoie1wiaWRcIjoxMTgsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTExOFwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjOGQ2ZGM0O1xcbmNvbG9yOiAjZmZmO1xcbmJveC1zaGFkb3c6MCA1cHggIzdjNTFjNDtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiM5NzdjYzQ7XFxuYm94LXNoYWRvdzowIDRweCAjN2M1MWM0O1xcbnRvcDoxcHg7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcImJveC1zaGFkb3c6MCAycHggIzdjNTFjNDtcXG50b3A6M3B4O1wiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxMTkiOiJ7XCJpZFwiOjExOSxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTE5XCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICM3NWQ2OWM7XFxuY29sb3I6ICNmZmY7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojN2RlNWE3O1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLWJveFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxMjAiOiJ7XCJpZFwiOjEyMCxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTIwXCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICM3NWQ2OWM7XFxuY29sb3I6ICNmZmY7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojN2RlNWE3O1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLXJvdW5kXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjEyMSI6IntcImlkXCI6MTIxLFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xMjFcIixcIm5vcm1hbFwiOlwiY29sb3I6ICM3NWQ2OWM7XFxuYm9yZGVyOnNvbGlkIDFweCAjNzVkNjljO1wiLFwiaG92ZXJcIjpcImJvcmRlci1jb2xvcjojN2RlNWE3O1xcbmNvbG9yOiM3ZGU1YTdcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxMjIiOiJ7XCJpZFwiOjEyMixcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTIyXCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICM3NWQ2OWM7XFxuY29sb3I6ICNmZmY7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojN2RlNWE3O1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLWNpcmNsZVwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxMjMiOiJ7XCJpZFwiOjEyMyxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTIzXCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICM3NWQ2OWM7XFxuY29sb3I6ICNmZmY7XFxuYm94LXNoYWRvdzowIDVweCAjNDFkNjdkO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6IzdkZTVhNztcXG5ib3gtc2hhZG93OjAgNHB4ICM0MWQ2N2Q7XFxudG9wOjFweDtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwiYm94LXNoYWRvdzowIDJweCAjNDFkNjdkO1xcbnRvcDozcHg7XCIsXCJzdHlsZVwiOlwibXMtYnRuLXJvdW5kXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjEyNCI6IntcImlkXCI6MTI0LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xMjRcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogIzIyMjtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiMzMzM7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tYm94XCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjEyNSI6IntcImlkXCI6MTI1LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xMjVcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogIzIyMjtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiMzMzM7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTI2Ijoie1wiaWRcIjoxMjYsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTEyNlwiLFwibm9ybWFsXCI6XCJjb2xvcjogIzIyMjtcXG5ib3JkZXI6c29saWQgMXB4ICMyMjI7XCIsXCJob3ZlclwiOlwiYm9yZGVyLWNvbG9yOiMzMzM7XFxuY29sb3I6IzMzM1wiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLXJvdW5kXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjEyNyI6IntcImlkXCI6MTI3LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xMjdcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogIzIyMjtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiMzMzM7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tY2lyY2xlXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjEyOCI6IntcImlkXCI6MTI4LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xMjhcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogIzIyMjtcXG5jb2xvcjogI2ZmZjtcXG5ib3gtc2hhZG93OjAgNXB4ICMwMDA7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojMzMzO1xcbmJveC1zaGFkb3c6MCA0cHggIzAwMDtcXG50b3A6MXB4O1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJib3gtc2hhZG93OjAgMnB4ICMwMDA7XFxudG9wOjNweDtcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTI5Ijoie1wiaWRcIjoxMjksXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTEyOVwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjZGVkZWRlO1xcbmNvbG9yOiAjNjY2O1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6I2QxZDFkMTtcXG5jb2xvcjogIzY2NjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1ib3hcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTMwIjoie1wiaWRcIjoxMzAsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTEzMFwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjZGVkZWRlO1xcbmNvbG9yOiAjNjY2O1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6I2QxZDFkMTtcXG5jb2xvcjogIzY2NjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxMzEiOiJ7XCJpZFwiOjEzMSxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTMxXCIsXCJub3JtYWxcIjpcImNvbG9yOiAjZGVkZWRlO1xcbmJvcmRlcjpzb2xpZCAxcHggI2RlZGVkZTtcIixcImhvdmVyXCI6XCJib3JkZXItY29sb3I6I2QxZDFkMTtcXG5jb2xvcjojZDFkMWQxXCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTMyIjoie1wiaWRcIjoxMzIsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTEzMlwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjZGVkZWRlO1xcbmNvbG9yOiAjNjY2O1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6I2QxZDFkMTtcXG5jb2xvcjogIzY2NjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1jaXJjbGVcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTMzIjoie1wiaWRcIjoxMzMsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTEzM1wiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjZGVkZWRlO1xcbmNvbG9yOiAjNjY2O1xcbmJveC1zaGFkb3c6MCA1cHggI0NBQ0FDQTtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiNkMWQxZDE7XFxuY29sb3I6ICM2NjY7XFxuYm94LXNoYWRvdzowIDRweCAjQ0FDQUNBO1xcbnRvcDoxcHhcIixcImFjdGl2ZVwiOlwiYm94LXNoYWRvdzowIDJweCAjQ0FDQUNBO1xcbnRvcDozcHg7XCIsXCJzdHlsZVwiOlwibXMtYnRuLXJvdW5kXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjEzNCI6IntcImlkXCI6MTM0LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xMzRcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogI2Y3YmU2ODtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiNlOWIzNjI7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tYm94XCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjEzNSI6IntcImlkXCI6MTM1LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xMzVcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogI2Y3YmU2ODtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiNlOWIzNjI7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTM2Ijoie1wiaWRcIjoxMzYsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTEzNlwiLFwibm9ybWFsXCI6XCJjb2xvcjogI2Y3YmU2ODtcXG5ib3JkZXI6c29saWQgMXB4ICNmN2JlNjg7XCIsXCJob3ZlclwiOlwiYm9yZGVyLWNvbG9yOiNlOWIzNjI7XFxuY29sb3I6I2U5YjM2MlwiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLXJvdW5kXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjEzNyI6IntcImlkXCI6MTM3LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xMzdcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogI2Y3YmU2ODtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiNlOWIzNjI7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tY2lyY2xlXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjEzOCI6IntcImlkXCI6MTM4LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xMzhcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogI2Y3YmU2ODtcXG5jb2xvcjogI2ZmZjtcXG5ib3gtc2hhZG93OjAgNXB4ICNFN0FGNTk7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojZTliMzYyO1xcbmJveC1zaGFkb3c6MCA0cHggI0U3QUY1OTtcXG50b3A6MXB4O1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJib3gtc2hhZG93OjAgMnB4ICNFN0FGNTk7XFxudG9wOjNweDtcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTM5Ijoie1wiaWRcIjoxMzksXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTEzOVwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjNWFhMWUzO1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6IzVmYWFlZjtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1ib3hcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTQwIjoie1wiaWRcIjoxNDAsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTE0MFwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjNWFhMWUzO1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6IzVmYWFlZjtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxNDEiOiJ7XCJpZFwiOjE0MSxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTQxXCIsXCJub3JtYWxcIjpcImNvbG9yOiAjNWFhMWUzO1xcbmJvcmRlcjpzb2xpZCAxcHggIzVhYTFlMztcIixcImhvdmVyXCI6XCJib3JkZXItY29sb3I6IzVmYWFlZjtcXG5jb2xvcjojNWZhYWVmXCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTQyIjoie1wiaWRcIjoxNDIsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTE0MlwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjNWFhMWUzO1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6IzVmYWFlZjtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1jaXJjbGVcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTQzIjoie1wiaWRcIjoxNDMsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTE0M1wiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjNWFhMWUzO1xcbmNvbG9yOiAjZmZmO1xcbmJveC1zaGFkb3c6MCA1cHggIzRjODdiZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiM1ZmFhZWY7XFxuYm94LXNoYWRvdzowIDRweCAjNGM4N2JmO1xcbnRvcDoxcHg7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcImJveC1zaGFkb3c6MCAycHggIzRjODdiZjtcXG50b3A6M3B4O1wiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxNDQiOiJ7XCJpZFwiOjE0NCxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTQ0XCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICM2ZGFiM2M7XFxuY29sb3I6ICNmZmY7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojNzZiOTQxO1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLWJveFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxNDUiOiJ7XCJpZFwiOjE0NSxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTQ1XCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICM2ZGFiM2M7XFxuY29sb3I6ICNmZmY7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojNzZiOTQxO1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLXJvdW5kXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjE0NiI6IntcImlkXCI6MTQ2LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xNDZcIixcIm5vcm1hbFwiOlwiY29sb3I6ICM2ZGFiM2M7XFxuYm9yZGVyOnNvbGlkIDFweCAjNmRhYjNjO1wiLFwiaG92ZXJcIjpcImJvcmRlci1jb2xvcjojNzZiOTQxO1xcbmNvbG9yOiM3NmI5NDFcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxNDciOiJ7XCJpZFwiOjE0NyxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTQ3XCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICM2ZGFiM2M7XFxuY29sb3I6ICNmZmY7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojNzZiOTQxO1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLWNpcmNsZVwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxNDgiOiJ7XCJpZFwiOjE0OCxcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTQ4XCIsXCJub3JtYWxcIjpcImJhY2tncm91bmQtY29sb3I6ICM2ZGFiM2M7XFxuY29sb3I6ICNmZmY7XFxuYm94LXNoYWRvdzowIDVweCAjNWU5MzM0O1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6Izc2Yjk0MTtcXG5ib3gtc2hhZG93OjAgNHB4ICM1ZTkzMzQ7XFxudG9wOjFweDtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwiYm94LXNoYWRvdzowIDJweCAjNWU5MzM0O1xcbnRvcDozcHg7XCIsXCJzdHlsZVwiOlwibXMtYnRuLXJvdW5kXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjE0OSI6IntcImlkXCI6MTQ5LFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xNDlcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogI2Y0NTI0ZDtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiNlMDRiNDc7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tYm94XCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjE1MCI6IntcImlkXCI6MTUwLFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xNTBcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogI2Y0NTI0ZDtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiNlMDRiNDc7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTUxIjoie1wiaWRcIjoxNTEsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTE1MVwiLFwibm9ybWFsXCI6XCJjb2xvcjogI2Y0NTI0ZDtcXG5ib3JkZXI6c29saWQgMXB4ICNmNDUyNGQ7XCIsXCJob3ZlclwiOlwiYm9yZGVyLWNvbG9yOiNlMDRiNDc7XFxuY29sb3I6I2UwNGI0N1wiLFwiYWN0aXZlXCI6XCJ0b3A6MXB4XCIsXCJzdHlsZVwiOlwibXMtYnRuLXJvdW5kXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjE1MiI6IntcImlkXCI6MTUyLFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xNTJcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogI2Y0NTI0ZDtcXG5jb2xvcjogI2ZmZjtcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiNlMDRiNDc7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tY2lyY2xlXCIsXCJzaXplXCI6XCJtcy1idG4tblwifSIsIjE1MyI6IntcImlkXCI6MTUzLFwiY2xhc3NOYW1lXCI6XCJtc3AtcHJlc2V0LWJ0bi0xNTNcIixcIm5vcm1hbFwiOlwiYmFja2dyb3VuZC1jb2xvcjogI2Y0NTI0ZDtcXG5jb2xvcjogI2ZmZjtcXG5ib3gtc2hhZG93OjAgNXB4ICNjYjQ0NDA7XCIsXCJob3ZlclwiOlwiYmFja2dyb3VuZC1jb2xvcjojZTA0YjQ3O1xcbmJveC1zaGFkb3c6MCA0cHggI2NiNDQ0MDtcXG50b3A6MXB4O1xcbmNvbG9yOiAjZmZmO1wiLFwiYWN0aXZlXCI6XCJib3gtc2hhZG93OjAgMnB4ICNjYjQ0NDA7XFxudG9wOjNweDtcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTU0Ijoie1wiaWRcIjoxNTQsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTE1NFwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjZjc5NDY4O1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6I2U3OGE2MTtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1ib3hcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTU1Ijoie1wiaWRcIjoxNTUsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTE1NVwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjZjc5NDY4O1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6I2U3OGE2MTtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0iLCIxNTYiOiJ7XCJpZFwiOjE1NixcImNsYXNzTmFtZVwiOlwibXNwLXByZXNldC1idG4tMTU2XCIsXCJub3JtYWxcIjpcImNvbG9yOiAjZjc5NDY4O1xcbmJvcmRlcjpzb2xpZCAxcHggI2Y3OTQ2ODtcIixcImhvdmVyXCI6XCJib3JkZXItY29sb3I6I2U3OGE2MTtcXG5jb2xvcjojZTc4YTYxXCIsXCJhY3RpdmVcIjpcInRvcDoxcHhcIixcInN0eWxlXCI6XCJtcy1idG4tcm91bmRcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTU3Ijoie1wiaWRcIjoxNTcsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTE1N1wiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjZjc5NDY4O1xcbmNvbG9yOiAjZmZmO1wiLFwiaG92ZXJcIjpcImJhY2tncm91bmQtY29sb3I6I2U3OGE2MTtcXG5jb2xvcjogI2ZmZjtcIixcImFjdGl2ZVwiOlwidG9wOjFweFwiLFwic3R5bGVcIjpcIm1zLWJ0bi1jaXJjbGVcIixcInNpemVcIjpcIm1zLWJ0bi1uXCJ9IiwiMTU4Ijoie1wiaWRcIjoxNTgsXCJjbGFzc05hbWVcIjpcIm1zcC1wcmVzZXQtYnRuLTE1OFwiLFwibm9ybWFsXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiAjZjc5NDY4O1xcbmNvbG9yOiAjZmZmO1xcbmJveC1zaGFkb3c6MCA1cHggI2RhODM1YztcIixcImhvdmVyXCI6XCJiYWNrZ3JvdW5kLWNvbG9yOiNlNzhhNjE7XFxuYm94LXNoYWRvdzowIDRweCAjZGE4MzVjO1xcbnRvcDoxcHg7XFxuY29sb3I6ICNmZmY7XCIsXCJhY3RpdmVcIjpcImJveC1zaGFkb3c6MCAycHggI2RhODM1YztcXG50b3A6M3B4O1wiLFwic3R5bGVcIjpcIm1zLWJ0bi1yb3VuZFwiLFwic2l6ZVwiOlwibXMtYnRuLW5cIn0ifX0=' );
			
			update_option( 'masterslider_default_buttons_added', 1 );			
		}
	}


	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		do_action( 'masterslider_deactivated' );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$locale = apply_filters( 'plugin_locale', get_locale(), MSWP_TEXT_DOMAIN );

		load_textdomain( MSWP_TEXT_DOMAIN, trailingslashit( WP_LANG_DIR ) . MSWP_TEXT_DOMAIN . '/' . MSWP_TEXT_DOMAIN . '-' . $locale . '.mo' );
		load_plugin_textdomain( MSWP_TEXT_DOMAIN, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
	}

}

endif;

function MSP(){ return Master_Slider::get_instance(); } 
MSP();
