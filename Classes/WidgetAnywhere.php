<?php

class tkWidgetAnywhere
{
    public function __construct() 
    {
        add_action('widgets_init', array($this, 'register_widgetanywhere_widgets'));
        
        // add MCE plugin
        add_filter('mce_buttons', array($this, 'widgetanywhere_register_button'));
        add_filter('mce_external_plugins', array($this, 'widgetanywhere_register_tinymce_javascript'));
        
        // enqueue scripts
        wp_enqueue_script('jquery');
        wp_localize_script('jquery', 'widgetanywhere', array('ajaxurl' => admin_url('admin-ajax.php')));
    }
    
    function widgetanywhere_register_button($buttons)
    {
    	array_push($buttons, 'separator', 'widgetanywhere');
    	return $buttons;
    }
    
    function widgetanywhere_register_tinymce_javascript($plugin_array)
    {
    	$plugin_array['widgetanywhere'] = plugins_url('/widget-anywhere.js', __FILE__);
    	return $plugin_array;
    }
    
    function register_widgetanywhere_widgets()
	{
		register_sidebar(array(
			'name' => 'Widget Anywhere',
			'id' => 'widget-anywhere',
			'before_widget' => '',
			'after_widget' => '',
			'before_title' => '',
			'after_title' => '',
			'description' => 'Any widgets you configure here can be inserted into the standard content editor on a page.'
		));
	}
	
	function GetWidgets()
    {
    	global $wp_registered_widgets, $sidebars_widgets;

		// TODO: multiple sidebar registrations as categories (defined by admin)
		$sidebars = ['widget-anywhere'];
		
		foreach ($sidebars as $sidebar)
		{
			$sidebarWidgets = $sidebars_widgets[$sidebar];
			$widgetsWithTitleBySidebar[$sidebar] = array();
			foreach($sidebarWidgets as $widget_id)
			{
				$callback = $wp_registered_widgets[$widget_id]['callback'];
				$number = $wp_registered_widgets[$widget_id]['params'][0]['number'];
				$settings = $callback[0]->get_settings();
				$title = $settings[$number]['title'];
				array_push($widgetsWithTitleBySidebar[$sidebar], array('id'=>$widget_id, 'title'=>$title));
			}
		}
		echo json_encode($widgetsWithTitleBySidebar);
		die();
	}
	
	function shortcode_WidgetAnywhere($params)
	{
		global $wp_registered_widgets, $sidebars_widgets, $wp_registered_sidebars;
		
		$params = shortcode_atts(array(
			'id' => ''
		), $params);
		
		// find the widget in the pool
		//$sidebar_widgets = get_option('sidebars_widgets');
		//$widget_anywhere_widgets = $sidebars_widgets['widget-anywhere'];
		//var_dump($widget_anywhere_widgets);
		
		//$widget_id = $widget_anywhere_widgets[$params['index']];
		//var_dump($widget_anywhere_widgets[$params['index']]);
		
		$widget_id = $params['id'];
		
		// validation
		if (!array_key_exists($widget_id, $wp_registered_widgets)) 
		{
		  return 'Widget ID "'.$widget_id.'" not found.';
		}
    
		// find sidebar 
		foreach($sidebars_widgets as $sidebar => $sidebar_widget)
		{
			foreach($sidebar_widget as $widget)
			{
				if ($widget==$widget_id) $current_sidebar = $sidebar;
			}
		}

		$presentation = (isset($current_sidebar)) ? $wp_registered_sidebars[$current_sidebar] : 
		  array('name' => '', 
				'id' => '',
				'description' => '',
				'class' => '',
				'before_widget'=> '',
				'after_widget'=> '',
				'before_title'=> '',
				'after_title' => '');

		// Clear formatting unless required
		if (!$format) 
		{
			$presentation['before_widget'] = '';
			$presentation['after_widget'] = '';
		}

		$params = array_merge(
			array( array_merge( $presentation, array('widget_id' => $widget_id, 'widget_name' => $wp_registered_widgets[$widget_id]['name']) ) ),
			(array) $wp_registered_widgets[$widget_id]['params']
		);

		// Substitute HTML id and class attributes into before_widget
		$classname_ = '';
		foreach ( (array) $wp_registered_widgets[$widget_id]['classname'] as $cn ) 
		{
			if ( is_string($cn) )
				$classname_ .= '_' . $cn;
			elseif ( is_object($cn) )
				$classname_ .= '_' . get_class($cn);
		}
		$classname_ = ltrim($classname_, '_');
		$params[0]['before_widget'] = sprintf($params[0]['before_widget'], $widget_id, $classname_);

		$params = apply_filters( 'dynamic_sidebar_params', $params ); // doesnt't add/minus from data
	
		$callback = $wp_registered_widgets[$widget_id]['callback'];

		if (is_callable($callback) ) 
		{
			ob_start();
			call_user_func_array($callback, $params);
			$widget_html = ob_get_contents();
			ob_end_clean();
		}
		
		return $widget_html;
	}
}

?>