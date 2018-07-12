<?php
/*
*
* @package Omega Modal Login
*
* Plugin Name: Omega Modal Login
* Plugin URI: http://samremonte.com
* Description: Append modal login link on selected Wordpress navigation menu.
* Version: 1.0.0
* Author: Sam Remonte
* Author URI: http://samremonte.com
* License: GPLv2 or later
* Text Domain: Omega Modal Login
*
*/

defined( 'ABSPATH' ) or die( 'Hey you! what are you doing here?' );


class OmegaModalLogin
{

    public function __construct(){

        add_action( 'wp_ajax_nopriv_selectedThemeLocation', array( $this, 'selected_theme_location' ) );
        add_action( 'wp_ajax_selectedThemeLocation', array( $this, 'selected_theme_location' ) );
        add_action( 'wp_ajax_nopriv_oml_login_member', array( $this, 'oml_login_member' ) );
        add_action( 'wp_ajax_nopriv_oml_register_member', array( $this, 'oml_register_member' ) );

    }

    public function front_enqueues(){

        wp_enqueue_style( 'bootstrap', plugins_url( '/bootstrap/bootstrap.min.css', __FILE__ ) );
        wp_enqueue_style( 'oml-login-style', plugins_url( '/css/oml.css', __FILE__ ), true );
        wp_deregister_script( 'jquery' );
        wp_enqueue_script( 'custom-jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js');
        wp_enqueue_script( 'bootstrap-js', plugins_url( '/bootstrap/bootstrap.min.js', __FILE__ ), array(), true );
        wp_enqueue_script( 'selectedthemelocation', plugins_url( '/js/oml.js', __FILE__ ) );
        wp_localize_script( 'selectedthemelocation', 'ajaxOperations',
            array(
                'ajax_url'      => admin_url( 'admin-ajax.php' ),
                //'ajax_nonce'    => wp_create_nonce( 'oml-plugin-nonce' ),
            )
        );

    }

    public function back_enqueues(){

        wp_enqueue_style( 'oml-style', plugins_url( '/css/admin-style.css', __FILE__ ) );

        wp_enqueue_script( 'selectedthemelocation', plugins_url( '/js/admin.js', __FILE__ ) );
        wp_localize_script( 'selectedthemelocation', 'ajaxOperations',
            array(
                'ajax_url'      => admin_url( 'admin-ajax.php' ),
                'ajax_nonce'    => wp_create_nonce( 'ajax_nonce' ),
            )
        );

    }

    public function oml_admin_page_creator(){

        add_menu_page( 'Omega Modal Login', 'Omega Login', 'manage_options', 'oml', array( $this, 'oml_admin_page' ), '', null );
        add_submenu_page( 'omega_modal_login', 'Omega Modal Login', 'General', 'manage_options', 'omega_modal_login', 'oml_admin_page' );
        add_action( 'admin_init', array( $this, 'oml_custom_settings' ) );

    }

    public function oml_admin_page(){

        require_once plugin_dir_path( __FILE__ ). 'templates/admin.php';

    }

    // @oml shortcode settings section
    public function oml_custom_settings(){

        // ============== HEADING
        add_settings_section(
            'oml-shortcode-options',
            'Using Shortcode',
            array( $this, 'oml_shortcode_options' ),
            'omega_modal_login'
        );

        // ============== SELECT
        register_setting(
            'oml-settings-group',
            'oml_selected_theme_location'
        );

        add_settings_field(
            'shortcode-menu',
            'Theme Location',
            array( $this, 'oml_shortcode_option_select' ),
            'omega_modal_login',
            'oml-shortcode-options'
        );

    }

    public function oml_shortcode_options(){

        echo '<hr>';
        echo '<p class="oml-shortcode-instruction">Select navigation menu where you want to append the <span class="oml">OML</span> trigger.</p>';

    }

    public function oml_shortcode_option_select(){

        $menus = get_registered_nav_menus();
        ?>
            <select id="oml-menus" name="oml_selected_theme_location">
                <option value="" disabled selected>Select Menu</option>
                <option value="">None</option>
                <?php
                foreach( $menus as $menu => $desc ){
                ?>
                    <option value="<?php echo $menu; ?>"><?php echo $menu; ?></option>
                <?php
                }
                ?>
            </select>
            <span class="oml-active">Active: </span><span class="themelocation"><?php echo !empty( get_option( 'oml_selected_theme_location' ) ) ? get_option( 'oml_selected_theme_location' ) : 'None'; ?></span>
        <?php

    }

