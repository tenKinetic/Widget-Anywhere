<?php

/*
	Plugin Name: Widget Anywhere
	Plugin URI: http://tenkinetic.com/widget-anywhere
	Description: Display a widget anywhere by using a shortcode to select it from the widget anywhere pool
	Author: tenKinetic
	Version: 0.1.0
	Author URI: http://tenkinetic.com
	Text Domain: tenkinetic-widget-anywhere
	Domain Path: /lang
 */

include 'Classes/WidgetAnywhere.php';

if(session_id() == '') session_start();

class tkWidgetAnywherePlugin
{
	protected $pluginPath;
    protected $pluginUrl;
    
    protected $WidgetAnywhere;
    
    public function __construct()
    {
    	add_action('admin_init', array($this, 'check_version'));

        // Don't run anything else in the plugin, if we're on an incompatible WordPress version
        if (!self::compatible_version()) return;
        
    	$this->WidgetAnywhere = new tkWidgetAnywhere;
        
        // Add shortcode support for widgets
	    add_filter('widget_text', 'do_shortcode');
	    
	    // Add the Widget Anywhere shortcode
	    add_shortcode('widget-anywhere', array($this->WidgetAnywhere, 'shortcode_WidgetAnywhere'));
	    
	    // Add the AJAX used to get all WidgetAnywhere widgets
	    add_action('wp_ajax_WidgetAnywhere_GetWidgets', array($this->WidgetAnywhere, 'GetWidgets'));
	    add_action('wp_ajax_nopriv_WidgetAnywhere_GetWidgets', array($this->WidgetAnywhere, 'GetWidgets'));
	}
	
	static function compatible_version() 
	{
        if (version_compare($GLOBALS['wp_version'], '4.1.2', '<')) return false;
        // Add sanity checks for other version requirements here
        return true;
    }
    
    // The primary sanity check, automatically disable the plugin on activation if it doesn't
    // meet minimum requirements.
    static function activation_check() 
    {
        if (!self::compatible_version())
        {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('Widget Anywhere requires WordPress 4.1.2 or higher.', 'widget-anywhere'));
        }
    }

    // The backup sanity check, in case the plugin is activated in a weird way,
    // or the versions change after activation.
    function check_version() 
    {
        if (!self::compatible_version())
        {
            if (is_plugin_active(plugin_basename(__FILE__))) 
            {
                deactivate_plugins(plugin_basename(__FILE__));
                add_action('admin_notices', array($this, 'disabled_notice'));
                if (isset($_GET['activate'])) 
                {
                    unset($_GET['activate']);
                }
            }
        }
    }
    
    function disabled_notice() 
    {
       echo '<strong>'.esc_html__('Widget Anywhere requires WordPress 4.1.2 or higher!', 'widget-anywhere').'</strong>';
    } 
}

global $tkWidgetAnywherePlugin;
$tkWidgetAnywherePlugin = new tkWidgetAnywherePlugin;
register_activation_hook(__FILE__, array('tkWidgetAnywherePlugin', 'activation_check'));

?>