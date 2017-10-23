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
	const REFERRAL_TYPE                = 'nform';

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
	 * Loads the classes according to the Affiliates version.
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		require_once AFFILIATES_NINJA_FORMS_LIB . '/class-affiliates-nf-admin.php';
		require_once AFFILIATES_NINJA_FORMS_LIB . '/class-affiliates-nf.php';
		include_once AFFILIATES_NINJA_FORMS_LIB . '/class-affiliates-nf-action.php';
	}
}
Affiliates_Ninja_Forms::init();