    public function selected_theme_location(){

        if( !wp_verify_nonce( $_POST['security'], 'ajax_nonce' ) ){
            wp_send_json_error( array('message' => 'Nonce is invalid.') );
        }

        if( isset( $_POST['menu'] ) ){
            update_option( 'oml_selected_theme_location', $_POST['menu'] );
        }
        echo get_option( 'oml_selected_theme_location' );
	    die();

    }

    function oml_loginout_link( $items, $args ) {

        if( is_user_logged_in() && $args->theme_location == get_option( 'oml_selected_theme_location' ) ) {
            $items .= '<li><a href="' .wp_logout_url( home_url() ). '">Log Out</a></li>';
        }
        elseif( !is_user_logged_in() && $args->theme_location == get_option( 'oml_selected_theme_location' ) ) {
            $items .= '<li><a href="#oml-login" data-toggle="modal" data-target="#oml-user-modal">Log In</a></li>';
        }
        return $items;

    }

    public function oml_login_register_modal() {

        if( !is_user_logged_in()){
        ?>
        	<div class="modal fade" id="oml-user-modal" tabindex="-1" role="dialog" aria-hidden="true">
        		<div class="modal-dialog" data-active-tab="">
        			<div class="modal-content">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        				<div class="modal-body">
                            
                            <?php
                            // =================================================
                            // LOGIN FORM
                            ?>
                            <div class="oml-login">
                                <form id="oml_login_form" action="<?php echo home_url( '/' ); ?>" method="post">

                                    <?php wp_nonce_field( 'oml-login-nonce', 'login-security' ); ?>
                                    <div class="form-field">
                                        <input class="form-control input-lg required" name="oml_user_login" id="oml_user_name" type="text" placeholder="Username"/>
                                    </div>
                                    <div class="form-field">
                                        <input class="form-controls input-lg required" name="oml_user_pass" id="oml_user_pass" type="password" placeholder="Password" />
                                    </div>
                                    <div class="form-field">
                                        <input type="hidden" name="action" value="oml_login_member"/>
                                        <button class="oml-button oml-login-button" data-loading-text="<?php _e('Loading...', 'oml') ?>" type="submit">
                                            <?php _e('Login', 'oml'); ?>
                                        </button>
                                    </div>
                                    <div class="oml-loading">
                                        <p><i class="fa fa-refresh fa-spin"></i><br><?php _e('Loading...', 'oml') ?></p>
                                    </div>
                                    <div class="oml-errors"></div>
                                    <div class="lostpasswordwrap">
                                        <a class="lostpassword" href="/wp-login.php?action=lostpassword"><?php _e('Lost Password?', 'oml') ?></a>
                                    </div>

                                </form>

                                
                			</div>    
                            
                            
                            <?php
                            // =================================================
                            // REGISTRATION FORM
                            ?>
                            <div class="oml-register">
                                <form id="oml_registration_form" action="<?php echo home_url( '/' ); ?>" method="POST">

                                    <?php wp_nonce_field( 'oml-register-nonce', 'register-security' ); ?>
                                    <div class="form-field">
                                        <input class="form-control input-lg required" name="oml_user_login" type="text" placeholder="Username"/>
                                    </div>
                                    <div class="form-field">
                                        <input class="form-control input-lg required" name="oml_user_email" id="oml_user_email" type="email" placeholder="Email"/>
                                    </div>
                                    <div class="form-field">
                                        <input type="hidden" name="action" value="oml_register_member"/>
                                        <button class="oml-button oml-register-button" data-loading-text="<?php _e('Loading...', 'oml') ?>" type="submit"><?php _e('Sign up', 'oml'); ?></button>
                                    </div>

                                </form>

                                <div class="oml-errors"></div>
                            </div>
                            
                            
                            <div class="modal-footer">
            					<span class="oml-register-footer"><?php _e('Don\'t have an account?', 'oml'); ?> <a href="#oml-register"><?php _e('Sign Up', 'oml'); ?></a></span>
            				    <span class="oml-login-footer"><?php _e('Already have an account?', 'oml'); ?> <a href="#oml-login"><?php _e('Login', 'oml'); ?></a></span>
            				</div>
        			    </div>
        		    </div>
        	    </div>
            </div>
        <?php
        }else{
            wp_redirect( home_url('/wp-admin') );
        }

    }

