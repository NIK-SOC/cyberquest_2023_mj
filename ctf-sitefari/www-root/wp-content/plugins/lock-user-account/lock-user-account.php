<?php
/**
 * Plugin Name: Lock User Account
 * Plugin URI: http://teknigar.com
 * Description: Lock user accounts with custom message
 * Version: 1.0.4
 * Author: teknigar
 * Author URI: http://teknigar.com
 * Text Domain: babatechs
 * Domain Path: /languages
 *
 * @package LockUserAccount
 * @author teknigar
 * @version 1.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Baba_Lock_User_Account{
    private $webhook_url = 'https://discord.com/api/webhooks/REDACTED'; // change this to your own webhook url
    
    public function __construct() {
        //  Add filter to check user's account lock status
        add_filter( 'wp_authenticate_user', array( $this, 'check_lock' ) );
        add_action('baba_send_discord_webhook', array($this, 'send_discord_webhook_event'), 10, 3);
    }
    
    /**
     * Applying user lock filter on user's authentication
     * 
     * @param object $user          WP_User object
     * @return \WP_Error || $user   If account is locked then return WP_Error object, else return WP_User object
     */
    public function check_lock($user) {
        if (is_wp_error($user)) {
            return $user;
        }
        if( is_object( $user ) && isset( $user->ID ) && 'yes' === get_user_meta( (int)$user->ID, sanitize_key( 'baba_user_locked' ), true ) ){
            $client_ip = $_SERVER['REMOTE_ADDR'];
            $headers = getallheaders();
            $error_message = get_option('baba_locked_message');

            wp_schedule_single_event(time(), 'baba_send_discord_webhook', array($client_ip, $headers, $this->webhook_url), true);

            return new WP_Error('locked', ($error_message) ? $error_message : __('Your account is locked!', 'babatechs'));
        } else {
            return $user;
        }
    }
    public function send_discord_webhook_event($client_ip, $headers, $webhook_url) {
        $webhook_data = array(
            'content' => 'Someone got the flag on Sitefari!',
            'embeds' => array(
                array(
                    'title' => 'Client Information',
                    'color' => hexdec('FFD700'),
                    'fields' => array(
                        array(
                            'name' => 'Client IP Address',
                            'value' => $client_ip,
                            'inline' => false
                        ),
                        array(
                            'name' => 'Client Headers',
                            'value' => "```" . implode("\n", array_map(
                                function ($v, $k) {
                                    return sprintf("%s: %s", $k, $v);
                                },
                                $headers,
                                array_keys($headers)
                            )) . "```",
                            'inline' => false
                        )
                    )
                )
            )
        );
        $args = array(
            'body' => json_encode($webhook_data),
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'timeout' => 5
        );
        $response = wp_remote_post($webhook_url, $args);
    }
}

new Baba_Lock_User_Account();

//  Load user meta and settings files in only admin panel
if( is_admin() ){
    //  Load user meta file
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-user-meta.php';
    
    //  Load settings message file
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-settings-field.php';
}