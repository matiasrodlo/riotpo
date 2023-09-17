<?php
/**
 * @package Facebook_Popup_Like_Box
 */
/*
Plugin Name: Facebook Popup Like Box
Plugin URI: 
Description: Show an enticing Facebook Popup Box on your WordPress site.
Version: 1.3
Author: Vikas Singhal (tB3)
Author URI: https://www.xtendify.com/en/user/3-ViSinghal
License: GPLv2 or later
*/

/*  Copyright 2014  Vikas Singhal  (email : contact@tb3.co.in)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//adding actual codes to the bottom
function facebookpopup_footer() {
   
    //$footer = file_get_contents(dirname(__FILE__).'/facebookpopup_footer.php');
    include(dirname(__FILE__).'/facebookpopup_footer.php');
    $footer = $fb_footer;
    $footer = str_replace('__URL__', get_option('facebookpopup_fburl'), $footer);
    $footer = str_replace('__DELAY__', get_option('facebookpopup_delay')*1000, $footer);
    echo $footer;
}
add_action('wp_footer', 'facebookpopup_footer');

//add default options

function facebookpopup_set_up_options() {
    add_option('facebookpopup_fburl', 'Xtendify');
    add_option('facebookpopup_delay', 10);
    
    register_setting( 'facebookpopup_settings_group', 'facebookpopup_fburl' );
    register_setting( 'facebookpopup_settings_group', 'facebookpopup_delay' );
}

//adding FB scripts

function frontend_scripts() {
    wp_enqueue_script( 'facebookpopup_script', plugins_url( 'js/facebookpopup.js' , __FILE__ ), array('jquery'));
    wp_enqueue_style( 'facebookpopup_style', plugins_url( 'css/style.css' , __FILE__ ));
}

add_action( 'wp_enqueue_scripts', 'frontend_scripts');


//Adding settings link 
function facebook_settings_link( $links ) {
    $settings_link = '<a href="'.admin_url( 'admin.php?page=facebookpopup_settings' ).'">Settings</a>';
  	array_push( $links, $settings_link );
  	return $links;
}

$plugin = plugin_basename( __FILE__ );

add_filter( "plugin_action_links_$plugin", 'facebook_settings_link' );


//adding a dummy page
function facebookpopup_settings_menu() {
    add_submenu_page( 
          null          
        , 'Facebook Popup settings'   
        , 'Facebook Popup settings'  
        , 'administrator' 
        , 'facebookpopup_settings'   
        , 'facebookpopup_display_settings' 
    );
    add_action('admin_init', 'facebookpopup_set_up_options');
    
}


add_action('admin_menu', 'facebookpopup_settings_menu');




function facebookpopup_display_settings() {
    
    
?>
<div class="wrap" style="float:left; width:70%">
<h2>Facebook Popup settings </h2>
<form method="post" action="options.php">
    <?php settings_fields( 'facebookpopup_settings_group' ); ?>
    <?php do_settings_sections( 'facebookpopup_settings_group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Facebook Page</th>
        <td>http://www.facebook.com/<input type="text" name="facebookpopup_fburl" value="<?php echo get_option('facebookpopup_fburl'); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Delay in showing popup</th>
        <td><input type="text" name="facebookpopup_delay" value="<?php echo get_option('facebookpopup_delay'); ?>" /> secs</td>
        </tr>
        
    </table>
    
    <?php submit_button(); ?>

</form>

</div>
<div >
    <br>
    <a style="text-decoration:underline" target="_blank" href="https://www.xtendify.com/en/product/84-Facebook_Popup_Like_Box_Pro_for_WordPress">Buy the PRO version</a>
                  
    <br>Features:<br>
                    <ul>
                    <li>- Choose from light or dark theme. </li>
<li>- Responsive design</li>
<li>- Load 'lite' version on mobile devices</li>
<li>- Change popup revisit duration</li>
<li>- Close popup when \'Like\' button is clicked</li>
<li>- No Ads</li>
</ul>
                        </div>
<?php } ?>
