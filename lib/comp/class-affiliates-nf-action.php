<?php
/**
 * class-affiliates-nf-action.php
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
 * @since 2.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the Affiliates action to Ninja Forms.
 *
 * @link http://developer.ninjaforms.com/codex/registering-actions/
 */
class Affiliates_NF_Action extends NF_Abstracts_Action {

	protected $_name = 'affiliates';
	protected $_nicename = '';
	protected $_tags = array( 'affiliate', 'affiliates', 'affiliates pro', 'affiliates enterprise', 'itthinx', 'referral', 'referrals', 'lead', 'leads', 'registration', 'growth', 'growthhacking', 'growthmarketing' );
	protected $_timing = 'late';
	protected $_priority = '10';

	/**
	 * Adds our hook to register our action.
	 */
	public static function init() {
		add_action( 'ninja_forms_register_actions', array( __CLASS__, 'ninja_forms_register_actions' ) );
	}

	/**
	 * Add our Affiliates action.
	 *
	 * @param array $actions current actions
	 * @return array with our action added
	 */
	public static function ninja_forms_register_actions( $actions ) {
		$actions['affiliates'] = new Affiliates_NF_Action();
		return $actions;
	}

	/**
	 * Returns true if we are using rates.
	 *
	 * @return boolean true if using rates
	 */
	private static function using_rates() {
		$using_rates = false;
		if (
			defined( 'AFFILIATES_EXT_VERSION' ) &&
			version_compare( AFFILIATES_EXT_VERSION, '3.0.0' ) >= 0 &&
			class_exists( 'Affiliates_Referral' ) &&
			(
				!defined( 'Affiliates_Referral::DEFAULT_REFERRAL_CALCULATION_KEY' ) ||
				!get_option( Affiliates_Referral::DEFAULT_REFERRAL_CALCULATION_KEY, null )
				)
		) {
			$using_rates = true;
		}
		return $using_rates;
	}

	/**
	 * Create a new instance. Registers our settings.
	 */
	public function __construct() {
		parent::__construct();
		$this->_nicename = __( 'Affiliates', 'affiliates-ninja-forms' );
		// $settings = Ninja_Forms::config( 'ActionAffiliatesSettings' );
		// $this->_settings = array_merge( $this->_settings, $settings );

		$this->_settings['affiliates_registration'] = array(
			'name' => 'affiliates_registration',
			'type' => 'fieldset',
			'label' => __( 'Registration', 'affiliates-ninja-forms' ),
			'width' => 'full',
			'group' => 'primary',
			'settings' => array(
				array(
					'name'  => 'affiliates_enable_registration',
					'label' => __( 'Enable Registration', 'affiliates-ninja-forms' ),
					'type'  => 'toggle',
					'group' => 'primary',
					'help'  => __( 'Allow affiliates to register through this form.', 'affiliates-ninja-forms' ),
					'width' => 'one-half'
				),
				array(
					'name'    => 'affiliates_affiliate_status',
					'label'   => __( 'Affiliate Status', 'affiliates-ninja-forms' ),
					'type'    => 'select',
					'group'   => 'primary',
					'help'    => __( 'The default status of affiliates who register through this form.', 'affiliates-ninja-forms' ),
					'value'   => $affiliate_status = get_option( 'aff_status', 'active' ),
					'options' => array(
						array( 'value' => 'active', 'label' => __( 'Active', 'affiliates-ninja-forms' ) ),
						array( 'value' => 'pending', 'label' => __( 'Pending', 'affiliates-ninja-forms' ) )
					),
					'width' => 'one-half'
				)
			)
		);

		$this->_settings['affiliates_referrals'] = array(
			'name' => 'affiliates_referrals',
			'type' => 'fieldset',
			'label' => __( 'Referrals', 'affiliates-ninja-forms' ),
			'width' => 'full',
			'group' => 'primary',
			'settings' => array(
				array(
					'name'  => 'affiliates_enable_referrals',
					'label' => __( 'Enable Referrals', 'affiliates-ninja-forms' ),
					'type'  => 'toggle',
					'group' => 'primary',
					'help'  => __( 'Allow affiliates to earn commissions on submissions of this form.', 'affiliates-ninja-forms' ),
					'width' => 'one-half'
				),
				array(
					'name'    => 'affiliates_referral_status',
					'label'   => __( 'Referral Status', 'affiliates-ninja-forms' ),
					'type'    => 'select',
					'group'   => 'primary',
					'help'    => __( 'The default status of referrals recorded for this form.', 'affiliates-ninja-forms' ),
					'value'   => get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED ),
					'options' => array(
						array( 'value' => AFFILIATES_REFERRAL_STATUS_ACCEPTED, 'label' => __( 'Accepted', 'affiliates-ninja-forms' ) ),
						array( 'value' => AFFILIATES_REFERRAL_STATUS_PENDING, 'label' => __( 'Pending', 'affiliates-ninja-forms' ) ),
						array( 'value' => AFFILIATES_REFERRAL_STATUS_REJECTED, 'label' => __( 'Rejected', 'affiliates-ninja-forms' ) ),
						array( 'value' => AFFILIATES_REFERRAL_STATUS_CLOSED, 'label' => __( 'Closed', 'affiliates-ninja-forms' ) )
					),
					'width' => 'one-half'
				),
				array(
					'name'           => 'affiliates_base_amount',
					'label'          => __( 'Transaction Base Amount', 'affiliates-ninja-forms' ),
					'type'           => 'textbox',
					'group'          => 'primary',
					'help'           => __( 'You can choose the field that is used to calculate the commission here. If left empty, the net amount will be calculated automatically, based on the form\'s total minus shipping.', 'affiliates-ninja-forms' ),
					'placeholder'    => __( 'Automatic', 'affiliates-ninja-forms' ),
					'value'          => '',
					'width'          => 'full',
					'use_merge_tags' => array(
						'exclude' => array(
							'post',
							'user',
							'system'
						)
					)
				)
			)
		);

