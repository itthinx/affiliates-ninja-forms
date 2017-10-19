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

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integration controller.
 */
class Affiliates_NF {

	/**
	 * Loads hooks
	 */
	public static function init() {
		add_action( 'ninja_forms_after_submission', array( __CLASS__, 'ninja_forms_after_submission' ) );
		add_action( 'delete_post', array( __CLASS__, 'delete_post' ) );
		add_action( 'wp_trash_post', array( __CLASS__, 'wp_trash_post' ) );
		add_action( 'untrash_post', array( __CLASS__, 'untrash_post' ) );
	}

	public static function delete_post( $post_id ) {
		if ( get_post_type( $post_id ) === 'nf_sub' ) {
			
		}
	}

	public static function wp_trash_post( $post_id ) {
		
	}

	public static function untrash_post( $post_id ) {
		
	}

	/**
	 * Process the 'ninja_forms_after_submission' action
	 *
	 * @param array $form_data
	 */
	//public static function ninja_forms_after_submission( $form_data ) {
	
	/**
	 * Save form submission handler.
	 * @param int $sub_id saved form's ID
	 */
	public static function nf_save_sub( $sub_id ) {

		global $ninja_forms_processing, $post_id;

		$options = get_option( Affiliates_Ninja_Forms::PLUGIN_OPTIONS , array() );

		/**
		 * The saved form submission.
		 * @var NF_Sub $sub
		 */
		$sub = Ninja_Forms()->sub( $sub_id );
		if ( empty( $sub->sub_id ) ) {
			return;
		}

		$form_id = $sub->form_id;

		/**
		 * The form related to the submission.
		 * @var NF_Form $form
		 */
		$form = Ninja_Forms()->form( $form_id );

		$description = sprintf( 'Ninja Forms form %s', $form_id );
		$ninja_forms_referral_status = isset( $options['aff_ninja_forms_referral_status'] ) ? $options['aff_ninja_forms_referral_status'] : get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );

		$amount = 0;
		if ( $total = $ninja_forms_processing->get_calc_total( false, false ) ) {
			$amount = $total;
		}

		$data = array();

		//Get all the user submitted values
		$all_fields = $sub->get_all_fields(); //$form_data['fields']; //$ninja_forms_processing->get_all_submitted_fields();

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

		// Using Affiliates 3.x API
		$referrer_params = array();
		$rc = new Affiliates_Referral_Controller();
		if ( $params = $rc->evaluate_referrer() ) {
			$referrer_params[] = $params;
		}
		$n = count( $referrer_params );
		if ( $n > 0 ) {
			foreach ( $referrer_params as $params ) {
				$affiliate_id = $params['affiliate_id'];
				$group_ids = null;
				if ( class_exists( 'Groups_User' ) ) {
					if ( $affiliate_user_id = affiliates_get_affiliate_user( $affiliate_id ) ) {
						$groups_user = new Groups_User( $affiliate_user_id );
						$group_ids = $groups_user->group_ids_deep;
						if ( !is_array( $group_ids ) || ( count( $group_ids ) === 0 ) ) {
							$group_ids = null;
						}
					}
				}

				$referral_items = array();
				if ( $rate = $rc->seek_rate( array(
					'affiliate_id' => $affiliate_id,
					'object_id'    => $form_id,
					'term_ids'     => null,
					'integration'  => 'affiliates-ninja-forms',
					'group_ids'    => $group_ids
				) ) ) {
					$rate_id = $rate->rate_id;
					$type = Affiliates_Ninja_Forms::REFERRAL_TYPE;

					switch ( $rate->type ) {
						case AFFILIATES_PRO_RATES_TYPE_AMOUNT :
							$amount = bcadd( '0', $rate->value, affiliates_get_referral_amount_decimals() );
							break;
						case AFFILIATES_PRO_RATES_TYPE_RATE :
							// check form for base_amount
							$amount = bcmul( $amount, $rate->value, affiliates_get_referral_amount_decimals() );
							break;
					}
					// split proportional total if multiple affiliates are involved
					if ( $n > 1 ) {
						$amount = bcdiv( $amount, $n, affiliates_get_referral_amount_decimals() );
					}

					$referral_item = new Affiliates_Referral_Item( array(
						'rate_id'     => $rate_id,
						'amount'      => $amount,
						'currency_id' => $rate->currency_id,
						'type'        => $type,
						'reference'   => $form_id,
						'line_amount' => $amount,
						'object_id'   => $form_id
					) );
					$referral_items[] = $referral_item;
				}
				$params['post_id']          = $post_id;
				$params['description']      = $description;
				$params['data']             = $data;
				$params['currency_id']      = $rate->currency_id;
				$params['type']             = 'nform';
				$params['referral_items']   = $referral_items;
				$params['reference']        = $form_id;
				$params['reference_amount'] = $amount;
				$params['integration']      = 'affiliates-ninja-forms';

				$rc->add_referral( $params );
			}
		}
	}

}
Affiliates_NF::init();
