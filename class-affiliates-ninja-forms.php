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

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !defined( 'AFFILIATES_NINJA_FORMS_CORE_DIR' ) ) {
	define( 'AFFILIATES_NINJA_FORMS_CORE_DIR', WP_PLUGIN_DIR . '/affiliates-ninja-forms' );
}

/**
 * Integration for Ninja Forms.
 */
class Affiliates_Ninja_Forms {

	const NINJA_FORMS_INTEGRATION_NAME = 'affiliates-ninjaforms';
	const NINJA_FORMS_POST_TYPE = 'ninja_forms';
	const PLUGIN_OPTIONS = 'affiliates_ninja_forms';
	const NONCE = 'aff_ninjaforms_admin_nonce';
	const SET_ADMIN_OPTIONS = 'set_admin_options';

	/**
	 * Admin messages
	 *
	 * @var array
	 */
	private static $admin_messages = array();

	/**
	 * Prints admin notices.
	 */
	public static function admin_notices() {
		if ( !empty( self::$admin_messages ) ) {
			foreach ( self::$admin_messages as $msg ) {
				echo wp_kses( $msg, array(
					'a'      => array( 'href' => array(), 'target' => array(), 'title' => array() ),
					'br'     => array(),
					'div'    => array( 'class' => array() ),
					'em'     => array(),
					'p'      => array( 'class' => array() ),
					'strong' => array()
				) );
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
		require_once AFFILIATES_NINJA_FORMS_COMP_LIB . '/class-affiliates-nf.php';

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
		echo '<style type="text/css">';
		echo 'div.field { padding: 0 1em 1em 0; }';
		echo 'div.field.ninja-forms-amount input { width: 5em; text-align: right;}';
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
		echo '<form action="" name="options" method="post">';

		$ninja_forms_amount      = isset( $options['aff_ninja_forms_amount'] ) ? $options['aff_ninja_forms_amount'] : '0';
		$ninja_forms_currency    = isset( $options['aff_ninja_forms_currency'] ) ? $options['aff_ninja_forms_currency'] : Affiliates::DEFAULT_CURRENCY;
		$ninja_forms_referral_status = isset( $options['aff_ninja_forms_referral_status'] ) ? $options['aff_ninja_forms_referral_status'] : get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );

		// amount
		echo '<div class="field ninja-forms-amount">';
		echo '<label>';
		echo '<span class="label">';
		echo esc_html__( 'Amount', 'affiliates' );
		echo '</span>';
		echo ' ';
		echo sprintf( '<input type="text" name="amount" value="%s"/>', esc_attr( $ninja_forms_amount ) );
		echo '</label>';
		echo '</div>';

		// currency
		echo '<div class="field ninja-forms-currency">';
		echo '<label>';
		echo '<span class="label">';
		echo esc_html__( 'Currency', 'affiliates' );
		echo '</span>';
		echo ' ';
		echo '<select name="currency">';
		foreach ( apply_filters( 'affiliates_supported_currencies', Affiliates::$supported_currencies ) as $cid ) {
			$selected = ( $ninja_forms_currency == $cid ) ? ' selected="selected" ' : '';
			echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $cid ) . '">' . esc_html( $cid ) . '</option>';
		}
		echo '</select>';
		echo '</label>';
		echo '</div>';

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
Affiliates_Ninja_Forms::init();
