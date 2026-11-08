<?php
/** Advertiser page: name, contact and all their ads. */
defined( 'ABSPATH' ) || exit;
get_header();
$u     = get_queried_object();
$phone = get_user_meta( $u->ID, 'qs_phone', true );
$name  = $u->display_name;
?>
<section class="page-head"><div class="wrap head-flex"><div><?php echo qs_crumbs( array( array( $name ) ) ); // phpcs:ignore ?>
	<div class="adv-head"><div class="co-logo xl"><?php echo esc_html( mb_substr( $name, 0, 1 ) ); ?></div><div><h1><?php echo esc_html( $name ); ?></h1><p class="muted"><?php echo esc_html( qs_t( 'member_since' ) . ' ' . gmdate( 'Y', strtotime( $u->user_registered ) ) ); ?></p></div></div></div>
	<?php if ( $phone ) : ?><div class="row-btns"><a class="btn btn-line" href="tel:+<?php echo esc_attr( $phone ); ?>"><?php echo qs_ic( 'phone' ); ?> <span class="ltr"><?php echo esc_html( qs_phone_fmt( $phone ) ); ?></span></a><a class="btn btn-wa" href="<?php echo esc_url( qs_wa_link( $phone ) ); ?>" target="_blank" rel="noopener"><?php echo qs_ic( 'wa' ) . ' '; qs_e( 'whatsapp' ); ?></a></div><?php endif; ?></div></section>
<div class="wrap sec"><?php echo qs_sec_head( qs_t( 'his_ads' ) ); // phpcs:ignore ?>
<?php
if ( have_posts() ) {
	echo '<div class="grid g3">';
	while ( have_posts() ) {
		the_post();
		echo qs_card( get_the_ID() ); // phpcs:ignore
	}
	echo '</div>' . qs_pagination(); // phpcs:ignore
} else {
	echo qs_empty( 'box' ); // phpcs:ignore
}
?>
</div>
<?php
get_footer();
