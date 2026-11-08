<?php
/**
 * Plugin Name:     Ultimate Member - Email Header/Footer
 * Description:     Extension to Ultimate Member for adding HTML Header/Footer to all outgoing UM Notification emails.
 * Version:         1.4.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Plugin URI:      https://github.com/MissVeronica/um-email-header-footer
 * Update URI:      https://github.com/MissVeronica/um-email-header-footer
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.14.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'UM' ) ) return;

class UM_Email_Header_Footer {

    function __construct( ) {

        define( 'Plugin_Basename_EHF', plugin_basename( __FILE__ ));

        add_filter( 'um_settings_structure',                      array( $this, 'um_settings_structure_header_footer' ), 10, 1 );
        add_filter( 'um_email_send_message_content',              array( $this, 'email_add_header_footer' ), 10, 3 );
        add_filter( 'um_admin_settings_email_section_fields',     array( $this, 'um_admin_settings_email_section_fields' ), 9, 2 );
        add_filter( 'plugin_action_links_' . Plugin_Basename_EHF, array( $this, 'plugin_settings_link' ), 10, 1 );
    }

    public function plugin_settings_link( $links ) {

        $url = get_admin_url() . 'admin.php?page=um_options&tab=email';
        $links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings' ) . '</a>';

        return $links;
    }

    public function email_add_header_footer( $message, $slug, $args ) {

        $lines = array_map( 'trim', explode( "\n", $message ));
        $new_lines = array();

        $header_footer_pre_html  = UM()->options()->get( 'email_templates_header_footer_pre_html' );
        $header_footer_post_html = UM()->options()->get( 'email_templates_header_footer_post_html' );

        foreach( $lines as $line ) {
            if ( ! empty( $line )) {

                switch ( substr( $line, 0, 6 )) {

                    case  '<body ': $new_lines[] = $line;
                                    $new_lines[] = html_entity_decode( wp_unslash( $header_footer_pre_html ),  ENT_QUOTES, 'UTF-8' );
                                    break;

                    case  '</body': $new_lines[] = html_entity_decode( wp_unslash( $header_footer_post_html ),  ENT_QUOTES, 'UTF-8' );
                                    $new_lines[] = $line;
                                    break;

                    default:        $new_lines[] = $line;
                }
            }
        }

        $message = implode( "\n", $new_lines );
        return $message;
    }

    public function um_settings_structure_header_footer( $settings_structure ) {

        $settings_structure['email']['form_sections']['header_footer']['title']       = __( 'Header/Footer', 'ultimate-member' );
        $settings_structure['email']['form_sections']['header_footer']['description'] = __( 'Plugin version 1.4.0 - tested with UM 2.10.2', 'ultimate-member' );

        $settings_structure['email']['form_sections']['header_footer']['fields'][] =

                array(
                    'id'          => 'email_templates_header_footer_pre_html',
                    'type'        => 'wp_editor',
                    'label'       => esc_html__( 'Custom Email Header', 'ultimate-member' ),
                    'description' => esc_html__( 'Enter your HTML Email Header for all outgoing UM Notification emails.', 'ultimate-member' ),
                );

        $settings_structure['email']['form_sections']['header_footer']['fields'][] =

                array(
                    'id'          => 'email_templates_header_footer_post_html',
                    'type'        => 'wp_editor',
                    'label'       => esc_html__( 'Custom Email Footer', 'ultimate-member' ),
                    'description' => esc_html__( 'Enter your HTML Email Footer for all outgoing UM Notification emails.', 'ultimate-member' ),
                );

        return $settings_structure;
    }

    public function um_admin_settings_email_section_fields( $section_fields, $email_key ) {

        $content = '';
        $max_width = ! empty( UM()->options()->get( $email_key . '_header_footer_width' ) ) ? absint( UM()->options()->get( $email_key . '_header_footer_width' )) : '390';

        if ( UM()->options()->get( $email_key . '_header_footer' ) == 1 ) {
            $content = html_entity_decode( wp_unslash(
                            '<br><div style="border-style: double; border-color: red; max-width: ' . $max_width . 'px">' .
                            UM()->options()->get( 'email_templates_header_footer_pre_html' ) .
                            UM()->mail()->get_email_template( $email_key ) .
                            UM()->options()->get( 'email_templates_header_footer_post_html' ) .
                            '</div>'
                       ));

            $content = um_convert_tags( $content, array() );
        }

        $section_fields[] = array(
					'id'          => $email_key . '_header_footer',
					'type'        => 'checkbox',
					'label'       => esc_html__( 'Email Content display with Header&Footer', 'ultimate-member' ),
					'conditional' => array( $email_key . '_on', '=', 1 ),
				);

                $section_fields[] = array(
					'id'          => $email_key . '_header_footer_width',
					'type'        => 'text',
                    'size'        => 'small',
                    'value'       => $max_width,
					'label'       => esc_html__( 'Enter display width in pixels.', 'ultimate-member' ) . '<br>' .
                                     esc_html__( 'Default value is 390px for an average mobile.', 'ultimate-member' ),
					'conditional' => array( $email_key . '_header_footer', '=', 1 ),
					'description' => $content,
				);

        return $section_fields;
    }
}

new UM_Email_Header_Footer();


