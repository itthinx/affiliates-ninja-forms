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
 * @link https://developer.ninjaforms.com/codex/registering-actions/
 */
class Affiliates_NF_Registration_Action extends NF_Abstracts_Action {

	/**
	 * @var string action name
	 */
	protected $_name     = 'affiliates_registration';

	/**
	 * @var string name for humans (translatable, set in constructor)
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
		add_filter( 'ninja_forms_display_fields', array( __CLASS__, 'ninja_forms_display_fields' ) );
		// add_filter( 'ninja_forms_display_form_settings', array( __CLASS__, 'ninja_forms_display_form_settings' ), 10, 2 );
		add_action( 'ninja_forms_output_templates', array( __CLASS__, 'ninja_forms_output_templates' ), 99999 );
		add_filter( 'ninja_forms_display_show_form', array( __CLASS__, 'ninja_forms_display_show_form' ), 10, 3 );
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
		if ( class_exists( 'NF_UserManagement_Actions_RegisterUser' ) ) {
			$r = new NF_UserManagement_Actions_RegisterUser();
			if ( intval( $this->_priority ) <= $r->get_priority() ) {
				$this->_priority = '' . ( $r->get_priority() + 10 );
			}
		}
		$this->_priority = apply_filters( 'affiliates_ninja_forms_affiliates_nf_registration_action_priority', $this->_priority );
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
				),
				array(
					'name' => 'affiliates_enable_registration_login',
					'label' => __( 'Automatic login', 'affiliates-ninja-forms' ),
					'type'  => 'toggle',
					'group' => 'primary',
					'help'  =>
						__( 'Automatically logs new users in upon successful registration.', 'affiliates-ninja-forms' ) .
						( class_exists( 'NF_UserManagement' ) ? ' ' . __( 'If registration via the <strong>Register User</strong> action is enabled, this option does not apply. Use the corresponding option of the <strong>Register User</strong> action instead.', 'affiliates-ninja-forms' ) : '' ),
					'width' => 'full'
				)
			)
		);

		if ( class_exists( 'NF_UserManagement' ) ) {
			$this->_settings['affiliates_registration']['settings'][] = array(
				'name'  => 'affiliates_enable_user_management_registration',
				'label' => __( 'Enable Registration via the <strong>Register User</strong> action', 'affiliates-ninja-forms' ),
				'type'  => 'toggle',
				'group' => 'primary',
				'help'  =>
					__( 'Allow affiliates to register through this form using the <strong>Register User</strong> action - this requires the <strong>User Management</strong> Add-On.', 'affiliates-ninja-forms' ) .
					' ' .
					__( 'The <strong>Affiliates Registration</strong> action will normally create a user account for new affiliates, but if this is enabled, the user account is created by the <strong>Register User</strong> action instead.', 'affiliates-ninja-forms' )
					,
				'width' => 'full'
			);
		}

		$this->_settings['affiliates_registration']['settings'][] = array(
			'name'           => 'affiliates_sign_up_field',
			'label'          => __( 'Opt in', 'affiliates-ninja-forms' ),
			'type'           => 'textbox',
			'group'          => 'primary',
			'help'           =>
			__( 'You can use a checkbox to let the user choose whether to sign up as an affiliate or not.', 'affiliates-ninja-forms' ) .
			' ' .
			__( 'The field <strong>must</strong> be of type <em>Single Checkbox</em>, appropriately labelled, usually inviting the user to <em>&quot;Join the Affiliate Program&quot;</em>.', 'affiliates-ninja-forms' ) .
			' ' .
			__( 'If the user does not opt in, the form submission will simply allow to create the user account (without joining the affiliate program).', 'affiliates-ninja-forms' ),
			'placeholder'    => __( 'Choose a field &hellip;', 'affiliates-ninja-forms' ),
			'value'          => '',
			'width'          => 'full',
			'use_merge_tags' => array(
				'include' => array(
					'fields'
				),
				'exclude' => array(
					'user',
					'post',
					'system',
					'calculations'
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
					if ( $field['enabled'] ) {
						$this->_settings['affiliates_registration_mapping']['settings'][] = array(
							'name'           => self::get_mapped_affiliates_field_name( $name ),
							'label'          => sprintf(
								__( 'Affiliates Field : %s%s', 'affiliates-ninja-forms' ),
								esc_html__( $field['label'], 'affiliates-ninja-forms' ),
								$field['required'] ? ' <span class="required">*</span>' : ''
							),
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
					'value' =>
						'<p>' .
						sprintf(
							__( 'Here you can relate fields defined in the Affiliates <a href="%s">Registration</a> settings with fields on this form.', 'affiliates-ninja-forms' ),
							esc_url( add_query_arg( 'section', 'registration', admin_url( 'admin.php?page=affiliates-admin-settings' ) ) )
						) .
						'</p>' .
						'<p>' .
						__( 'Required affiliate registration fields are marked with <span class="required">*</span>, these must be mapped to required form fields to allow successful form submissions.', 'affiliates-ninja-forms' ) .
						'</p>'
				);
			}
		}

	}

	/**
	 * Would run checks for field consistency, currently not used.
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
			if ( defined( 'AFFILIATES_CORE_LIB' ) ) {
				$user = wp_get_current_user();
				$registration_fields = self::get_affiliates_registration_fields();
				if ( count( $registration_fields ) > 0 ) {
					$userdata = array();
					foreach ( $registration_fields as $name => $field ) {
						if ( $field['enabled'] ) {
							$field_name = self::get_mapped_affiliates_field_name( $name );
							switch( $name ) {
								case 'first_name' :
								case 'last_name' :
								case 'user_login' :
								case 'user_email' :
								case 'user_url' :
									if ( is_user_logged_in() ) {
										if ( !empty( $user->$name ) ) {
											$action[$field_name] = sanitize_user_field( $name, $user->$name, $user->ID, 'display' );
										}
									}
									break;
							}
							$field_value = !empty( $action[$field_name] ) ? $action[$field_name] : '';
							if ( $field_value !== null ) {
								$userdata[$name] = $field_value;
							}
						}
					}
					$affiliate_user_id = null;
					if ( !is_user_logged_in() ) {
						if ( empty( $action['affiliates_enable_user_management_registration'] ) || !$action['affiliates_enable_user_management_registration'] ) {
							do_action( 'affiliates_before_register_affiliate', $userdata );
							$affiliate_user_id = Affiliates_Registration::register_affiliate( $userdata );
							do_action( 'affiliates_after_register_affiliate', $userdata );
							if ( !is_wp_error( $affiliate_user_id ) && isset( $action['affiliates_enable_registration_login'] ) && $action['affiliates_enable_registration_login'] ) {
								wp_set_current_user( $affiliate_user_id, $userdata['user_login'] );
								wp_set_auth_cookie( $affiliate_user_id );
								do_action( 'wp_login', $userdata['user_login'], get_user_by( 'id', $affiliate_user_id ) );
							}
						}
					} else {
						$affiliate_user_id = $user->ID;
					}
					if ( !is_wp_error( $affiliate_user_id ) ) {
						if ( $affiliate_user_id !== null  ) {
							if ( empty( $action['affiliates_sign_up_field'] ) || strtolower( $action['affiliates_sign_up_field'] ) === 'checked' ) {
								$affiliate_id = Affiliates_Registration::store_affiliate( $affiliate_user_id, $userdata, $status );
								// update user including meta
								Affiliates_Registration::update_affiliate_user( $affiliate_user_id, $userdata );
								do_action( 'affiliates_stored_affiliate', $affiliate_id, $affiliate_user_id );
							}
						}
					} else {
						/**
						 * @var WP_Error $wp_error Affiliate registration errors.
						 */
						$wp_error = $affiliate_user_id;
						foreach ( $wp_error->get_error_codes() as $error_code ) {
							$data['errors']['form'][$error_code] = $wp_error->get_error_message( $error_code );
						}
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


