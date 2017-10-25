<?php
/**
 * class-affiliates-nf-settings.php
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
 * @author itthinx
 * @package affiliates-ninja-forms
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Affiliates integration admin sections.
 */
class Affiliates_NF_Admin {

	const NONCE             = 'aff_ninjaforms_admin_nonce';
	const SET_ADMIN_OPTIONS = 'set_admin_options';

	/**
	 * Initialization action on WordPress init.
	 */
	public static function init() {
		if ( current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
			add_action( 'affiliates_admin_menu', array( __CLASS__, 'affiliates_admin_menu' ) );
			// http://developer.ninjaforms.com/codex/registering-plugin-settings/
			add_filter( 'ninja_forms_plugin_settings', array( __CLASS__, 'ninja_forms_plugin_settings' ) );
			add_filter( 'ninja_forms_plugin_settings_groups', array( __CLASS__, 'ninja_forms_plugin_settings_groups' ) );
			
		}
	}

	/**
	 * Renders the setting section under Ninja Forms > Settings for our Affiliates integration.
	 *
	 * @param array $settings current settings
	 *
	 * @return array with our settings section added
	 */
	public static function ninja_forms_plugin_settings( $settings ) {
		$settings['affiliates'] = array(
			'affiliates' => array(
				'id'    => 'affiliates',
				'type'  => 'desc',
				'label' => __( 'Affiliates Integration', 'affiliates-ninja-forms' ),
				'desc'  => self::get_info()
			),
		);
		return $settings;
	}

	/**
	 * Adds our settings group under Ninja Forms > Settings.
	 *
	 * @param array $groups current groups
	 *
	 * @return array with our group added
	 */
	public static function ninja_forms_plugin_settings_groups( $groups ) {
		$groups['affiliates'] = array(
			'id'    => 'affiliates',
			'label' => __( 'Affiliates', 'affiliates-ninja-forms' )
		);
		return $groups;
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
		$options = get_option( Affiliates_Ninja_Forms::PLUGIN_OPTIONS , array() );
		if ( isset( $_POST['submit'] ) ) {
			if ( wp_verify_nonce( $_POST[self::NONCE], self::SET_ADMIN_OPTIONS ) ) {
				// currently nothing needed here
			}
			update_option( Affiliates_Ninja_Forms::PLUGIN_OPTIONS, $options );
		}

		// css
		echo '<style type="text/css">';
		echo 'div.field { padding: 0 1em 1em 0; }';
		echo 'div.field span.label { display: inline-block; width: 20%; }';
		echo 'div.field span.description { display: block; }';
		echo 'div.buttons { padding-top: 1em; }';
		echo '</style>';

		echo '<div>';
		echo '<h2>';
		esc_html_e( 'Affiliates Ninja Forms Integration', 'affiliates-ninja-forms' );
		echo '</h2>';
		echo '</div>';

		echo '<div class="manage" style="padding:2em;margin-right:1em;">';

		echo self::get_info();

		echo sprintf( __( 'You can also review this information on the Ninja Forms <a href="%s">Settings</a> page.', 'affiliates-ninja-forms' ),  esc_url( admin_url( 'admin.php?page=nf-settings#ninja_forms_metabox_affiliates_settings' ) ) );

		echo '<p>';
		echo wp_nonce_field( self::SET_ADMIN_OPTIONS, self::NONCE, true, false );
		// echo '<input class="button-primary" type="submit" name="submit" value="' . esc_attr__( 'Save', 'affiliates-ninja-forms' ) . '"/>';
		echo '</p>';

		echo '</div>';
		echo '</form>';
		echo '</div>'; // .manage

		affiliates_footer();

	}

	/**
	 * Returns information on the integration.
	 *
	 * @return string
	 */
	private static function get_info() {
		return
			'<p>' .
			sprintf(
				__( 'You have the <strong>Affiliates</strong> integration by <a href="%s">itthinx</a> for Ninja Forms installed.', 'affiliates-ninja-forms' ),
				esc_url( 'https://www.itthinx.com/' )
			) .
			'</p>' .
			'<p>' .
			sprintf(
				__( 'It integrates <a href="%s">Affiliates</a>, <a href="%s">Affiliates Pro</a> and <a href="%s">Affiliates Enterprise</a> with <a href="%s">Ninja Forms</a>.', 'affiliates-ninja-forms' ),
				esc_url( 'https://wordpress.org/plugins/affiliates/' ),
				esc_url( 'https://www.itthinx.com/shop/affiliates-pro/' ),
				esc_url( 'https://www.itthinx.com/shop/affiliates-enterprise/' ),
				esc_url( 'https://wordpress.org/plugins/ninja-forms/' )
			) .
			'</p>' .
			'<p>' .
			 __( 'This integration features:', 'affiliates-ninja-forms' ) . '</li>' .
			'</p>' .
			'<ul style="list-style:inside">' .
			'<li>' . __( 'Affiliate Registration Forms: Allow affiliates to sign up through a form provided by Ninja Forms.', 'affiliates-ninja-forms' ) . '</li>' .
			'<li>' . __( 'Referrals and Leads: Allow affiliates to refer others to the site, record referrals to grant commissions on form submissions and gather leads.', 'affiliates-ninja-forms' ) . '</li>' .
			'</ul>' .
			'<p>' .
			__( 'To enable referrals for a form, add the <strong>Affiliates</strong> action to it.', 'affiliates-ninja-forms' ) .
			' ' .
			__( 'To allow affiliates to register through a form, add the <strong>Affiliates Registration</strong> action to it.', 'affiliates-ninja-forms' ) .
			'</p>' .
			'<p>' .
			__( 'Please refer to these documentation pages for more details:', 'affiliates-ninja-forms' ) .
			'<ul style="list-style:inside">' .
			'<li>' . sprintf( __( 'Integration with <a href="%s">Affiliates</a>', 'affiliates-ninja-forms' ), esc_url( 'http://docs.itthinx.com/document/affiliates/setup/settings/integrations/' ) ) .'</li>' .
			'<li>' . sprintf( __( 'Integration with <a href="%s">Affiliates Pro</a>', 'affiliates-ninja-forms' ), esc_url( 'http://docs.itthinx.com/document/affiliates-pro//setup/settings/integrations/' ) ) .'</li>' .
			'<li>' . sprintf( __( 'Integration with <a href="%s">Affiliates Enterprise</a>', 'affiliates-ninja-forms' ), esc_url( 'http://docs.itthinx.com/document/affiliates-enterprise/setup/settings/integrations/' ) ) .'</li>' .
			'</ul>' .
			'</p>';
	}
}
Affiliates_NF_Admin::init();