		$form_id = isset( $_REQUEST['form_id'] ) && is_numeric( $_REQUEST['form_id'] ) ? $_REQUEST['form_id'] : null;
		if ( !self::using_rates() ) {
			$this->_settings['affiliates_referrals']['settings'][] = array(
				'name'  => 'affiliates_referral_amount',
				'label' => __( 'Referral Amount', 'affiliates-ninja-forms' ),
				'type'  => 'number',
				'help'  =>
					__( 'If a fixed amount is desired, input the referral amount to be credited for form submissions.', 'affiliates-ninja-forms' ) .
					' ' .
					__( 'Leave this empty if a commission based on the form total should be granted.', 'affiliates-ninja-forms' ),
				'width' => 'full'
			);
			$this->_settings['affiliates_referrals']['settings'][] = array(
				'name'  => 'affiliates_referral_rate',
				'label' => __( 'Referral Rate', 'affiliates-ninja-forms' ),
				'type'  => 'number',
				'help'  =>
					__( 'If the referral amount should be calculated based on the form total, input the rate to be used.', 'affiliates-ninja-forms' ) .
					' ' .
					__( 'For example, use 0.1 to grant a commission of 10%.', 'affiliates-ninja-forms' ) .
					' ' .
					__( 'Leave this empty if a fixed commission should be granted.', 'affiliates-ninja-forms' ),
				'width' => 'full'
			);
		} else {
			if ( $form_id !== null ) {
				$output = '';
				$rates = Affiliates_Rate::get_rates( array( 'integration' => 'affiliates-ninja-forms', 'object_id' => $form_id ) );
				if ( count( $rates ) > 0 ) {
					$output .= '<p>';
					$output .= esc_html( _n( 'This specific rate applies to this form.', 'These specific rates apply to this form.', count( $rates ), 'affiliates-ninja-forms' ) );
					$output .= '</p>';
					$odd      = true;
					$is_first = true;
					$output .= '<table style="width:100%">';
					foreach ( $rates as $rate ) {
						if ( $is_first ) {
							$output .= wp_kses_post( $rate->view( array( 'style' => 'table', 'titles' => true, 'exclude' => array( 'integration', 'term_id', 'object_id' ), 'prefix_class' => 'odd' ) ) );
						} else {
							$output .= wp_kses_post( $rate->view( array( 'style' => 'table', 'exclude' => array( 'integration', 'term_id', 'object_id' ), 'prefix_class' => $odd ? 'odd' : 'even' ) ) );
						}
						$is_first = false;
						$odd      = !$odd;
					}
					$output .= '</table>';
				} else {
					$output .= '<p>';
					$output .= esc_html( __( 'This form has no specific applicable rates.', 'affiliates-ninja-forms' ) );
					$output .= '</p>';
				}
				if ( current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
					$output .= '<p>';
					$url = wp_nonce_url( add_query_arg(
						array(
							'integration' => 'affiliates-ninja-forms',
							'action'      => 'create-rate',
							'object_id'   => $form_id
						),
						admin_url( 'admin.php?page=affiliates-admin-rates' )
					) );
					$output .= sprintf(
						'<a href="%s">',
						esc_url( $url )
					);
					$output .= esc_html__( 'Create a rate', 'affiliates-ninja-forms' );
					$output .= '</a>';
					$output .= '</p>';
					$output .= '<p class="description">';
					$output .= esc_html( __( 'Please save any changes before you click the link to create a rate or the link to a rate, as this will take you away from editing this form.', 'affiliates-ninja-forms' ) );
					$output .= '</p>';
				}
				$this->_settings['affiliates_referrals']['settings'][] = array(
					'name'  => 'affiliates_rates',
					'label' => __( 'Affiliates Rates', 'affiliates-ninja-forms' ),
					'type'  => 'html',
					'value' => $output,
					'group' => 'primary',
					'width' => 'full'
				);
			}
		}
		if ( $form_id !== null ) {
			$currency = Ninja_Forms()->form( $form_id )->get()->get_setting( 'currency' );
			if ( empty( $currency ) ) {
				$currency = Ninja_Forms()->get_setting( 'currency' );
			}
			$this->_settings['affiliates_referrals']['settings'][] = array(
				'name'  => 'affiliates_currency',
				'label' => __( 'Currency', 'affiliates-ninja-forms' ),
				'type'  => 'html',
				'value' => '<p class="description">' . sprintf( __( 'The currency used for this form is <strong>%s</strong>.', 'affiliates-ninja-forms' ), esc_html( $currency ) ) . '</p>',
				'group' => 'primary',
				'width' => 'full'
			);
		}
	}

	/**
	 * Basic checks for field consistency.
	 *
	 * {@inheritDoc}
	 * @see NF_Abstracts_Action::save()
	 */
	public function save( $action_settings ) {
		if ( !self::using_rates() ) {
			$amount = !empty( $action_settings['affiliates_referral_amount'] ) ? trim( $action_settings['affiliates_referral_amount'] ) : '';
			if ( !empty( $amount ) && floatval( $amount ) < 0 ) {
				$amount = '';
			}
			$rate = !empty( $action_settings['affiliates_referral_rate'] ) ? trim( $action_settings['affiliates_referral_rate'] ) : '';
			if ( !empty( $rate ) && floatval( $rate ) < 0 ) {
				$rate = '';
			}
			if ( !empty( $rate ) && floatval( $rate ) > 1 ) {
				$rate = '1';
			}
			if ( !empty( $amount ) && !empty( $rate ) ) {
				$rate = '';
			}
			$action_settings['affiliates_referral_amount'] = $amount;
			$action_settings['affiliates_referral_rate'] = $rate;
		}
		return $action_settings;
	}

	/**
	 * Handles the form submission for our action.
	 *
	 * @param $action array action settings (the abstract class declares this as $action_id)
	 * @param $form_id int ID of the processed form
	 * @param $data array form, submission and other data
	 *
	 * @return array $data
	 *
	 * {@inheritDoc}
	 * @see NF_Abstracts_Action::process()
	 */
	public function process( $action, $form_id, $data ) {

		// Don't act on preview submissions.
		if (
			isset( $data['settings'] ) &&
			isset( $data['settings']['is_preview'] ) &&
			$data['settings']['is_preview']
		) {
			return $data;
		}

		$sub    = null;
		$sub_id = null;
		if (
			isset( $data['actions'] ) &&
			isset( $data['actions']['save'] ) &&
			!empty( $data['actions']['save']['sub_id'] )
		) {
			$sub_id = $data['actions']['save']['sub_id'];
		}

		/**
		 * The factory object we'll use to obtain the submission object.
		 *
		 * @var NF_Abstracts_ModelFactory $form
		 */
		$factory = Ninja_Forms()->form( $form_id );
		if ( method_exists( $factory, 'get_sub' ) ) {
			$sub = $factory->get_sub( $sub_id );
		}

		if ( !empty( $action['affiliates_enable_registration'] ) ) {
			$this->process_registration( $action, $form_id, $data, $factory, $sub_id, $sub );
		}

		if ( !empty( $action['affiliates_enable_referrals'] ) ) {
			$this->process_referral( $action, $form_id, $data, $factory, $sub_id, $sub );
		}

		return $data;
	}

	/**
	 * Handle the affiliate registration request.
	 *
	 * @param array $action action settings
	 * @param int $form_id form ID
	 * @param array $data form, submission and other data
	 * @param NF_Abstracts_ModelFactory $factory form factory
	 * @param int $sub_id submission ID
	 * @param NF_Database_Models_Submission $sub submission object
	 */
	private function process_registration( &$action, &$form_id, &$data, &$factory, &$sub_id = null, &$sub = null ) {
		// @todo implement
	}

	/**
	 * Handle the referral request.
	 *
	 * @param array $action action settings
	 * @param int $form_id form ID
	 * @param array $data form, submission and other data
	 * @param NF_Abstracts_ModelFactory $factory form factory
	 * @param int $sub_id submission ID
	 * @param NF_Database_Models_Submission $sub submission object
	 */
	private function process_referral( &$action, &$form_id, &$data, &$factory, &$sub_id = null, &$sub = null ) {
		$currency = Ninja_Forms()->form( $form_id )->get()->get_setting( 'currency' );
		if ( empty( $currency ) ) {
			$currency = Ninja_Forms()->get_setting( 'currency' );
		}
		$status = isset( $action['affiliates_referral_status'] ) ? $action['affiliates_referral_status'] : get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );

		$total = bcadd( '0', '0', affiliates_get_referral_amount_decimals() );
		$fields = $factory->get_fields();
		foreach( $fields as $field ) {
			$value = '0';
			$key   = $field->get_setting( 'key' );
			$type  = $field->get_setting( 'type' );
			if ( $type === 'total' ) {
				$value = $sub->get_field_value( $key );
				$total = bcadd( $total, $value, affiliates_get_referral_amount_decimals() );
			}
			if ( $type === 'shipping' ) {
				// doesn't have a value
				// $value = $sub->get_field_value( $key );
				$value = $field->get_setting( 'shipping_cost' );
				$total = bcsub( $total, $value, affiliates_get_referral_amount_decimals() );
			}
// 			error_log(__METHOD__. ' ========== field value  = ' . var_export($value,true)); // @todo remove
// 			error_log(__METHOD__. ' ========== field key  = ' . var_export($key,true)); // @todo remove
// 			error_log(__METHOD__. ' ========== field type = ' . var_export($type,true)); // @todo remove
// 			error_log(__METHOD__. ' ========== total = ' . var_export($total,true)); // @todo remove
		}

// 		error_log(__METHOD__. ' ========== action = ' . var_export($action,true)); // @todo remove
// 		error_log(__METHOD__. ' ========== data   = ' . var_export($data,true)); // @todo remove
// 		error_log(__METHOD__. ' ========== sub    = ' . var_export($sub,true)); // @todo remove
		
	}

}

Affiliates_NF_Action::init();