	/**
	 * Use default values from the user object for certain registration fields and try to inhibit input if possible.
	 * This is just for display purposes.
	 *
	 * @param array $fields
	 * @return array fields
	 */
	public static function ninja_forms_display_fields( $fields ) {

		global $wpdb;

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$affiliates_registration_action = null;

			// Obtain the form id.
			foreach ( $fields as $field ) {
				if ( !empty( $field['id'] ) ) {
					$id = intval( $field['id'] );
					$form_id = $wpdb->get_var( $wpdb->prepare(
						"SELECT parent_id from {$wpdb->prefix}nf3_fields WHERE id = %d", $id
					) );
					if ( $form_id !== null ) {
						$factory = Ninja_Forms()->form( $form_id );
						$actions = $factory->get_actions();
						/**
						 * @var NF_Database_Model_Action $action related form action
						 */
						foreach( $actions as $action ) {
							if ( $action->get_setting( 'type' ) === 'affiliates_registration' ) {
								$enabled = $action->get_setting( 'affiliates_enable_registration' );
								if ( $enabled ) {
									$affiliates_registration_action = $action;
									break;
								}
							}
						}
					}
				}
			}

			// These are fields for an affiliate registration form. Check field mappings and set values from the user object.
			if ( $affiliates_registration_action!== null ) {
				$registration_fields = self::get_affiliates_registration_fields();
				if ( count( $registration_fields ) > 0 ) {
					$mapped_fields = array();
					foreach ( $registration_fields as $name => $registration_field ) {
						$field_name = self::get_mapped_affiliates_field_name( $name );
						$field_value = $affiliates_registration_action->get_setting( $field_name );
						if ( preg_match( '/{field:(.*?)}/', trim( $field_value ), $matches ) ) {
							if ( !empty( $matches ) && !empty( $matches[1] ) ) {
								$mapped_field_name = $matches[1];
								$mapped_fields[$mapped_field_name] = $name;
							}
						}
					}
					if ( count( $mapped_fields ) > 0 ) {
						for ( $i = 0; $i < count( $fields ); $i++ ) {
							$key = !empty( $fields[$i]['key'] ) ? $fields[$i]['key'] : null;
							if ( isset( $mapped_fields[$key] ) ) {
								switch( $mapped_fields[$key] ) {
									case 'first_name' :
									case 'last_name' :
									case 'user_login' :
									case 'user_email' :
									case 'user_url' :
									case 'password' :
										$name = $mapped_fields[$key];
										if ( !empty( $user->$name ) || $name === 'password' ) {
											$fields[$i]['disable_input'] = true;
											$field_jquery = sprintf(
												'jQuery("#nf-field-%d").attr("readonly","readonly").attr("disabled","disabled");',
												intval( $fields[$i]['id'] )
											);
											global $affiliates_ninja_forms_field_jquery;
											$affiliates_ninja_forms_field_jquery[] = $field_jquery;
											// we can't add this to $fields[$i]['afterField'] as escaping is applied
											if ( $name !== 'password' ) {
												$fields[$i]['value'] = sanitize_user_field( $name, $user->$name, $user->ID, 'display' );
											} else {
												$fields[$i]['value'] = '********';
											}
										}
										break;
								}
							}
							// also disable any password confirm field related to an affiliate registration password field
							$confirm_key = !empty( $fields[$i]['confirm_field'] ) ? $fields[$i]['confirm_field'] : null;
							if ( $confirm_key !== null ) {
								if ( isset( $mapped_fields[$confirm_key] ) ) {
									if ( $mapped_fields[$confirm_key] === 'password' ) {
										$fields[$i]['disable_input'] = true;
										$field_jquery = sprintf(
											'jQuery("#nf-field-%d").attr("readonly","readonly").attr("disabled","disabled");',
											intval( $fields[$i]['id'] )
										);
										global $affiliates_ninja_forms_field_jquery;
										$affiliates_ninja_forms_field_jquery[] = $field_jquery;
										$fields[$i]['value'] = '********';
									}
								}
							}
						}
					}
				}
			}
		}
		return $fields;
	}

	/**
	 * Sets selected affiliate registration fields as readonly and disabled.
	 * This is needed because disable_input does not work on some of them.
	 */
	public static function ninja_forms_output_templates() {
		global $affiliates_ninja_forms_field_jquery;
		// this needs to be hooked on nfFormReady, document ready will not work
		if ( !empty( $affiliates_ninja_forms_field_jquery ) ) {
			echo '<script id="affiliates-ninja-forms-field-jquery" type="text/javascript">';
			echo 'if ( typeof jQuery !== "undefined" ) {';
			echo 'jQuery(document).on("nfFormReady",function(e,layoutView){';
			echo implode( "\n", $affiliates_ninja_forms_field_jquery );
			echo '});';
			echo '}';
			echo '</script>';
		}
	}

	/**
	 * Hook - currently not used.
	 *
	 * @param array $settings form settings
	 * @param int $form_id form ID
	 * @return array
	 */
	public static function ninja_forms_display_form_settings( $settings, $form_id ) {
		return $settings;
	}

	/**
	 * Avoids rendering a form that is enabled for affiliate registration when the user is already an affiliate.
	 *
	 * @param boolean $show whether the form should be shown
	 * @param int $form_id the form ID
	 * @param object $form the form object
	 *
	 * @return boolean
	 */
	public static function ninja_forms_display_show_form( $show, $form_id, $form ) {
		if ( $show && is_user_logged_in() ) {
			if ( !empty( $form_id ) ) {
				$factory = Ninja_Forms()->form( $form_id );
				$actions = $factory->get_actions();
				/**
				 * @var NF_Database_Model_Action $action related form action
				 */
				foreach( $actions as $action ) {
					if ( $action->get_setting( 'type' ) === 'affiliates_registration' ) {
						$enabled = $action->get_setting( 'affiliates_enable_registration' );
						if ( $enabled ) {
							$affiliates_registration_action = $action;
							if (
								affiliates_user_is_affiliate() ||
								affiliates_user_is_affiliate_status( null, 'pending' ) ||
								affiliates_user_is_affiliate_status( null, 'deleted' )
							) {
								$show = false;
							}
							break;
						}
					}
				}
			}
		}
		return $show;
	}

	/**
	 * Returns the mapped name.
	 *
	 * @param string $name field name
	 * @return string
	 */
	private static function get_mapped_affiliates_field_name( $name ) {
		return sprintf( 'affiliates_field_%s', $name );
	}
}

Affiliates_NF_Registration_Action::init();