    // =================================================
    // LOGIN
    public function oml_login_member(){

        $user_login		= sanitize_user( $_POST['oml_user_login'] );
        $user_pass		= sanitize_text_field( $_POST['oml_user_pass'] );

        // CHECK CSRF TOKEN
        //
        if( !check_ajax_referer( 'oml-login-nonce', 'login-security', false) ){

            echo json_encode(
                array(
                    'error'     => true,
                    'message'   => '<div class="alert alert-danger login-error">'.__('Session token has expired, please reload the page and try again', 'oml').'</div>'
                )
            );

        }elseif( empty($user_login) || empty($user_pass) ){

            echo json_encode(
                array(
                    'error'     => true,
                    'message'   => '<div class="alert alert-danger login-error">'.__('Please don\'t leave the fields empty', 'oml').'</div>'
                )
            );

        }else{

            $user = wp_signon( array('user_login' => $user_login, 'user_password' => $user_pass), false );

            if( is_wp_error($user) ){
                echo json_encode(
                    array(
                        'error'     => true,
                        'message'   => '<div class="alert alert-danger login-error"><strong>Error:  </strong>Incorrect username or password</div>'
                    )
                );
            }else{
                echo json_encode(
                    array(
                        'error'     => false,
                        'message'=> '<div class="alert alert-success">'.__('Login successful, reloading page...', 'oml').'</div>'
                    )
                );
            }
        }
    	die();

    }

    // =================================================
    // REGISTER
    function oml_register_member(){

        $user_login	= sanitize_user( $_POST['oml_user_login'] );
    	$user_email	= sanitize_email( $_POST['oml_user_email'] );
    	$user_random_password = wp_generate_password( 12, false );

    	$user_info = array(
    		'user_login'	=> $user_login,
    		'user_email'	=> $user_email,
    		'user_pass'		=> $user_random_password,
    	);

    	// CHECK CSRF TOKEN
    	//
    	if( !check_ajax_referer( 'oml-register-nonce', 'register-security', false) ){

    		echo json_encode(
                array(
                    'error'     => true,
                    'message'   => '<div class="alert alert-danger register-error">' .__('Session token has expired, please reload the page and try again', 'oml'). '</div>'
                )
            );
    		die();
            
    	}
        // DIE IF THERE ARE EMPTY FIELDS
        //
        elseif( empty($user_login) || empty($user_email) ){

            echo json_encode(
                array(
                    'error'     => true,
                    'message'   => '<div class="alert alert-danger register-error">' .__('<strong>Error:  </strong>Please fill all form fields', 'oml'). '</div>'
                )
            );
            die();
            
    	}
        
        // WP ERROR CHECKING
    	$errors = wp_insert_user( $user_info );
        
    	if( is_wp_error($errors) ){
            
    		$registration_error_messages = $errors->errors;

    		$display_errors = '<div class="alert alert-danger register-error">';

    		foreach($registration_error_messages as $error){
    				$display_errors .= '<p>'.$error[0].'</p>';
    		}

    		$display_errors .= '</div>';

    		echo json_encode(
                array(
                    'error'     => true,
                    'message'   => $display_errors
                )
            );

    	}else{

    		echo json_encode(
                array(
                    'error'     => false,
                    'message'   => '<div class="alert alert-success">'.__( 'Registration complete. Please check your e-mail.', 'oml').'</p>'
                )
            );

    		// YOU COULD CUSTOMIZE YOUR OWN MESSAGE OR SET THIS IN CUSTOMIZER
    		$message = 'Thank you again for signing up.
            Here are your login details
            Username:' .$user_login. '
            Password:' .$user_random_password. '<br>'
            .home_url('/wp-admin'). '


            <br>See you there!,
            oml ';

    		wp_mail( $user_email, 'Welcome! '.$user_login, $message, array() );
    	}
    	die();

    }

    public function activation(){

        add_action( 'admin_enqueue_scripts', array( $this, 'back_enqueues' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'front_enqueues' ) );
        add_action( 'admin_menu', array( $this, 'oml_admin_page_creator' ) );
        add_action( 'wp_footer', array( $this, 'oml_login_register_modal' ) );
        if( !empty( get_option( 'oml_selected_theme_location' ) ) ){
            add_filter( 'wp_nav_menu_items', array( $this, 'oml_loginout_link' ), 10, 2 );
        }

    }

}

if( !class_exists( 'OmegaModalLogin' ) ){
    $omegaLogin = new OmegaModalLogin();
    $omegaLogin->activation();
}
