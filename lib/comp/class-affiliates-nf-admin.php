<?php
/**
 * class-affiliates-nf-settings.php
 *
 * Copyright (c) 2014 - 2017 www.itthinx.com
 *
 * This code is provided subject to the license granted.
 * Unauthorized use and distribution is prohibited.
 * See COPYRIGHT.txt and LICENSE.txt
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This header and all notices must be kept intact.
 *
 * @author itthinx
 * @package affiliates-ninja-forms
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Affiliates form settings.
 */
class Affiliates_NF_Admin {

	const NONCE             = 'aff_ninjaforms_admin_nonce';
	const SET_ADMIN_OPTIONS = 'set_admin_options';

	/**
	 * Initialization action on WordPress init.
	 */
	public static function init() {
		if ( current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
			add_action( 'affiliates_admin_menu', array( __CLASS__, 'affiliates_admin_menu' ) );
			// http://developer.ninjaforms.com/codex/registering-plugin-settings/
			add_filter( 'ninja_forms_plugin_settings', array( __CLASS__, 'ninja_forms_plugin_settings' ) );
			add_filter( 'ninja_forms_plugin_settings_groups', array( __CLASS__, 'ninja_forms_plugin_settings_groups' ) );
			
		}
	}

	/**
	 * Renders the setting section under Ninja Forms > Settings for our Affiliates integration.
	 *
	 * @param array $settings current settings
	 *
	 * @return array with our settings section added
	 */
	public static function ninja_forms_plugin_settings( $settings ) {
		$settings['affiliates'] = array(
			'affiliates' => array(
				'id'    => 'affiliates',
				'type'  => 'desc',
				'label' => __( 'Affiliates Integration', 'affiliates-ninja-forms' ),
				'desc'  =>
					'<p>' .
					sprintf(
						__( 'You have the <strong>Affiliates</strong> integration by <a href="%s">itthinx</a> for Ninja Forms installed.', 'affiliates-ninja-forms' ),
						esc_url( 'https://www.itthinx.com/' )
					) .
					'</p>' .
					'<p>' .
					sprintf(
						__( 'This integrates <a href="%s">Affiliates</a>, <a href="%s">Affiliates Pro</a> and <a href="%s">Affiliates Enterprise</a> with Ninja Forms.', 'affiliates-ninja-forms' ),
						esc_url( 'https://wordpress.org/plugins/affiliates/' ),
						esc_url( 'https://www.itthinx.com/shop/affiliates-pro/' ),
						esc_url( 'https://www.itthinx.com/shop/affiliates-enterprise/' )
					) .
					'</p>' .
					'<p>' .
					__( 'To enable the integration for a form, add the <strong>Affiliates</strong> action to it.', 'affiliates-ninja-forms' ) .
					'</p>' .
					'<p>' .
					__( 'Please refer to these documentation pages for more details:', 'affiliates-ninja-forms' ) .
					'<ul>' .
					'<li>' . sprintf( __( 'Integration with <a href="%s">Affiliates</a>', 'affiliates-ninja-forms' ), esc_url( 'http://docs.itthinx.com/document/affiliates/setup/settings/integrations/' ) ) .'</li>' .
					'<li>' . sprintf( __( 'Integration with <a href="%s">Affiliates Pro</a>', 'affiliates-ninja-forms' ), esc_url( 'http://docs.itthinx.com/document/affiliates-pro//setup/settings/integrations/' ) ) .'</li>' .
					'<li>' . sprintf( __( 'Integration with <a href="%s">Affiliates Enterprise</a>', 'affiliates-ninja-forms' ), esc_url( 'http://docs.itthinx.com/document/affiliates-enterprise/setup/settings/integrations/' ) ) .'</li>' .
					'</ul>' .
					'</p>'
			),
		);
		return $settings;
	}

	/**
	 * Adds our settings group under Ninja Forms > Settings.
	 *
	 * @param array $groups current groups
	 *
	 * @return array with our group added
	 */
	public static function ninja_forms_plugin_settings_groups( $groups ) {
		$groups['affiliates'] = array(
			'id'    => 'affiliates',
			'label' => __( 'Affiliates', 'affiliates-ninja-forms' )
		);
		return $groups;
	}

	/**
	 * Adds a submenu item to the Affiliates menu for the Ninja Forms integration options.
	 */
	public static function affiliates_admin_menu() {
		$page = add_submenu_page(
			'affiliates-admin',
			__( 'Ninja Forms Ninja Forms', 'affiliates-ninja-forms' ),
			__( 'Ninja Forms Integration', 'affiliates-ninja-forms' ),
			AFFILIATES_ADMINISTER_OPTIONS,
			'affiliates-admin-ninja-forms',
			array( __CLASS__, 'affiliates_admin_ninja_forms' )
		);
		$pages[] = $page;
		add_action( 'admin_print_styles-' . $page, 'affiliates_admin_print_styles' );
		add_action( 'admin_print_scripts-' . $page, 'affiliates_admin_print_scripts' );
	}

