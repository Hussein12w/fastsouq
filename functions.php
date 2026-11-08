<?php
/** FastSouq theme bootstrap. */
defined( 'ABSPATH' ) || exit;

define( 'QS_VERSION', '1.1.0' );
define( 'QS_DIR', get_template_directory() );
define( 'QS_URI', get_template_directory_uri() );

require QS_DIR . '/inc/icon-paths.php';
require QS_DIR . '/inc/i18n.php';
require QS_DIR . '/inc/data.php';
require QS_DIR . '/inc/makes-ar.php';
require QS_DIR . '/inc/setup.php';
require QS_DIR . '/inc/ui.php';
require QS_DIR . '/inc/browse.php';
require QS_DIR . '/inc/forms.php';
require QS_DIR . '/inc/promotions.php';
require QS_DIR . '/inc/admin.php';
require QS_DIR . '/inc/customizer.php';
require QS_DIR . '/inc/seed.php';

add_action(
	'after_switch_theme',
	function () {
		qs_register_content();
		qs_create_pages();
		qs_seed_terms();
		flush_rewrite_rules();
	}
);

/* Moving from the "Quick Sale" theme: keep its Customizer settings (logo, hero image, contact numbers…). */
add_action(
	'after_switch_theme',
	function ( $old_name, $old_theme = null ) {
		$mine = get_option( 'theme_mods_' . get_stylesheet() );
		if ( is_array( $mine ) && count( array_filter( array_keys( $mine ), fn( $k ) => 0 === strpos( (string) $k, 'qs_' ) ) ) ) {
			return; // already has its own settings
		}
		$candidates = array( 'quick-sale' );
		if ( $old_theme instanceof WP_Theme ) {
			array_unshift( $candidates, $old_theme->get_stylesheet() );
		}
		foreach ( array_unique( $candidates ) as $slug ) {
			$old = get_option( 'theme_mods_' . $slug );
			if ( is_array( $old ) && array_filter( array_keys( $old ), fn( $k ) => 0 === strpos( (string) $k, 'qs_' ) ) ) {
				unset( $old['nav_menu_locations'], $old['qs_logo'], $old['custom_logo'] ); // menus are re-assigned by WordPress; the old Quick Sale logo is replaced by FastSouq's
				update_option( 'theme_mods_' . get_stylesheet(), array_merge( $old, is_array( $mine ) ? $mine : array() ) );
				return;
			}
		}
	},
	5,
	2
);
