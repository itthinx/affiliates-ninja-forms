<?php
/**
 * affiliates-ninja-forms.php
 *
 * Copyright (c) 2017-2022 "kento" Karim Rahimpur www.itthinx.com
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
 * Plugin Name: Affiliates Ninja Forms
 * Plugin URI: https://www.itthinx.com/plugins/affiliates-ninja-forms/
 * Description: Integrates <a href="https://wordpress.org/plugins/affiliates/">Affiliates</a>, <a href="https://www.itthinx.com/shop/affiliates-pro/">Affiliates Pro</a> and <a href="https://www.itthinx.com/shop/affiliates-enterprise/">Affiliates Enterprise</a> with <a href="https://wordpress.org/plugins/ninja-forms/">Ninja Forms</a>.
 * Author: itthinx
 * Author URI: https://www.itthinx.com/
 * Donate-Link: https://www.itthinx.com/shop/
 * License: GPLv3
 * Version: 3.2.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AFFILIATES_NINJA_FORMS_PLUGIN_VERSION', '3.2.0' );

/**
 * Plugin boot.
 */
function affiliates_ninja_forms_plugins_loaded() {
	if ( class_exists( 'Affiliates' ) && class_exists( 'NF_Abstracts_Action' ) ) {
		define( 'AFFILIATES_NINJA_FORMS_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'AFFILIATES_NINJA_FORMS_LIB', AFFILIATES_NINJA_FORMS_DIR . '/lib' );
		define( 'AFFILIATES_NINJA_FORMS_PLUGIN_URL', plugins_url( 'affiliates-ninja-forms' ) );
		require_once AFFILIATES_NINJA_FORMS_LIB . '/class-affiliates-ninja-forms.php';
	}
}
add_action( 'plugins_loaded', 'affiliates_ninja_forms_plugins_loaded' );
