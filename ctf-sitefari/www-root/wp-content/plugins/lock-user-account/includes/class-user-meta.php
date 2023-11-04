<?php

/* 
 * Contains functions and definations for user meta
 * 
 * @package LockUserAccount
 * @author babaTechs
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Baba_User_Meta{
    
    public function __construct() {
        //  Add filter to add another action in users' bulk action dropdown
        add_filter( 'bulk_actions-users', array( $this, 'register_bulk_action' ) );
        
        //  Add filter to add another column header in users' listing
        add_filter( 'manage_users_columns' , array( $this, 'register_column_header' ) );
        
        //  Add filter to show output of custom column in users' listing
        add_filter( 'manage_users_custom_column', array( $this, 'output_column' ), 10, 3 );
        
        //  Add action to process bulk action request
        add_action( 'admin_init', array( $this, 'process_lock_action' ) );
    }
    
    /**
     * Add another action in bulk action drop down list on users listing screen
     * 
     * @param array $actions    Array of users bulk actions
     * @return array            Array with addition of Lock action
     */
    public function register_bulk_action( $actions ){
        $actions['lock'] = esc_html__( 'Lock', 'babatechs' );
        $actions['unlock'] = esc_html__( 'Unlock', 'babatechs' );
        return $actions;
    }
    
    /**
     * Add another column header in listing of users
     * 
     * @param array $columns    Array of columns headers
     * @return array            Array with adition of Locked column
     */
    public function register_column_header( $columns ){
        return array_merge( $columns, 
              array( 'locked' => esc_html__( 'Locked', 'babatechs' ) ) );
    }
    
    /**
     * Displaying status of user's account in list of users for Locked column
     * 
     * @param string $output        Output value of custom column
     * @param string $column_name   Column name
     * @param int $user_id          ID of user
     * @return string               Output value of custom column
     */
    public function output_column( $output, $column_name, $user_id ){
        if( 'locked' !== $column_name ) return $output;
        $locked = get_user_meta( $user_id, sanitize_key( 'baba_user_locked' ), true );
        return ( 'yes' === $locked ) ? __( 'Locked', 'babatechs' ) : __( 'Not Locked', 'babatechs' );
    }
    
    /**
     * Processing Lock and Unlock users on request of bulk action
     */
    public function process_lock_action(){
        
        if ( isset( $_GET['_wpnonce'] ) && ! empty( $_GET['_wpnonce'] ) && wp_get_referer() == '/wp-admin/users.php' ){
            $action  = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
            
            //  check the action is not supposed to catch
            if( 'lock' !== $action && 'unlock' !== $action ){
                return;
            }
            
            //  security check one
            if ( ! check_admin_referer( 'bulk-users' ) ) {
                return;
            }
            
            //  security check two
            if( ! current_user_can( 'create_users' ) ){
                return;
            }
            
            //  secure input for user ids
            $userids = [];
            if( isset( $_GET['users'] ) && is_array( $_GET['users'] ) && !empty( $_GET['users'] ) ){
                foreach( $_GET['users'] as $user_id ){
                    $userids[] = (int)$user_id;
                }
            }
            else{
                return;
            }
            
            //  Process lock request
            if( 'lock' === $action ){
                $current_user_id = get_current_user_id();
                foreach( $userids as $userid ){
                    if( $userid == $current_user_id ) continue;
                    update_user_meta( (int)$userid, sanitize_key( 'baba_user_locked' ), 'yes' );
                }
            }
            
            //  Process unlock request
            elseif( 'unlock' === $action ){
                foreach( $userids as $userid ){
                    update_user_meta( (int)$userid, sanitize_key( 'baba_user_locked' ), '' );
                }
            }
        }
    }
}

new Baba_User_Meta();