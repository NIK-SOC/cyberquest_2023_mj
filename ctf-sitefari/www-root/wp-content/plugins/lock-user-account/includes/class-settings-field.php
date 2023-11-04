<?php

/* 
 * Contains functions and definations for settings field
 * 
 * @package LockUserAccount
 * @author babaTechs
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Baba_Locked_Message_Settings{
    
    public function __construct() {
        //  Add action to add settings field to get input message for error display on locked account login
        add_action( 'admin_init', array( $this, 'register_settings_section' ) );
        
        //  Add support link into plugin's actions links
        add_filter( 'plugin_action_links_' . plugin_basename( 'lock-user-account/lock-user-account.php' ), array( $this, 'support_feedback_link' ) );
    }
    
    /**
     * Initializing field and section in general settings page in admin panel
     * This is generating input field to get error message from admin
     */
    public function register_settings_section(){
        //  Add settings field to store option
        register_setting( 'general', 'baba_locked_message', 'sanitize_text_field' ); 
        
        //  Add settings section to show on general settings page
        add_settings_section(
                'baba_locked_message_section',
                esc_html__( 'Lock User Account', 'babatechs' ),
                array( $this, 'settings_section_callback' ),
                'general'
        );
        
        //  Add settings field to show in section of settings on general settings page
        add_settings_field( 
                'baba_locked_message',
                esc_html__( 'Locked User Message', 'babatechs' ),
                array( $this, 'settings_field_callback' ),
                'general',
                'baba_locked_message_section',
                array( 'label_for' => 'baba_locked_message' ) 
        );
    }
    
    /**
     * Call back function of settings section
     * 
     * @param array $args   Array of parameters provided to add settings section
     */
    public function settings_section_callback( $args ){
        printf( '<p>%1$s</p>', esc_html__( 'This section add by Lock User Account plugin.', 'babatechs' ) );
    }
    
    /**
     * Call back function of field for settings section
     * Showing input field to get input from admin to store error message
     * 
     * @param array $args   Array of paremters provided to register settings field
     */
    public function settings_field_callback( $args ){
        
        //  Show input field to get input from admin
        printf( '<input type="text" name="%1$s" id="%2$s" aria-describedby="baba-locked-message-description" value="%3$s" class="regular-text ltr">', esc_attr( $args['label_for'] ), esc_attr( $args['label_for'] ), esc_attr( get_option( 'baba_locked_message' ) ) );
        
        //  Show description under input field
        printf( '<p class="description" id="baba-locked-message-description">%1$s</p>', esc_html__( 'Please enter message to show on login screen in case of locked account', 'babatechs' ) );
    }
    
    /**
     * Adding support link under plugin's description
     * 
     * @param array $links  Links about plugin
     * @return array        Links with support link
     */
    public function support_feedback_link( $links ){
        $support_links = array(
                        sprintf( '<a href="mailto:%1$s">%2$s</a>', sanitize_email( 'mejidev@gmail.com' ), esc_html__( 'Support', 'babatechs' ) ),
                    );
        return array_merge( $links, $support_links );
    }
}

new Baba_Locked_Message_Settings();