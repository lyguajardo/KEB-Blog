<?php 




function mig_cseven_customizer_(){?>
<?php 

$dynamic = get_option('cseven_customizer_admin_options');
echo stripslashes($dynamic['cseven_customizer_version_link']);
?>


<style type="text/css">

	.wpcf7-form {
	font-size:<?php echo $dynamic['cseven_customizer_font_size']?>;
	font-style:<?php echo $dynamic['cseven_customizer_font_style']?>;
	color:<?php echo $dynamic['cseven_customizer_font_color']?>;
	background:<?php echo $dynamic['cseven_customizer_main_background']?>;
	padding:<?php echo $dynamic['cseven_customizer_main_padding']?>;
	float:<?php echo $dynamic['cseven_customizer_main_float']?>;
	width:<?php echo $dynamic['cseven_customizer_main_width']?>;
	}
	
	.wpcf7-form input.wpcf7-text, .wpcf7-form textarea{
	font-size:<?php echo $dynamic['cseven_customizer_font_size_fields']?>;
	font-style:<?php echo $dynamic['cseven_customizer_font_style_fields']?>;
	color:<?php echo $dynamic['cseven_customizer_font_color_fields']?>;
	padding:<?php echo $dynamic['cseven_customizer_fields_padding']?>;
	background-color:<?php echo $dynamic['cseven_customizer_fields_background']?>;
	border-color:<?php echo $dynamic['cseven_customizer_fields_border_color']?>;
	border-radius:<?php echo $dynamic['cseven_customizer_fields_border_radius']?>;
     
	}
	
	.wpcf7-form input.wpcf7-text {
	width:<?php echo $dynamic['cseven_customizer_fields_width']?> ;	
	}
	
	.wpcf7-form textarea{
	width:<?php echo $dynamic['cseven_customizer_textarea_width']?> ;		
	}
	
	.wpcf7-form .wpcf7-form-control.wpcf7-submit {
	font-size:<?php echo $dynamic['cseven_customizer_button_font_size']?>;
	font-style:<?php echo $dynamic['cseven_customizer_button_font_style']?>;
	color:<?php echo $dynamic['cseven_customizer_button_text_color']?>;
	padding:<?php echo $dynamic['cseven_customizer_button_padding']?>;
	background-color:<?php echo $dynamic['cseven_customizer_button_background']?>;
	border-color:<?php echo $dynamic['cseven_customizer_button_border_color']?>;
	border-radius:<?php echo $dynamic['cseven_customizer_button_border_radius']?>;
	background-image: none !important;	
	}
	
	.wpcf7-form .wpcf7-select {
	font-size:<?php echo $dynamic['cseven_customizer_dropdown_font_size']?>;
	font-style:<?php echo $dynamic['cseven_customizer_dropdown_font_style']?>;
	color:<?php echo $dynamic['cseven_customizer_dropdown_text_color']?>;
	padding:<?php echo $dynamic['cseven_customizer_dropdown_padding']?>;
	background-color:<?php echo $dynamic['cseven_customizer_dropdown_background']?>;
	border-color:<?php echo $dynamic['cseven_customizer_button_dropdown_color']?>;
	border-radius:<?php echo $dynamic['cseven_customizer_button_dropdown_radius']?>;
	background-image: none !important;
	border:1px solid;	
	}
	
	.wpcf7-form-linkk {
	text-align: center;
    font-size: 16px;
    height: 1px;
    width: 1px;
    overflow: hidden;
    margin: auto;	
	}
	
</style>	
<?php }

add_action('wp_footer', 'mig_cseven_customizer_');
?>