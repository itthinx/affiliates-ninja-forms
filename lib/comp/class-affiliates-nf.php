<?php
/**
 * class-affiliates-ninja-forms.php
 *
 * Copyright (c) "kento" Karim Rahimpur www.itthinx.com
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
 * @author Karim Rahimpur
 * @package affiliates-ninja-forms
 * @since affiliates-ninja-forms 2.0.0
 */

/**
 * Integration controller.
 */
class Affiliates_NF {

	/**
	 * Loads hooks
	 */
	public static function init() {
		add_action( 'ninja_forms_process', array( __CLASS__, 'ninja_forms_process' ) ); // Ninja Forms 2.x
		add_action( 'ninja_forms_after_submission', array( __CLASS__, 'ninja_forms_after_submission' ) );
	}

	/**
	 * Process the 'ninja_forms_after_submission' action
	 *
	 * @param array $form_data
	 */
	public static function ninja_forms_after_submission( $form_data ) {
		global $ninja_forms_processing;
		$post_id = $form_data['form_id'];

		$description = sprintf( 'NinjaForms form %s', $post_id );

		$options = get_option( Affiliates_Ninja_Forms::PLUGIN_OPTIONS , array() );

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
			$affiliate_id = $r->evaluate( $post_id, $description, $data, $base_amount, $amount, $currency, $ninja_forms_referral_status, Affiliates_Ninja_Forms::NINJA_FORMS_POST_TYPE );
		} else {
			$affiliate_id = affiliates_suggest_referral( $post_id, $description, $data, $amount, $currency, $ninja_forms_referral_status, Affiliates_Ninja_Forms::NINJA_FORMS_POST_TYPE );
		}
	}

	/**
	 * Deprecated: Ninja Forms 2.x form processed.
	 */
	public static function ninja_forms_process() {
		global $ninja_forms_processing;

		$post_id = $ninja_forms_processing->get_form_ID();

		$description = sprintf( 'NinjaForms form %s', $post_id );

		$options = get_option( Affiliates_Ninja_Forms::PLUGIN_OPTIONS , array() );

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

		$affiliate_id = Affiliates_Service::get_referrer_id();
		if ( $affiliate_id ) {
			// create referral using Affiliates Pro/Enterprise 3.x API
			$referrer_params = array();
			$rc = new Affiliates_Referral_Controller();
			if ( $params = $rc->evaluate_referrer() ) {
				$referrer_params[] = $params;
			}
			$n = count( $referrer_params );
			if ( $n > 0 ) {
				foreach ( $referrer_params as $params ) {
					$affiliate_id = $params['affiliate_id'];
					$order_items = $order->get_items();
					if ( count( $order_items ) > 0 ) {
						$referral_items = array();
						foreach ( $order_items as $order_item_id => $order_item ) {
							$rate_id   = null;
							$object_id = null;
							$term_ids  = null;
							if ( $product = $order_item->get_product() ) {
								$object_id = $product->get_id();
								$term_ids = $product->get_category_ids();
							}
							if ( $rate = $rc->seek_rate( array(
								'affiliate_id' => $affiliate_id,
								'object_id'    => $post_id,
								'integration'  => Affiliates_Ninja_Forms::NINJA_FORMS_INTEGRATION_NAME
							) ) ) {
								$rate_id = $rate->rate_id;
								$type = Affiliates_Ninja_Forms::NINJA_FORMS_POST_TYPE;
								// split proportional total if multiple affiliates are involved
								if ( $n > 1 ) {
									$amount = bcdiv( $amount, $n, affiliates_get_referral_amount_decimals() );
								}
								$referral_item = new Affiliates_Referral_Item( array(
									'rate_id'     => $rate_id,
									'amount'      => $amount,
									'currency_id' => $currency,
									'type'        => $type,
									'reference'   => $post_id,
									'line_amount' => $amount
								) );
								$referral_items[] = $referral_item;
							}
						}
					}
					$params['post_id']          = $post_id;
					$params['description']      = $description;
					$params['data']             = $data;
					$params['currency_id']      = $currency;
					$params['type']             = Affiliates_Ninja_Forms::NINJA_FORMS_POST_TYPE;
					$params['referral_items']   = $referral_items;
					$params['reference']        = $post_id;
					$params['reference_amount'] = $amount;
					$rc->add_referral( $params );
				}
			}
		}
	}

}
Affiliates_NF::init();
