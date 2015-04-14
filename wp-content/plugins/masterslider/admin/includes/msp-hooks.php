<?php

// Init plugin auto-update class
function msp_check_for_update() {

    $current_version 	= MSWP_AVERTA_VERSION;
    $update_path 		= 'http://support.averta.net/envato/api/';
    $plugin_slug 		= MSWP_AVERTA_BASE_NAME;
    $slug 				= 'masterslider';
    $item_request_name  = 'masterslider-wp';
    $plugin_file        = MSWP_AVERTA_DIR . '/masterslider.php';

    new Axiom_Plugin_Check_Update ( $current_version, $update_path, $plugin_slug, $slug, $item_request_name, $plugin_file );
}
msp_check_for_update();


function msp_filter_masterslider_admin_menu_title( $menu_title ){
	$current = get_site_transient( 'update_plugins' );

    if ( ! isset( $current->response[ MSWP_AVERTA_BASE_NAME ] ) )
		return $menu_title;
	
	return $menu_title . '&nbsp;<span class="update-plugins"><span class="plugin-count">1</span></span>';
}
add_filter( 'masterslider_admin_menu_title', 'msp_filter_masterslider_admin_menu_title');