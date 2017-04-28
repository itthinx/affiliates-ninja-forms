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
 * Version: 2.0
 * Author: itthinx
 * Author URI: http://www.itthinx.com/
 * Donate-Link: http://www.itthinx.com/shop/affiliates-enterprise/
 * License: GPLv3
 */

/**
 * Integration for Ninja Forms.
 */
class Affiliates_Ninja_Forms_Integration {

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

		add_action ( 'ninja_forms_process', array( __CLASS__, 'ninja_forms_process' ) ); // Ninja Forms 2.x
		add_action( 'affiliates_admin_menu', array( __CLASS__, 'affiliates_admin_menu' ) );

		add_action( 'ninja_forms_after_submission', array( __CLASS__, 'ninja_forms_after_submission' ) );
	}

	public static function ninja_forms_after_submission( $form_data ){
	global $ninja_forms_processing;

		$post_id = $form_data['form_id'];
	
		$description = sprintf( 'NinjaForms form %s', $post_id );
		
		$options = get_option( self::PLUGIN_OPTIONS , array() );
		
		$base_amount = null;
		$amount = isset( $options['aff_ninja_forms_amount'] ) ? $options['aff_ninja_forms_amount'] : '0';
		$currency = isset( $options['aff_ninja_forms_currency'] ) ? $options['aff_ninja_forms_currency'] : Affiliates::DEFAULT_CURRENCY;
		$ninja_forms_referral_status = isset( $options['aff_ninja_forms_referral_status'] ) ? $options['aff_ninja_forms_referral_status'] : get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );
		
		$data = array();
		
		//Get all the user submitted values
		$all_fields = $form_data['fields']; //$ninja_forms_processing->get_all_submitted_fields();
		
		if ( is_array( $all_fields ) ) {
			foreach ( $all_fields as $field_id => $field_data ) {
				$field_value = $field_data['value'];
				
				$data[$field_id] = array(
						'title' => $field_data['label'],
						'domain' => 'affiliates',
						'value' => $field_value
				);
			}
		}

		if ( class_exists( 'Affiliates_Referral_WordPress' ) ) {
			$r = new Affiliates_Referral_WordPress();
			$affiliate_id = $r->evaluate( $post_id, $description, $data, $base_amount, $amount, $currency, $ninja_forms_referral_status, self::NINJA_FORMS_POST_TYPE );
		} else {
			$affiliate_id = affiliates_suggest_referral( $post_id, $description, $data, $amount, $currency, $ninja_forms_referral_status, self::NINJA_FORMS_POST_TYPE );
		}
	}

	/**
	 * Deprecated: Ninja Forms 2.x form processed.
	 */
	public static function ninja_forms_process() {
		global $ninja_forms_processing;

		$post_id = $ninja_forms_processing->get_form_ID();

		$description = sprintf( 'NinjaForms form %s', $post_id );

		$options = get_option( self::PLUGIN_OPTIONS , array() );

		$base_amount = null;
		$amount = isset( $options['aff_ninja_forms_amount'] ) ? $options['aff_ninja_forms_amount'] : '0';
		$currency = isset( $options['aff_ninja_forms_currency'] ) ? $options['aff_ninja_forms_currency'] : Affiliates::DEFAULT_CURRENCY;
		$ninja_forms_referral_status = isset( $options['aff_ninja_forms_referral_status'] ) ? $options['aff_ninja_forms_referral_status'] : get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );

		$data = array();

		//Get all the user submitted values
		$all_fields = $ninja_forms_processing->get_all_submitted_fields();
		
		if ( is_array( $all_fields ) ) {
			foreach ( $all_fields as $field_id => $user_value ) {

				$field_value = $ninja_forms_processing->get_field_value( $field_id );
				$field_settings = $ninja_forms_processing->get_field_settings( $field_id );

				$data[$field_id] = array(
						'title' => $field_settings['data']['label'],
						'domain' => 'affiliates',
						'value' => $field_value
				);
			}
		}

		if ( class_exists( 'Affiliates_Referral_WordPress' ) ) {
			$r = new Affiliates_Referral_WordPress();
			$affiliate_id = $r->evaluate( $post_id, $description, $data, $base_amount, $amount, $currency, $ninja_forms_referral_status, self::NINJA_FORMS_POST_TYPE );
		} else {
			$affiliate_id = affiliates_suggest_referral( $post_id, $description, $data, $amount, $currency, $ninja_forms_referral_status, self::NINJA_FORMS_POST_TYPE );
		}
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
