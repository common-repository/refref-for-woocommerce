<?php
/**
 * Plugin Name: RefRef For WooCommerce
 * Plugin URI: https://refref.app
 * Description: RefRef is a consumer-focused referral program for consumers to earn cash from their referrals and marketplace that features growing businesses and brands.
 * Author: nelsontky
 * Version: 1.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

if ( ! class_exists( 'RefRef_For_WooCommerce' ) ) :

class RefRef_For_WooCommerce {

	/**
	* Construct the plugin.
	*/
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), [$this, 'refref_plugin_links']);
	}

	/**
	* Initialize the plugin.
	*/
	public function init() {

		// Checks if WooCommerce is installed.
		if ( class_exists( 'WC_Integration' ) ) {
			// Include our integration class.
			include_once 'includes/class-wc-refref-integration.php';

			// Register the integration.
			add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
		} else {
			// throw an admin error if you like
		}
	}

	/**
	 * Add a new integration to WooCommerce.
	 */
	public function add_integration( $integrations ) {
		$integrations[] = 'WC_RefRef_Integration';
		return $integrations;
	}

	public function refref_plugin_links($links) {
		$refref_tab_url = "admin.php?page=wc-settings&tab=integration&section=wc-refref-integration";
		$settings_link = "<a href='". esc_url( get_admin_url(null, $refref_tab_url) ) ."'>Settings</a>";

		array_unshift($links, $settings_link);

		return $links;
	}
}

$RefRef_For_WooCommerce = new RefRef_For_WooCommerce( __FILE__ );

endif;
