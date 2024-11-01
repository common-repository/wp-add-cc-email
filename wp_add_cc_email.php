<?php
/*
Plugin Name: WP Add CC Email
Plugin URI: http://wpable.io
Description: Allow admin add new multiple cc email via dashbard then  all emails sent to admin will be added cc to list cc email.
Version: 1.0
Author: wpable.io
Author URI: http://wpable.io
*/
Class wa_add_cc_mail_setting{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     *construction class
     */
    public function __construct()  {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_filter("plugin_action_links_".plugin_basename(__FILE__), array($this,'add_quick_setting_link') );
    }
    function add_quick_setting_link($links) {
        $settings_link = '<a href="options-general.php?page=wp-cc-mail-settings">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Add options page
     */
    public function add_plugin_page()  {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'Add CC Email',
            'manage_options',
            'wp-cc-mail-settings',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
        // Set class property
        $this->options = get_option( 'wa_option_name' );
        ?>
        <div class="wrap">
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'wa_option_group' );
                do_settings_sections( 'wp-cc-mail-settings' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()   {
        register_setting(
            'wa_option_group', // Option group
            'wa_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Add CC Email Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'wp-cc-mail-settings' // Page
        );


        add_settings_field(
            'cc_email',
            'CC Email',
            array( $this, 'cc_email_callback' ),
            'wp-cc-mail-settings',
            'setting_section_id'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input ) {
        $new_input = array();

        if( isset( $input['cc_email'] ) )
            $new_input['cc_email'] = sanitize_text_field( $input['cc_email'] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info() {
        print 'Enter your settings below:';
    }
    /**
     * Get the settings option array and print one of its values
     */
    public function id_number_callback(){
        printf(
            '<input type="text" id="id_number" name="wa_option_name[id_number]" value="%s" />',
            isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function cc_email_callback() {
        printf(
            '<input type="text" id="cc_email" class="regular-text" name="wa_option_name[cc_email]" value="%s" /> <br /> <span> <i>you can add mutiple cc by comma charater</i></span>',

            isset( $this->options['cc_email'] ) ? esc_attr( $this->options['cc_email']) : ''
        );
    }
}

if( is_admin() )
	new wa_add_cc_mail_setting();

class wa_add_cc_email_front{

    function __construct(){
         add_filter( 'wp_mail', array( $this,'wa_add_cc_mail')) ;
    }

    function wa_add_cc_mail($args){

        $wa_options =  get_option( 'wa_option_name' );
        if( !empty($wa_options) && isset( $wa_options['cc_email'] ) ){

            $cc_mail = $wa_options['cc_email'];

            if( is_array( $args['headers']) ) {
                $args['headers']['Cc'] = $cc_mail;
            } else {
                $args['headers'] .= 'Cc: '.$cc_mail;
            }
        }
        return $args;
    }
}
new wa_add_cc_email_front();