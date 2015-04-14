<?php

  
  require_once("admin-page-class/admin-options.php");
  

  $config = array(    
		'menu'=> array('cseven_customizer', 'cseven_customizer_options'),      
		'page_title' => __('Contact Form 7 Customizer','apc'),     
		'capability' => 'edit_themes',   
		'option_group' => 'cseven_customizer_admin_options',      
		'id' => 'cseven_admin_page',      
		'fields' => array(),            
		'local_images' => false,          
		'use_with_theme' => false,
		'position' => 134,
  );  
  
  

  $options_panel = new BF_Admin_Page_Class($config);
  $options_panel->OpenTabs_container('');
  
  
  $options_panel->TabsListing(array(
    'links' => array(
	'main_options' => __('Main Options', 'apc'),
	'typography_options' => __('Typography Options', 'apc'),
	'fields_options' => __('Fields Options', 'apc'),
	'button_options' => __('Button Options', 'apc'),
	'dropdown_options' => __('Dropdown Menu Options', 'apc'),
    )
  ));
  
  $options_panel->OpenTab('main_options');
  $options_panel->Title(__("Main Options","apc"));
  $options_panel->addText('cseven_customizer_main_width', array('name'=> __('Main Form Width','apc'), 'std'=> '96%', 'desc' => __('You can use pixels or percents; Ex: 300px or 30%','apc')));
  $options_panel->addColor('cseven_customizer_main_background',array('name'=> __('Main Background Color','apc'), 'std'=> '#eaeaea', 'desc' => __('','apc')));
  $options_panel->addSelect('cseven_customizer_main_float',array('left' => 'Left', 'right' => 'Right', 'none' => 'None'),array('name'=> __('Align Form to left or right?','apc'), 'std'=> array('left'), 'desc' => __('','apc')));
  $options_panel->addSelect('cseven_customizer_main_padding',array('1%' => '1%', '2%' => '2%', '3%' => '3%', '4%' => '4%', '5%' => '5%', '6%' => '6%', '7%' => '7%'),array('name'=> __('Main Padding','apc'), 'std'=> array('2%'), 'desc' => __('Distance Between fields and main form container','apc')));
  $options_panel->CloseTab();
  

   
  $options_panel->OpenTab('typography_options');
  $options_panel->Title(__("Typography Options","apc"));
  $options_panel->addSelect('cseven_customizer_font_size',$migpercentsizes,array('name'=> __('Main Font Size','apc'), 'std'=> array('100%'), 'desc' => __('Percent related to the original theme font size','apc')));
  $options_panel->addSelect('cseven_customizer_font_style',array('normal' => 'normal', 'italic' => 'italic'),array('name'=> __('Main Font Style','apc'), 'std'=> array('normal'), 'desc' => __('Percent related to the original theme font size','apc')));
  $options_panel->addColor('cseven_customizer_font_color',array('name'=> __('Main Font Color','apc'), 'std'=> array('#666666'), 'desc' => __('','apc')));
  $options_panel->addSelect('cseven_customizer_font_size_fields',$migpercentsizes,array('name'=> __('Fields Font Size','apc'), 'std'=> array('80%'), 'desc' => __('Percent related to the original theme font size','apc')));
  $options_panel->addColor('cseven_customizer_font_color_fields',array('name'=> __('Fields Font Color','apc'), 'std'=> array('#aaaaaa'), 'desc' => __('','apc')));
  $options_panel->addSelect('cseven_customizer_font_style_fields',array('normal' => 'normal', 'italic' => 'italic'),array('name'=> __('Fields Font Style','apc'), 'std'=> array('italic'), 'desc' => __('','apc')));
  $options_panel->CloseTab();
  
 

  $options_panel->OpenTab('fields_options');
  $options_panel->Title(__("Fields Options","apc"));
  $options_panel->addText('cseven_customizer_fields_width', array('name'=> __('Text Fields Width','apc'), 'std'=> '', 'desc' => __('You can use pixels or percents; Ex: 300px or 30%','apc')));
  $options_panel->addText('cseven_customizer_textarea_width', array('name'=> __('Text Area Width','apc'), 'std'=> '', 'desc' => __('You can use pixels or percents; Ex: 300px or 30%','apc')));
  $options_panel->addSelect('cseven_customizer_fields_padding',array('0px' => '0px', '5px' => '5px', '10px' => '10px', '15px' => '15px', '20px' => '20px', '25px' => '25px', '30px' => '39px', ),array('name'=> __('Fields Padding','apc'), 'std'=> array('10px'), 'desc' => __('Distance Between typo and fields borders','apc')));
  $options_panel->addColor('cseven_customizer_fields_background',array('name'=> __('Fields Background Color','apc'), 'std'=> array('#eaeaea'), 'desc' => __('','apc')));
  $options_panel->addColor('cseven_customizer_fields_border_color',array('name'=> __('Fields Border Color','apc'), 'std'=> array('#bababa'), 'desc' => __('','apc')));
  $options_panel->addSelect('cseven_customizer_fields_border_radius',array('0px' => '0px', '5px' => '5px', '10px' => '10px', '15px' => '15px', '20px' => '20px', '25px' => '25px', '30px' => '30px', ),array('name'=> __('Fields Border Radius','apc'), 'std'=> array('5px'), 'desc' => __('Corner Radius','apc')));
  $options_panel->CloseTab();
  
  $options_panel->OpenTab('button_options');
  $options_panel->Title(__("Button Options","apc"));
  $options_panel->addSelect('cseven_customizer_button_font_size',$migpercentsizes,array('name'=> __('Button Font Size','apc'), 'std'=> array('120%'), 'desc' => __('Percent related to the original theme font size','apc')));
  $options_panel->addSelect('cseven_customizer_button_font_style',array('normal' => 'normal', 'italic' => 'italic'),array('name'=> __('Button Font Style','apc'), 'std'=> array('italic'), 'desc' => __('','apc')));
  $options_panel->addColor('cseven_customizer_button_background',array('name'=> __('Button Background Color','apc'), 'std'=> array('#444444'), 'desc' => __('','apc')));
  $options_panel->addColor('cseven_customizer_button_text_color',array('name'=> __('Button Text Color','apc'), 'std'=> array('#ffffff'), 'desc' => __('','apc')));
  $options_panel->addColor('cseven_customizer_button_border_color',array('name'=> __('Button Border Color','apc'), 'std'=> array('#000000'), 'desc' => __('','apc')));
  $options_panel->addSelect('cseven_customizer_button_padding',array('0px' => '0px', '5px' => '5px', '10px' => '10px', '15px' => '15px', '20px' => '20px', '25px' => '25px', '30px' => '30px', ),array('name'=> __('Button Padding','apc'), 'std'=> array('10px'), 'desc' => __('Distance Between typo and fields borders','apc')));
  $options_panel->addSelect('cseven_customizer_button_border_radius',array('0px' => '0px', '5px' => '5px', '10px' => '10px', '15px' => '15px', '20px' => '20px', '25px' => '25px', '30px' => '30px', ),array('name'=> __('Button Border Radius','apc'), 'std'=> array('5px'), 'desc' => __('Corner Radius','apc')));
  $options_panel->CloseTab();
  
  $options_panel->OpenTab('dropdown_options');
  $options_panel->Title(__("Dropdown Menu Options","apc"));
  $options_panel->addSelect('cseven_customizer_dropdown_font_size',$migpercentsizes,array('name'=> __('Dropdown Menu Font Size','apc'), 'std'=> array('120%'), 'desc' => __('Percent related to the original theme font size','apc')));
  $options_panel->addSelect('cseven_customizer_dropdown_font_style',array('normal' => 'normal', 'italic' => 'italic'),array('name'=> __('Dropdown Menu Font Style','apc'), 'std'=> array('italic'), 'desc' => __('','apc')));
  $options_panel->addColor('cseven_customizer_dropdown_background',array('name'=> __('Dropdown Menu Background Color','apc'), 'std'=> array('#444444'), 'desc' => __('','apc')));
  $options_panel->addColor('cseven_customizer_dropdown_text_color',array('name'=> __('Dropdown Menu Text Color','apc'), 'std'=> array('#ffffff'), 'desc' => __('','apc')));
  $options_panel->addColor('cseven_customizer_dropdown_border_color',array('name'=> __('Dropdown Menu Border Color','apc'), 'std'=> array('#666666'), 'desc' => __('','apc')));
  $options_panel->addSelect('cseven_customizer_dropdown_padding',array('0px' => '0px', '5px' => '5px', '10px' => '10px', '15px' => '15px', '20px' => '20px', '25px' => '25px', '30px' => '30px', ),array('name'=> __('Dropdown Menu Padding','apc'), 'std'=> array('5px'), 'desc' => __('Distance Between typo and fields borders','apc')));
  $options_panel->addSelect('cseven_customizer_dropdown_border_radius',array('0px' => '0px', '5px' => '5px', '10px' => '10px', '15px' => '15px', '20px' => '20px', '25px' => '25px', '30px' => '30px', ),array('name'=> __('Dropdown Menu Border Radius','apc'), 'std'=> array('5px'), 'desc' => __('Corner Radius','apc')));
  $options_panel->addCode('cseven_customizer_version_link', array('name'=> __('version 1.02','apc'), 'std'=> '<h4 class="wpcf7-form-linkk"><a href="http://themerewards.com">newspaper templates - theme rewards</a></h4>', 'desc' => __('')));
  $options_panel->CloseTab();