<?php
/**
 * Template Name: FastSouq — My account
 */
defined( 'ABSPATH' ) || exit;
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( qs_page_url( 'login', array( 'redirect' => qs_page_url( 'account' ) ) ) );
	exit;
}
get_header();
$u   = wp_get_current_user();
$tab = in_array( qs_f( 'tab' ), qs_promos_enabled() ? array( 'profile', 'promote' ) : array( 'profile' ), true ) ? qs_f( 'tab' ) : 'ads';
if ( 'profile' === $tab ) {
	ob_start();
	?>
	<form class="box" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="qs_profile"><?php wp_nonce_field( 'qs_profile', '_qsn' ); ?>
		<h2><?php qs_e( 'my_profile' ); ?></h2>
		<div class="form-grid two">
			<label class="fld"><span><?php qs_e( 'full_name' ); ?></span><input name="name" value="<?php echo esc_attr( $u->display_name ); ?>"></label>
			<label class="fld"><span><?php qs_e( 'phone' ); ?></span><div class="phone-in"><span class="cc">+971</span><input name="phone" class="ltr-in" inputmode="tel" value="<?php echo esc_attr( preg_replace( '/^971/', '', (string) get_user_meta( $u->ID, 'qs_phone', true ) ) ); ?>"></div></label>
			<label class="fld"><span><?php qs_e( 'email' ); ?></span><input type="email" name="email" class="ltr-in" value="<?php echo esc_attr( $u->user_email ); ?>"></label>
		</div>
		<div class="post-foot"><span></span><button class="btn btn-y btn-lg" type="submit"><?php echo qs_ic( 'check' ) . ' '; qs_e( 'save' ); ?></button></div>
	</form>
	<?php
	$body = ob_get_clean();
} elseif ( 'promote' === $tab ) {
	$body = qs_promote_view( absint( qs_f( 'ad' ) ) );
} else {
	$mine = get_posts( array( 'post_type' => 'listing', 'author' => $u->ID, 'post_status' => array( 'publish', 'pending', 'draft', 'expired' ), 'posts_per_page' => 100 ) );
	$body = '<div class="my-ads">';
	foreach ( $mine as $p ) {
		$st   = $p->post_status;
		$cls  = 'publish' === $st ? 'act' : ( 'expired' === $st ? 'exp' : 'pend' );
		$exp  = (int) qs_m( $p->ID, 'expires' );
		$note = 'publish' === $st && $exp ? qs_tf( 'expires_in', array( 'n' => max( 0, (int) ceil( ( $exp - time() ) / DAY_IN_SECONDS ) ) ) ) : qs_t( 'status_' . $st );
		$del  = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-confirm><input type="hidden" name="action" value="qs_delete"><input type="hidden" name="post_id" value="' . (int) $p->ID . '">' . wp_nonce_field( 'qs_delete', '_qsn', true, false ) . '<button class="btn btn-sm btn-line" type="submit">' . qs_ic( 'close' ) . ' ' . esc_html( qs_t( 'delete' ) ) . '</button></form>';
		$body .= '<div class="my-ad' . ( 'exp' === $cls ? ' is-exp' : '' ) . '"><a href="' . esc_url( get_permalink( $p ) ) . '" class="my-ad-art">' . qs_media( $p->ID, 'thumbnail' ) . '</a><div class="my-ad-b"><a href="' . esc_url( get_permalink( $p ) ) . '"><b>' . esc_html( qs_title( $p ) ) . '</b></a><span>' . ( 'service' === qs_group( $p->ID ) ? esc_html( qs_term_name( qs_sub( $p->ID ) ) ) : qs_price( $p->ID ) ) . '</span>' . ( ( $fu = (int) qs_m( $p->ID, 'featured_until' ) ) > time() ? '<span class="b b-y">' . qs_ic( 'star', 'sm' ) . ' ' . esc_html( qs_tf( 'featured_until', array( 'd' => wp_date( 'Y-m-d', $fu ) ) ) ) . '</span>' : '' ) . '
			<small>' . qs_ic( 'eye' ) . ' ' . esc_html( qs_fmt( (int) qs_m( $p->ID, 'views' ) ) . ' · ' . $note ) . '</small></div><span class="status ' . $cls . '">' . esc_html( qs_t( 'status_' . $st ) ) . '</span>
			<div class="my-ad-acts">' . ( qs_promos_enabled() && in_array( $st, array( 'publish', 'expired' ), true ) ? '<a class="btn btn-sm btn-y2" href="' . esc_url( qs_page_url( 'account', array( 'tab' => 'promote', 'ad' => $p->ID ) ) ) . '">' . qs_ic( 'star' ) . ' ' . esc_html( qs_t( 'promote' ) ) . '</a>' : '' ) . '<a class="btn btn-sm btn-line" href="' . esc_url( qs_page_url( 'post', array( 'edit' => $p->ID ) ) ) . '">' . qs_ic( 'edit' ) . ' ' . esc_html( qs_t( 'edit' ) ) . '</a>' . $del . '</div></div>';
	}
	$body .= ( $mine ? '' : qs_empty( 'box', qs_t( 'no_my_ads' ) ) ) . '<div class="row-btns"><a class="btn btn-y" href="' . esc_url( qs_page_url( 'post' ) ) . '">' . qs_ic( 'plus' ) . ' ' . esc_html( qs_t( 'post_ad' ) ) . '</a></div></div>';
}
$tabs = array( 'ads' => array( 'my_ads', 'box' ), 'profile' => array( 'my_profile', 'user' ) );
?>
<section class="page-head slim"><div class="wrap head-flex"><div><h1><?php echo esc_html( qs_t( 'hello' ) . ( 'en' === qs_lang() ? ', ' : '، ' ) . $u->display_name ); ?></h1></div><a class="btn btn-line" href="<?php echo esc_url( get_author_posts_url( $u->ID ) ); ?>"><?php echo qs_ic( 'eye' ) . ' '; qs_e( 'see_profile' ); ?></a></div></section>
<div class="wrap account"><aside class="acc-nav">
	<?php foreach ( $tabs as $k => $x ) { echo '<a href="' . esc_url( qs_page_url( 'account', array( 'tab' => $k ) ) ) . '" class="' . ( $tab === $k ? 'on' : '' ) . '">' . qs_ic( $x[1] ) . ' ' . esc_html( qs_t( $x[0] ) ) . '</a>'; } // phpcs:ignore ?>
	<a href="<?php echo esc_url( qs_page_url( 'favorites' ) ); ?>"><?php echo qs_ic( 'heart' ) . ' '; qs_e( 'favorites' ); ?></a>
	<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php echo qs_ic( 'logout' ) . ' '; qs_e( 'logout' ); ?></a></aside>
	<div class="acc-body"><?php echo qs_notice() . $body; // phpcs:ignore ?></div></div>
<?php
get_footer();
