<?php
/**
 * Plugin Name: ACF Local JSON Manager
 * Description: Manages plugins that use ACF Local JSON
 * Version: 1.2
 * Author: khromov
 * GitHub Plugin URI: khromov/acf-local-json-manager
 */

/**
 * Class ACF_Local_JSON_Manager
 */
class ACF_Local_JSON_Manager {
  function __construct() {
    add_action('admin_bar_menu', array(&$this, 'admin_bar'), 31);
    add_action('admin_head', array($this, 'admin_css'));

    //Init action
    add_action('init', array($this, 'init'));

    //Override Local JSON location
    add_action('acf/settings/save_json',  array($this, 'override_json_location'), apply_filters('aljm_override_priority', 9999));
  }

  /**
   * Initialization function
   */
  function init()
  {
    //Registers GET listener to toggle setting
    $this->register_endpoints();

    //TODO: Register acf/settings/save_json at very late priority
  }

  /**
   * Adds a menu item to the admin bar via the admin_bar_menu hook
   *
   * @param $wp_admin_bar WP_Admin_Bar object
   */
  function admin_bar($wp_admin_bar)
  {
    //We're in admin and this is not a WPML install
    if(is_admin() && current_user_can('manage_options') === true) {

      $top_id = 'acf-local-json-manager-icon-' . ($this->override_activated() ? 'active' : 'inactive');

      //Add main menu
      $main_bar = array(
        'id' => $top_id,
        'title' => 'Local JSON' . ($this->override_activated() ? ': ' . esc_html(get_option('aljm_current_plugin')) : ''),
        'href' => '#',
        'meta' => array(
          'class' => 'acf-local-json-manager'
        )
      );

      //List all submenus
      $submenus = array();

      foreach(apply_filters('aljm_save_json', array()) as $plugin => $path) {

        $submenus[] = array(
          'id' => 'acf-local-json-manager-'. esc_attr($plugin),
          'title' => esc_html($plugin),
          'href' => $this->build_switch_url($plugin),
          'parent' => $top_id,
          'meta' => array(
            'target' => '_self'
          )
        );
      }

      //Add sub menu
      if($this->override_activated()) {
        $submenus[] = array(
          'id' => 'acf-local-json-manager-disable',
          'title' => 'Disable overrides',
          'href' => $this->build_switch_url('_none'),
          'parent' => $top_id,
          'meta' => array(
            'target' => '_self'
          )
        );
      }

      $wp_admin_bar->add_node($main_bar);

      foreach($submenus as $submenu) {
        $wp_admin_bar->add_node($submenu);
      }
    }
  }

  /**
   * Builds URL to switch between plugins
   *
   * @param $plugin_slug
   *
   * @return string|void
   */
  function build_switch_url($plugin_slug) {
    return admin_url('?aljm_select_plugin=' . urlencode_deep($plugin_slug). '&aljm_nonce=' . wp_create_nonce( 'aljm' ) . '&aljm_return_url=' . urlencode_deep((is_ssl() ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]));
  }

  /**
   * Adds a little icon to the admin bar for later WordPress versions
   */
  function admin_css()
  {
    ?>
      <style type="text/css">
        #wpadminbar #wp-admin-bar-acf-local-json-manager-icon-inactive > .ab-item:before,
        #wpadminbar #wp-admin-bar-acf-local-json-manager-icon-active > .ab-item:before
        {
          content: "\f240";
          top: 2px;
        }

        #wpadminbar #wp-admin-bar-acf-local-json-manager-icon-active > .ab-item:before
        {
          color: #40ff00;
        }

        #wpadminbar #wp-admin-bar-acf-local-json-manager-icon-inactive > .ab-item:before
        {
          color: #888;
        }

        #wpadminbar #wp-admin-bar-acf-local-json-manager-disable a {
          font-weight: bold !important;
        }
      </style>
    <?php
  }

  /**
   * Whether the plugin is currently active or not
   * @return bool
   */
  function override_activated() {
    return get_option('aljm_current_plugin') ? true : false;
  }

  /**
   * This plugin listens for the GET variable that toggles the current setting
   */
  function register_endpoints()
  {
    //We're in admin
    if(is_admin() && current_user_can('manage_options') && isset($_GET['aljm_nonce']) && wp_verify_nonce( $_GET['aljm_nonce'], 'aljm')) {
      //Is the GET variable set?
      if(isset($_GET['aljm_select_plugin'])) {

        $new_option = ($_GET['aljm_select_plugin'] === '_none' ? '' : $_GET['aljm_select_plugin']);

        update_option('aljm_current_plugin', $new_option);

        if(isset($_GET['aljm_return_url'])) {
          wp_redirect(urldecode($_GET['aljm_return_url']));
        }
        else {
          wp_redirect(admin_url());
        }
      }
    }
  }

  /**
   * Override the final ACF Local JSON path
   *
   * @param $path
   *
   * @return mixed
   */
  function override_json_location($path) {

    //Get current override
    $override_plugin = get_option('aljm_current_plugin');

    //Get registered plugins
    $plugins = apply_filters('aljm_save_json', array());

    //If override plugin is enabled, look for it in the registered plugins array and set it
    if($override_plugin && isset($plugins[$override_plugin])) {
      return $plugins[$override_plugin];
    }

    //Fallback
    return $path;
  }
}

$acf_local_json_manager = new ACF_Local_JSON_Manager();