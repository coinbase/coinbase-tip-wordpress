<?php
/**
 * Plugin Name: Coinbase Tip
 * Plugin URI: http://coinbase.com
 * Description: Add Coinbase Tips to your WordPress site.
 * Version: 1.0
 * Author: Coinbase Inc.
 * Author URI: https://coinbase.com
 * License: GPLv2 or later
 */

/* 

Copyright (C) 2014 Coinbase Inc.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

define('COINBASE_PATH', plugin_dir_path( __FILE__ ));
define('COINBASE_URL', plugins_url( '', __FILE__ ));

class WP_Coinbase {

  private $plugin_path;
  private $plugin_url;
  private $l10n;
  private $wpsf;

  function __construct() {  
    $this->plugin_path = plugin_dir_path( __FILE__ );
    $this->plugin_url = plugin_dir_url( __FILE__ );
    $this->l10n = 'wp-settings-framework';

    add_action( 'admin_menu', array(&$this, 'admin_menu'), 99 );
    add_action( 'admin_init', array(&$this, 'admin_init'), 99 );
    add_action('admin_enqueue_scripts', array(&$this, 'admin_styles'), 1);

    // Include and create a new WordPressSettingsFramework
    require_once( $this->plugin_path .'wp-settings-framework.php' );
    $this->wpsf = new WordPressSettingsFramework( $this->plugin_path .'settings/coinbase.php' );

    add_shortcode('coinbase_button', array(&$this, 'shortcode'));

    add_filter('the_content', array(&$this, 'coinbase_content_filter'));
  }

  function admin_menu() {
    add_submenu_page( 'options-general.php', __( 'Coinbase Tip', $this->l10n ), __( 'Coinbase Tip', $this->l10n ), 'update_core', 'coinbase', array(&$this, 'settings_page') );
  }

  function admin_init() {
    register_setting ( 'coinbase', 'coinbase-tokens' );
  }

  function settings_page() {
    ?>
      <div class="wrap">
        <h2 style="font-size: 32px;"><img src="https://www.coinbase.com/favicon.ico" height="24px" width="24px" /> Coinbase Tip</h2>
        <h3><a href="https://www.coinbase.com/signup">Need a Coinbase Account?</a></h3><br />
    <?php
        $this->wpsf->settings();
    ?>
      </div>
    <?php
  }

  function shortcode( $atts, $content = null ) {
    $button = create_coinbase_button();
    return $button;
  }

  function coinbase_content_filter( $content ) {
    $autoembed_enable = wpsf_get_setting( 'coinbase', 'autoembed', 'enable' );
    $autoembed_type = wpsf_get_setting( 'coinbase', 'autoembed', 'type' );

    $userinfo = wpsf_get_setting( 'coinbase', 'general', 'userinfo' );

      if(is_single()) {
        if ($autoembed_enable == 1) { // autoembed is enabled, so lets output it..
          if ($autoembed_type == 'top') {
            $content = $this->create_coinbase_button($userinfo) . $content;
          } else if ($autoembed_type == 'bottom') {
            $content = $content .  $this->create_coinbase_button($userinfo);
          } else if ($autoembed_type == 'top_bottom') {
            $content = $this->create_coinbase_button($userinfo) . $content .  $this->create_coinbase_button($userinfo);
          } else {
            $content = $content;
          }
        }
      }
    return $content;
  }

  public function admin_styles() {
    wp_enqueue_style( 'coinbase-admin-styles', COINBASE_URL .'/css/coinbase-admin.css', array(), '1', 'all' );
  }

  public function create_coinbase_button( $info ) {
    $return = '';

    if ( isset($info) ) {
      $return = '<div class="cb-tip-button" data-content-location="" data-href="//www.coinbase.com/tip_buttons/show_tip" data-to-user-id="'.$info.'" data-src="wp-plugin"></div><script>!function(d,s,id) {var js,cjs=d.getElementsByTagName(s)[0],e=d.getElementById(id);if(e){return;}js=d.createElement(s);js.id=id;js.src="https://www.coinbase.com/assets/tips.js";cjs.parentNode.insertBefore(js,cjs);}(document, \'script\', \'coinbase-tips\');</script>';
    } else {
      $return = "The Coinbase-WP plugin has not been properly set up - please visit the Coinbase settings page in your administrator console.";
    }

    return $return;
  }

}
new WP_Coinbase();

?>