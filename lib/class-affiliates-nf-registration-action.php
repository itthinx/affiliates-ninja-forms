<?php
/**
 * class-affiliates-nf-registration-action.php
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
 * Adds the Affiliates Registration action to Ninja Forms.
 *
 * @link http://developer.ninjaforms.com/codex/registering-actions/
 */
class Affiliates_NF_Registration_Action extends NF_Abstracts_Action {

	protected $_name     = 'affiliates_registration';
	protected $_nicename = '';
	protected $_tags     = array( 'affiliate', 'affiliates', 'affiliates pro', 'affiliates enterprise', 'itthinx', 'referral', 'referrals', 'lead', 'leads', 'registration', 'growth', 'growthhacking', 'growthmarketing' );
	protected $_timing   = 'late';
	protected $_priority = '10';

	/**
	 * Adds our hook to register our action.
	 */
	public static function init() {
		add_action( 'ninja_forms_register_actions', array( __CLASS__, 'ninja_forms_register_actions' ) );

		// @todo remove ?
		//add_filter( 'ninja_forms_display_fields', array( __CLASS__, 'ninja_forms_display_fields' ) );

		// @todo keep ?
		// $settings = apply_filters( 'ninja_forms_display_form_settings', $settings, $form_id );
		add_filter( 'ninja_forms_display_form_settings', array( __CLASS__, 'ninja_forms_display_form_settings' ), 10, 2 );
	}

	/**
	 * Add our Affiliates action.
	 *
	 * @param array $actions current actions
	 * @return array with our action added
	 */
	public static function ninja_forms_register_actions( $actions ) {
		$actions['affiliates_registration'] = new Affiliates_NF_Registration_Action();
		return $actions;
	}

	/**
	 * Create a new instance. Registers our settings.
	 */
	public function __construct() {
		parent::__construct();
		$this->_nicename = __( 'Affiliates Registration', 'affiliates-ninja-forms' );
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

		if ( defined( 'AFFILIATES_CORE_LIB' ) ) {
			$this->_settings['affiliates_registration_mapping'] = array(
				'name'     => 'affiliates_registration_mapping',
				'type'     => 'fieldset',
				'label'    => __( 'Affiliates Registration Field Mapping', 'affiliates-ninja-forms' ),
				'width'    => 'full',
				'group'    => 'primary',
				'settings' => array()
			);
			$registration_fields = self::get_affiliates_registration_fields();
			if ( count( $registration_fields ) > 0 ) {
				foreach ( $registration_fields as $name => $field ) {
					if ( $field['enabled'] || $field['obligatory'] ) {
						$this->_settings['affiliates_registration_mapping']['settings'][] = array(
							'name'           => self::get_mapped_affiliates_field_name( $name ),
							'label'          => sprintf( __( 'Affiliates Field : %s', 'affiliates-ninja-forms' ), esc_html__( $field['label'], 'affiliates-ninja-forms' ) ),
							'type'           => 'textbox',
							'group'          => 'primary',
							'help'           => __( 'Choose the form field that is mapped to this affiliate registration field.', 'affiliates-ninja-forms' ),
							'placeholder'    => __( 'Choose a field &hellip;', 'affiliates-ninja-forms' ),
							'value'          => '',
							'width'          => 'full',
							'required'       => $field['required'],
							'use_merge_tags' => array(
								'include' => array(
									'user',
									'fields'
								),
								'exclude' => array(
									'post',
									'system',
									'calculations'
								)
							)
						);
					}
				}
				// 'help' doesn't show on fieldset type so we add it like this
				$this->_settings['affiliates_registration_mapping']['settings'][] = array(
					'name' => 'affiliates_field_mapping_help',
					'label' => '',
					'type' => 'html',
					'value' => sprintf(
						__( 'Here you can relate fields defined in the Affiliates <a href="%s">Registration</a> settings with fields on this form.', 'affiliates-ninja-forms' ),
						esc_url( add_query_arg( 'section', 'registration', admin_url( 'admin.php?page=affiliates-admin-settings' ) ) )
					)
				);
			}
		}
	}

