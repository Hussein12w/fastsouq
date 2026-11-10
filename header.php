<?php
/** Header: logo, search, language, account, post ad; mega menu with 6 main categories and "More". */
defined( 'ABSPATH' ) || exit;
$uid    = get_current_user_id();
$active = qs_nav_key();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'lang-' . qs_lang() ); ?>>
<?php wp_body_open(); ?>
<div id="app">
<header class="hdr">
	<div class="wrap hdr-top">
		<?php echo qs_logo(); // phpcs:ignore ?>
		<form class="search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<select name="sec" aria-label="<?php echo esc_attr( qs_t( 'section' ) ); ?>"><option value=""><?php qs_e( 'all_sections' ); ?></option><?php foreach ( qs_nav() as $n ) { echo '<option value="' . esc_attr( $n['slug'] ) . '"' . selected( qs_f( 'sec' ), $n['slug'], false ) . '>' . esc_html( $n['label'] ) . '</option>'; } ?></select>
			<input name="s" type="search" placeholder="<?php echo esc_attr( qs_t( 'search_ph' ) ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" aria-label="<?php echo esc_attr( qs_t( 'search' ) ); ?>">
			<input type="hidden" name="post_type" value="listing">
			<button type="submit" aria-label="<?php echo esc_attr( qs_t( 'search' ) ); ?>"><?php echo qs_ic( 'search' ); // phpcs:ignore ?></button>
		</form>
		<div class="hdr-actions">
			<div class="lang-seg" role="group" aria-label="language"><?php echo qs_lang_btns(); // phpcs:ignore ?></div>
			<?php if ( $uid ) : $me = wp_get_current_user(); ?>
			<div class="pop-wrap"><button class="avatar-btn" data-act="umenu"><span class="avatar"><?php echo esc_html( mb_substr( trim( $me->display_name ), 0, 1 ) ); ?></span><span class="only-d"><?php echo esc_html( explode( ' ', $me->display_name )[0] ); ?></span></button>
				<div class="pop user-pop" id="userPop" hidden>
					<a href="<?php echo esc_url( qs_page_url( 'account', array( 'tab' => 'ads' ) ) ); ?>"><?php echo qs_ic( 'box' ) . ' '; qs_e( 'my_ads' ); ?></a>
					<a href="<?php echo esc_url( qs_page_url( 'account' ) ); ?>"><?php echo qs_ic( 'user' ) . ' '; qs_e( 'my_profile' ); ?></a>
					<a href="<?php echo esc_url( qs_page_url( 'favorites' ) ); ?>"><?php echo qs_ic( 'heart' ) . ' '; qs_e( 'favorites' ); ?></a>
					<?php if ( current_user_can( 'edit_others_posts' ) ) : ?><a href="<?php echo esc_url( admin_url() ); ?>"><?php echo qs_ic( 'grid' ); ?> Dashboard</a><?php endif; ?>
					<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php echo qs_ic( 'logout' ) . ' '; qs_e( 'logout' ); ?></a>
				</div></div>
			<?php else : ?>
			<a href="<?php echo esc_url( qs_page_url( 'login' ) ); ?>" class="btn btn-line login-btn"><?php echo qs_ic( 'user' ); ?><span><?php qs_e( 'login' ); ?></span></a>
			<?php endif; ?>
			<a href="<?php echo esc_url( qs_page_url( 'post' ) ); ?>" class="btn btn-y post-btn"><?php echo qs_ic( 'plus' ); ?><span><?php qs_e( 'post_ad' ); ?></span></a>
		</div>
	</div>
	<?php list( $menu_in, $menu_more ) = qs_menu_split(); ?>
	<nav class="mainnav"><div class="wrap navrow">
		<button class="menu-btn" data-act="drawer" aria-label="<?php echo esc_attr( qs_t( 'menu' ) ); ?>"><?php echo qs_ic( 'menu' ); ?></button>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav-home<?php echo 'home' === $active ? ' on' : ''; ?>"><?php echo qs_ic( 'home' ); ?><span><?php qs_e( 'home' ); ?></span></a>
		<?php
		foreach ( $menu_in as $n ) {
			echo qs_mega( $n, $active === $n['slug'] ); // phpcs:ignore
		}
		if ( $menu_more ) {
			echo qs_mega( array( 'slug' => 'more', 'label' => qs_t( 'more' ), 'url' => qs_ads_url(), 'icon' => 'dots', 'subs' => $menu_more ), in_array( $active, wp_list_pluck( $menu_more, 'slug' ), true ) ); // phpcs:ignore
		}
		?>
	</div></nav>
</header>
<main id="view">
