<?php
/**
 * class-affiliates-nf-action.php
 *
 * Copyright (c) "kento" Karim Rahimpur www.itthinx.com
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

	/**
	 * @var string action name
	 */
	protected $_name     = 'affiliates';

	/**
	 * @var string action name for humans (translatable, set in constructor)
	 */
	protected $_nicename = '';

	/**
	 * @var array tags related to the action
	 */
	protected $_tags     = array( 'affiliate', 'affiliates', 'affiliates pro', 'affiliates enterprise', 'itthinx', 'referral', 'referrals', 'lead', 'leads', 'registration', 'growth', 'growthhacking', 'growthmarketing' );

	/**
	 * @var string action timing (late)
	 */
	protected $_timing   = 'late';

	/**
	 * @var string action priority
	 */
	protected $_priority = '10';

	/**
	 * Adds our hook to register our action.
	 */
	public static function init() {
		add_action( 'ninja_forms_register_actions', array( __CLASS__, 'ninja_forms_register_actions' ) );
		add_action( 'delete_post', array( __CLASS__, 'delete_post' ) );
		add_action( 'wp_trash_post', array( __CLASS__, 'wp_trash_post' ) );
		add_action( 'untrash_post', array( __CLASS__, 'untrash_post' ) );
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
	 * Returns the currency for the form or the default currency.
	 * If no $form_id is given or the form has no specific currency, the default currency is used.
	 * If no default currency is obtained, USD is returned.
	 *
	 * @param int $form_id form ID
	 * @return boolean|string|number
	 */
	private static function get_currency( $form_id = null ) {
		$currency = 'USD';
		if ( $form_id !== null ) {
			$currency = Ninja_Forms()->form( $form_id )->get()->get_setting( 'currency' );
		}
		if ( empty( $currency ) ) {
			$currency = Ninja_Forms()->get_setting( 'currency' );
		}
		return $currency;
	}

	/**
	 * Create a new instance. Registers our settings.
	 */
	public function __construct() {
		parent::__construct();
		$this->_nicename = __( 'Affiliates', 'affiliates-ninja-forms' );
		$this->_priority = apply_filters( 'affiliates_ninja_forms_affiliates_nf_action_priority', $this->_priority );
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
					/**
					 * @var $rate Affiliates_Rate
					 */
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
			$currency = $this->get_currency( $form_id );
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

		if ( !empty( $action['affiliates_enable_referrals'] ) ) {
			$this->process_referral( $action, $form_id, $data, $factory, $sub_id, $sub );
		}

		return $data;
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
		$d        = affiliates_get_referral_amount_decimals();
		$currency = $this->get_currency( $form_id );
		$status   = isset( $action['affiliates_referral_status'] ) ? $action['affiliates_referral_status'] : get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );

		$description = sprintf( __( 'Ninja Forms form #%d submission #%d', 'affiliates-ninja-forms' ), intval( $form_id ), intval( $sub_id ) );

		$referral_data = array();
		$fields = $factory->get_fields();
		foreach( $fields as $field ) {
			$value = '';
			$key   = $field->get_setting( 'key' );
			$type  = $field->get_setting( 'type' );
			$label = $field->get_setting( 'label' );
			if ( $type === 'shipping' ) {
				$value = $field->get_setting( 'shipping_cost' );
			} else {
				$value = $sub->get_field_value( $key );
			}
			$referral_data[$key] = array(
				'title'  => $label,
				'domain' => 'affiliates',
				'value'  => $value
			);
		}

		$base_amount = !empty( $action['affiliates_base_amount'] ) ? $action['affiliates_base_amount'] : '';
		if ( $base_amount === '' ) { // automatic
			$total = bcadd( '0', '0', $d );
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
			}
			$base_amount = $total;
		} else {
			$base_amount = bcadd( $base_amount, '0', $d );
		}

		if ( $this->using_rates() ) {
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
						$amount = '0';
						switch ( $rate->type ) {
							case AFFILIATES_PRO_RATES_TYPE_AMOUNT :
								$amount = bcadd( '0', $rate->value, affiliates_get_referral_amount_decimals() );
								break;
							case AFFILIATES_PRO_RATES_TYPE_RATE :
								// check form for base_amount
								$amount = bcmul( $base_amount, $rate->value, affiliates_get_referral_amount_decimals() );
								break;
						}
						// split proportional total if multiple affiliates are involved
						if ( $n > 1 ) {
							$amount = bcdiv( $amount, $n, affiliates_get_referral_amount_decimals() );
						}

						$referral_item = new Affiliates_Referral_Item( array(
							'rate_id'     => $rate_id,
							'amount'      => $amount,
							'currency_id' => $rate->currency_id !== null ? $rate->currency_id : $currency,
							'type'        => 'nf_sub',
							'reference'   => $sub_id,
							'line_amount' => $amount,
							'object_id'   => $form_id
						) );
						$referral_items[] = $referral_item;
					}
					$params['post_id']          = $sub_id;
					$params['description']      = $description;
					$params['data']             = $referral_data;
					$params['currency_id']      = $rate->currency_id !== null ? $rate->currency_id : $currency;
					$params['type']             = Affiliates_Ninja_Forms::REFERRAL_TYPE; // 'nform'
					$params['referral_items']   = $referral_items;
					$params['reference']        = $sub_id;
					$params['reference_amount'] = $amount;
					$params['integration']      = 'affiliates-ninja-forms';

					$rc->add_referral( $params );
				}
			}
		} else {
			$referral_amount = !empty( $action['affiliates_referral_amount'] ) ? $action['affiliates_referral_amount'] : '0';
			$referral_rate = !empty( $action['affiliates_referral_rate'] ) ? $action['affiliates_referral_rate'] : '0';
			if ( empty( $referral_amount ) && !empty( $referral_rate ) ) {
				$referral_amount = bcmul( $referral_rate, $base_amount, $d );
			}
			if ( class_exists( 'Affiliates_Referral_WordPress' ) ) {
				$r = new Affiliates_Referral_WordPress();
				$affiliate_id = $r->evaluate( $sub_id, $description, $referral_data, null, $referral_amount, $currency, $status, Affiliates_Ninja_Forms::REFERRAL_TYPE, $sub_id );
			} else {
				$affiliate_id = affiliates_suggest_referral( $sub_id, $description, $referral_data, $referral_amount, $currency, $status, Affiliates_Ninja_Forms::REFERRAL_TYPE, $sub_id );
			}
		}

	}

	/**
	 * Reject the referral.
	 *
	 * @param int $post_id
	 */
	public static function delete_post( $post_id ) {
		if ( get_post_type( $post_id ) === 'nf_sub' ) {
			if ( class_exists( 'Affiliates_Referral' ) && method_exists( 'Affiliates_Referral', 'get_ids_by_reference' ) ) {
				$referral_ids = Affiliates_Referral::get_ids_by_reference( $post_id );
				foreach ( $referral_ids as $referral_id ) {
					try {
						$referral = new Affiliates_Referral();
						if ( $referral->read( $referral_id ) ) {
							if ( $referral->status !== AFFILIATES_REFERRAL_STATUS_CLOSED ) {
								$referral->status = AFFILIATES_REFERRAL_STATUS_REJECTED;
								$referral->update();
							}
						}
					} catch ( Exception $ex ) {
					}
				}
			}
		}
	}

	/**
	 * Reject the referral.
	 *
	 * @param int $post_id
	 */
	public static function wp_trash_post( $post_id ) {
		if ( get_post_type( $post_id ) === 'nf_sub' ) {
			self::delete_post( $post_id );
		}
	}

	/**
	 * Update the referral status to its default.
	 *
	 * @param int $post_id
	 */
	public static function untrash_post( $post_id ) {
		if ( get_post_type( $post_id ) === 'nf_sub' ) {
			if ( class_exists( 'Affiliates_Referral' ) && method_exists( 'Affiliates_Referral', 'get_ids_by_reference' ) ) {
				/**
				 * The factory object used to obtain the submission object.
				 *
				 * @var NF_Abstracts_ModelFactory $factory
				 */
				$factory = Ninja_Forms()->form();
				if ( method_exists( $factory, 'get_sub' ) ) {
					if ( $sub = $factory->get_sub( $post_id ) ) {
						$form = Ninja_Forms()->form( $sub->get_form_id() )->get();
						if ( $form->get_id() ) {
							$status = $form->get_setting( 'affiliates_referral_status' );
							if ( empty( $status ) ) {
								$status = get_option( 'aff_default_referral_status', AFFILIATES_REFERRAL_STATUS_ACCEPTED );
							}
							// reset the referral status
							$referral_ids = Affiliates_Referral::get_ids_by_reference( $post_id );
							foreach ( $referral_ids as $referral_id ) {
								try {
									$referral = new Affiliates_Referral();
									if ( $referral->read( $referral_id ) ) {
										if ( $referral->status !== AFFILIATES_REFERRAL_STATUS_CLOSED ) {
											$referral->status = $status;
											$referral->update();
										}
									}
								} catch ( Exception $ex ) {
								}
							}
						}
					}
				}

			}
		}
	}

}

Affiliates_NF_Action::init();