	/**
	 * Basic checks for field consistency.
	 *
	 * {@inheritDoc}
	 * @see NF_Abstracts_Action::save()
	 */
	public function save( $action_settings ) {
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
		if ( !empty( $action['affiliates_enable_registration'] ) ) {
			$status = isset( $action['affiliates_affiliate_status'] ) ? $action['affiliates_affiliate_status'] : get_option( 'aff_status', 'active' );
			// @todo implement
			
			error_log(__METHOD__. ' action = ' . var_export($action,true)); // @todo remove
			error_log(__METHOD__. ' form_id = ' . var_export($form_id,true)); // @todo remove
			error_log(__METHOD__. ' data = ' . var_export($data,true)); // @todo remove
			error_log(__METHOD__. ' factory = ' . var_export($factory,true)); // @todo remove
			error_log(__METHOD__. ' sub_id = ' . var_export($sub_id,true)); // @todo remove
			error_log(__METHOD__. ' sub = ' . var_export($sub,true)); // @todo remove

			if ( defined( 'AFFILIATES_CORE_LIB' ) ) {
				$user = wp_get_current_user();
				$registration_fields = self::get_affiliates_registration_fields();
				if ( count( $registration_fields ) > 0 ) {
					$userdata = array();
					foreach ( $registration_fields as $name => $field ) {
						if ( $field['enabled'] || $field['obligatory'] ) {
							$field_name = self::get_mapped_affiliates_field_name( $name );
							$field_value = isset( $action[$field_name] ) ? $action[$field_name] : null;
							if ( $value !== null ) {
								$userdata[$name] = $action[$field_name];
								// @todo : handle case when user is logged in (don't render fields and take fields from user object instead)
							}
						}
					}
					if ( !is_user_logged_in() ) {
						do_action( 'affiliates_before_register_affiliate', $userdata );
						$affiliate_user_id = Affiliates_Registration::register_affiliate( $userdata );
						do_action( 'affiliates_after_register_affiliate', $userdata );
					} else {
						$affiliate_user_id = $user->ID;
					}
					if ( !is_wp_error( $affiliate_user_id ) ) {
						$affiliate_id = Affiliates_Registration::store_affiliate( $affiliate_user_id, $userdata, $status );
						// update user including meta
						Affiliates_Registration::update_affiliate_user( $affiliate_user_id, $userdata );
						do_action( 'affiliates_stored_affiliate', $affiliate_id, $affiliate_user_id );
					}
				}
			}
		}
	}

	/**
	 * Returns an array with registration fields from Affiliates > Settings > Registration.
	 *
	 * @return array of affiliate registration fields
	 */
	private static function get_affiliates_registration_fields() {
		$registration_fields = array();
		if ( defined( 'AFFILIATES_CORE_LIB' ) ) {
			include_once AFFILIATES_CORE_LIB . '/class-affiliates-settings.php';
			include_once AFFILIATES_CORE_LIB . '/class-affiliates-settings-registration.php';
			if ( class_exists( 'Affiliates_Settings_Registration' ) && method_exists( 'Affiliates_Settings_Registration', 'get_fields' ) ) {
				$registration_fields = Affiliates_Settings_Registration::get_fields();
			}
		}
		return $registration_fields;
	}

	// @todo remove ?
	public static function ninja_forms_display_fields( $fields ) {
		error_log(var_export($fields,true)); // @todo remove
		return $fields;
	}

	// @todo keep ?
	public static function ninja_forms_display_form_settings( $settings, $form_id ) {
		error_log(' form_id = ' . var_export($form_id,true)); // @todo remove
		error_log(' settings : ' . var_export($settings,true)); // @todo remove
		return $settings;
	}

	private static function get_mapped_affiliates_field_name( $name ) {
		return sprintf( 'affiliates_field_%s', $name );
	}
}

Affiliates_NF_Registration_Action::init();