	/**
	 * Affiliates Ninja Forms Integration : admin section.
	 */
	public static function affiliates_admin_ninja_forms() {
		if ( !current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
			wp_die( esc_html__( 'Access denied.', 'affiliates-ninja-forms' ) );
		}
		$options = get_option( Affiliates_Ninja_Forms::PLUGIN_OPTIONS , array() );
		if ( isset( $_POST['submit'] ) ) {
			if ( wp_verify_nonce( $_POST[self::NONCE], self::SET_ADMIN_OPTIONS ) ) {

				if ( !empty( $_POST['status'] ) ) {
					$options['aff_ninja_forms_referral_status'] = $_POST['status'];
				}

			}
			update_option( Affiliates_Ninja_Forms::PLUGIN_OPTIONS, $options );
		}

		// css
		echo '<style type="text/css">';
		echo 'div.field { padding: 0 1em 1em 0; }';
		echo 'div.field span.label { display: inline-block; width: 20%; }';
		echo 'div.field span.description { display: block; }';
		echo 'div.buttons { padding-top: 1em; }';
		echo '</style>';

		echo
		'<div>' .
		'<h2>' .
		esc_html__( 'Affiliates Ninja Forms Integration', 'affiliates-ninja-forms' ) .
		'</h2>' .
		'</div>';

		echo '<div class="manage" style="padding:2em;margin-right:1em;">';

		echo '<h4>';
		echo esc_html__( 'Rates', 'affiliates-ninja-forms' );
		echo '</h4>';

		$rates = Affiliates_Rate::get_rates( array( 'integration' => 'affiliates-ninja-forms' ) );

		if ( count( $rates ) > 0 ) {
			echo '<p>';
			echo esc_html( _n( 'This specific rate applies to this integration', 'These specific rates apply to this integration.', count( $rates ), 'affiliates-ninja-forms' ) );
			echo '</p>';
			$odd      = true;
			$is_first = true;
			echo '<table style="width:100%">';
			foreach ( $rates as $rate ) {
				if ( $is_first ) {
					echo wp_kses_post( $rate->view( array( 'style' => 'table', 'titles' => true, 'exclude' => 'integration', 'prefix_class' => 'odd' ) ) );
				} else {
					echo wp_kses_post( $rate->view( array( 'style' => 'table', 'exclude' => 'integration', 'prefix_class' => $odd ? 'odd' : 'even' ) ) );
				}
				$is_first = false;
				$odd      = !$odd;
			}
			echo '</table>';
		} else {
			echo '<p>';
			echo esc_html( __( 'This integration has no specific applicable rates.', 'affiliates-ninja-forms' ) );
			echo '</p>';
		}
		if ( current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
			echo '<p>';
			$url = wp_nonce_url( add_query_arg(
				array(
					'integration' => 'affiliates-ninja-forms',
					'action'      => 'create-rate'
				),
				admin_url( 'admin.php?page=affiliates-admin-rates' )
			) );
			echo sprintf(
				'<a href="%s">',
				esc_url( $url )
			);
			echo esc_html__( 'Create a rate', 'affiliates-ninja-forms' );
			echo '</a>';
			echo '</p>';
		}

		echo '<h4>';
		echo esc_html__( 'Status', 'affiliates-ninja-forms' );
		echo '</h4>';

		echo '<form action="" name="options" method="post">';

		$ninja_forms_referral_status = isset( $options['aff_ninja_forms_referral_status'] ) ? $options['aff_ninja_forms_referral_status'] : get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );

		$status_descriptions = array(
			AFFILIATES_REFERRAL_STATUS_ACCEPTED => __( 'Accepted', 'affiliates' ),
			AFFILIATES_REFERRAL_STATUS_PENDING  => __( 'Pending', 'affiliates' )
		);
		echo '<div class="field ninja-forms-referral-status">';
		echo '<label>';
		echo '<span class="label">';
		echo esc_html__( 'Referral Status', 'affiliates' );
		echo '</span>';
		echo ' ';
		echo "<select name='status'>";
		foreach ( $status_descriptions as $status_key => $status_value ) {
			if ( $status_key == $ninja_forms_referral_status ) {
				$selected = "selected='selected'";
			} else {
				$selected = '';
			}
			echo "<option value='" . esc_attr( $status_key ) . "' " . esc_attr( $selected ) . '>' . esc_html( $status_value ) . '</option>';
		}
		echo '</select>';
		echo '</label>';
		echo '</div>';

		echo '<p>';
		echo wp_nonce_field( self::SET_ADMIN_OPTIONS, self::NONCE, true, false );
		echo '<input class="button-primary" type="submit" name="submit" value="' . esc_attr__( 'Save', 'affiliates-ninja-forms' ) . '"/>';
		echo '</p>';

		echo '</div>';
		echo '</form>';
		echo '</div>';

		affiliates_footer( false );

	}

}
Affiliates_NF_Admin::init();
