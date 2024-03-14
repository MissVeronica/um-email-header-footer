<?php
/**
 * Plugin Name:     Ultimate Member - Email Header/Footer
 * Description:     Extension to Ultimate Member for adding HTML Header/Footer to all outgoing UM Notification emails.
 * Version:         1.2.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.8.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'UM' ) ) return;

class UM_Email_Header_Footer {

    function __construct( ) {

        add_filter( 'um_settings_structure',         array( $this, 'um_settings_structure_header_footer' ), 10, 1 );
        add_filter( 'um_email_send_message_content', array( $this, 'email_add_footer' ), 10, 3 );
        add_filter( 'um_email_template_body_attrs',  array( $this, 'email_add_header' ), 10, 3 );
    }

    public function email_add_footer( $message, $slug, $args ) {
        
        $header_footer_post_html = UM()->options()->get( 'email_templates_header_footer_post_html' );

        $message = str_replace( array( '</body>', '</html>' ) , '', $message );
        $message .= html_entity_decode( wp_unslash( $header_footer_post_html ),  ENT_QUOTES, 'UTF-8' ) . '</body></html>';

        return $message;
    }

    public function email_add_header( $message, $slug, $args ) {

        $header_footer_pre_html = UM()->options()->get( 'email_templates_header_footer_pre_html' );
        $message .= '>' . html_entity_decode( wp_unslash( $header_footer_pre_html ),  ENT_QUOTES, 'UTF-8' ) . '<p></p';

        return $message;
    }

    public function um_settings_structure_header_footer( $settings_structure ) {

        $settings_structure['email']['form_sections']['header_footer']['title']       = __( 'Header/Footer', 'ultimate-member' );
        $settings_structure['email']['form_sections']['header_footer']['description'] = __( 'Plugin version 1.1.0 - tested with UM 2.8.4', 'ultimate-member' );

        $settings_structure['email']['form_sections']['header_footer']['fields'][] =
 
                array(
                    'id'          => 'email_templates_header_footer_pre_html',
                    'type'        => 'wp_editor',
                    'label'       => __( 'Custom Header', 'ultimate-member' ),
                    'description' => __( 'Enter your HTML Email Header', 'ultimate-member' ),
                );

        $settings_structure['email']['form_sections']['header_footer']['fields'][] =
 
                array(
                    'id'          => 'email_templates_header_footer_post_html',
                    'type'        => 'wp_editor',
                    'label'       => __( 'Custom Footer', 'ultimate-member' ),
                    'description' => __( 'Enter your HTML Email Footer', 'ultimate-member' ),
                );

        return $settings_structure;
    }

}

new UM_Email_Header_Footer();
