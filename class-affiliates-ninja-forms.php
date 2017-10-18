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
 * Integration for Ninja Forms.
 */
class Affiliates_Ninja_Forms {

	const NINJA_FORMS_INTEGRATION_NAME = 'affiliates-ninjaforms';
	const NINJA_FORMS_POST_TYPE        = 'ninja_forms';
	const PLUGIN_OPTIONS               = 'affiliates_ninja_forms';
	const REFERRAL_TYPE                = 'nf3_forms'; // Ninja Forms database table name.

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
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
	}

	/**
	 * Loads the classes according to the Affiliates version.
	 */
	public static function wp_init() {
		if (
			defined( 'AFFILIATES_EXT_VERSION' ) &&
			version_compare( AFFILIATES_EXT_VERSION, '3.0.0' ) >= 0 &&
			class_exists( 'Affiliates_Referral' ) &&
			(
				!defined( 'Affiliates_Referral::DEFAULT_REFERRAL_CALCULATION_KEY' ) ||
				!get_option( Affiliates_Referral::DEFAULT_REFERRAL_CALCULATION_KEY, null )
			)
		) {
			$comp = '/comp';
		} else {
			$comp = '/comp-2';
		}
		if ( !defined( 'AFFILIATES_NINJA_FORMS_COMP_LIB' ) ) {
			define( 'AFFILIATES_NINJA_FORMS_COMP_LIB', AFFILIATES_NINJA_FORMS_CORE_DIR . '/lib' . $comp );
		}
		require_once AFFILIATES_NINJA_FORMS_COMP_LIB . '/class-affiliates-nf-admin.php';
		require_once AFFILIATES_NINJA_FORMS_COMP_LIB . '/class-affiliates-nf.php';
	}
}
Affiliates_Ninja_Forms::init();
