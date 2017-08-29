<?php
/**
 * affiliates-ninja-forms.php
 * 
 * Copyright (c) 2012-2015 "kento" Karim Rahimpur www.itthinx.com
 * 
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 * 
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * This header and all notices must be kept intact.
 * 
 * @author Karim Rahimpur
 * @package affiliates-ninja-forms
 * @since affiliates-ninja-forms 1.0.0
 *
 * Plugin Name: Affiliates Ninja Forms Integration
 * Plugin URI: http://www.itthinx.com/plugins/affiliates-ninja-forms/
 * Description: Integrates Affiliates with Ninja Forms
 * Version: 2.0.0
 * Author: itthinx
 * Author URI: http://www.itthinx.com/
 * Donate-Link: http://www.itthinx.com/shop/affiliates-enterprise/
 * License: GPLv3
 */

if ( !defined( 'AFFILIATES_NINJA_FORMS_CORE_DIR' ) ) {
	define( 'AFFILIATES_NINJA_FORMS_CORE_DIR', WP_PLUGIN_DIR . '/affiliates-ninja-forms' );
}

/**
 * Integration for Ninja Forms.
 */
class Affiliates_Ninja_Forms_Integration {

	const NINJA_FORMS_INTEGRATION_NAME = 'affiliates-ninjaforms';
	const NINJA_FORMS_POST_TYPE = 'ninja_forms';
	const PLUGIN_OPTIONS = 'affiliates_ninja_forms';
	const NONCE = 'aff_ninjaforms_admin_nonce';
	const SET_ADMIN_OPTIONS = 'set_admin_options';
	

	private static $admin_messages = array();

	/**
	 * Prints admin notices.
	 */
	public static function admin_notices() {
		if ( !empty( self::$admin_messages ) ) {
			foreach ( self::$admin_messages as $msg ) {
				echo $msg;
			}
		}
	}

	/**
	 * Checks dependencies and adds appropriate actions and filters.
	 */
	public static function init() {

		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		add_action( 'affiliates_admin_menu', array( __CLASS__, 'affiliates_admin_menu' ) );

		if ( defined( 'AFFILIATES_EXT_VERSION' ) && version_compare( AFFILIATES_EXT_VERSION, '3.0.0' ) >= 0 ) {
			$comp = '/comp';
		} else {
			$comp = '/comp-2';
		}
		if ( !defined( 'AFFILIATES_NINJA_FORMS_COMP_LIB' ) ) {
			define( 'AFFILIATES_NINJA_FORMS_COMP_LIB', AFFILIATES_NINJA_FORMS_CORE_DIR . '/lib' . $comp );
		}
		require_once( AFFILIATES_NINJA_FORMS_COMP_LIB . '/class-affiliates-ninja-forms.php');

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
		$output = '';
		if ( !current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
			wp_die( __( 'Access denied.', 'affiliates-ninja-forms' ) );
		}
		$options = get_option( self::PLUGIN_OPTIONS , array() );
		if ( isset( $_POST['submit'] ) ) {
			if ( wp_verify_nonce( $_POST[self::NONCE], self::SET_ADMIN_OPTIONS ) ) {

				if ( !empty( $_POST['amount'] ) ) {
					$amount = floatval( $_POST['amount'] );
					if ( $amount < 0 ) {
						$amount = 0;
					}
					$options['aff_ninja_forms_amount'] = $amount;
				}
	
				if ( !empty( $_POST['currency'] ) ) {
					$options['aff_ninja_forms_currency'] = $_POST['currency'];
				}
	
				if ( !empty( $_POST['status'] ) ) {
					$options['aff_ninja_forms_referral_status'] = $_POST['status'];
				}

			}
			update_option( self::PLUGIN_OPTIONS, $options );
		}

		// css
		$output .= '<style type="text/css">';
		$output .= 'div.field { padding: 0 1em 1em 0; }';
		$output .= 'div.field.ninja-forms-amount input { width: 5em; text-align: right;}';
		$output .= 'div.field span.label { display: inline-block; width: 20%; }';
		$output .= 'div.field span.description { display: block; }';
		$output .= 'div.buttons { padding-top: 1em; }';
		$output .= '</style>';

		$output .=
			'<div>' .
			'<h2>' .
			__( 'Affiliates Ninja Forms Integration', 'affiliates-ninja-forms' ) .
			'</h2>' .
			'</div>';

		$output .= '<div class="manage" style="padding:2em;margin-right:1em;">';
		$output .= '<form action="" name="options" method="post">';        

		$ninja_forms_amount      = isset( $options['aff_ninja_forms_amount'] ) ? $options['aff_ninja_forms_amount'] : '0';
		$ninja_forms_currency    = isset( $options['aff_ninja_forms_currency'] ) ? $options['aff_ninja_forms_currency'] : Affiliates::DEFAULT_CURRENCY;
		$ninja_forms_referral_status = isset( $options['aff_ninja_forms_referral_status'] ) ? $options['aff_ninja_forms_referral_status'] : get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );

		// amount
		$output .= '<div class="field ninja-forms-amount">';
		$output .= '<label>';
		$output .= '<span class="label">';
		$output .= __( 'Amount', 'affiliates' );
		$output .= '</span>';
		$output .= ' ';
		$output .= sprintf( '<input type="text" name="amount" value="%s"/>', esc_attr( $ninja_forms_amount ) );
		$output .= '</label>';
		$output .= '</div>';

		// currency
		$currency_select = '<select name="currency">';
		foreach( apply_filters( 'affiliates_supported_currencies', Affiliates::$supported_currencies ) as $cid ) {
			$selected = ( $ninja_forms_currency == $cid ) ? ' selected="selected" ' : '';
			$currency_select .= '<option ' . $selected . ' value="' .esc_attr( $cid ).'">' . $cid . '</option>';
		}
		$currency_select .= '</select>';
		$output .= '<div class="field ninja-forms-currency">';
		$output .= '<label>';
		$output .= '<span class="label">';
		$output .= __( 'Currency', 'affiliates' );
		$output .= '</span>';
		$output .= ' ';
		$output .= $currency_select;
		$output .= '</label>';
		$output .= '</div>';

		$status_descriptions = array(
				AFFILIATES_REFERRAL_STATUS_ACCEPTED => __( 'Accepted', 'affiliates' ),
				AFFILIATES_REFERRAL_STATUS_PENDING  => __( 'Pending', 'affiliates' )
		);
		$status_select = "<select name='status'>";
		foreach ( $status_descriptions as $status_key => $status_value ) {
			if ( $status_key == $ninja_forms_referral_status ) {
				$selected = "selected='selected'";
			} else {
				$selected = "";
			}
			$status_select .= "<option value='$status_key' $selected>$status_value</option>";
		}
		$status_select .= "</select>";
		$output .= '<div class="field ninja-forms-referral-status">';
		$output .= '<label>';
		$output .= '<span class="label">';
		$output .= __( 'Referral Status', 'affiliates' );
		$output .= '</span>';
		$output .= ' ';
		$output .= $status_select;
		$output .= '</label>';
		$output .= '</div>';

		$output .= '<p>';
		$output .= wp_nonce_field( self::SET_ADMIN_OPTIONS, self::NONCE, true, false );
		$output .= '<input class="button-primary" type="submit" name="submit" value="' . __( 'Save', 'affiliates-ninja-forms' ) . '"/>';
		$output .= '</p>';

		$output .= '</div>';
		$output .= '</form>';
		$output .= '</div>';

		$output .= affiliates_footer( false );

		echo $output;

	}

}
Affiliates_Ninja_Forms_Integration::init();
