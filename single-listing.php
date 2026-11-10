<?php
/** Ad page — car, accessory or service. */
defined( 'ABSPATH' ) || exit;
get_header();
the_post();
$id     = get_the_ID();
$group  = qs_group( $id );
$lv     = qs_levels( $id );
list( $make, $model ) = qs_make_model( $id );
$phone  = qs_phone_of( $id );
$author = get_userdata( get_post_field( 'post_author', $id ) );
$own    = get_current_user_id() && $author && $author->ID === get_current_user_id();
$lat    = qs_m( $id, 'lat' );
$lng    = qs_m( $id, 'lng' );
$addr   = trim( qs_city_label( $id ) . ( qs_m( $id, 'area' ) ? ' — ' . qs_m( $id, 'area' ) : '' ) );
$photos = array_values( array_unique( array_filter( array_merge( array( get_post_thumbnail_id( $id ) ), (array) qs_m( $id, 'gallery' ) ) ) ) );

/* Details table — the fields of this ad's group. */
$rows = array();
$add  = function ( $label, $val ) use ( &$rows ) {
	if ( '' !== trim( (string) $val ) ) {
		$rows[] = array( $label, $val );
	}
};
$add( qs_t( 'subsection' ), qs_term_name( $lv[1] ) );
if ( 'vehicle' === $group ) {
	$add( qs_t( 'make' ), $make ? qs_term_name( $make ) : '' );
	$add( qs_t( 'model' ), qs_model_label( $id, $model ) );
	$add( qs_t( 'year' ), qs_m( $id, 'year' ) );
	$body = qs_m( $id, 'body' );
	if ( $body ) {
		foreach ( qs_body_options()[ $lv[1] ? $lv[1]->slug : '' ] ?? array() as $o ) {
			$p = explode( '|', $o );
			if ( $p[0] === $body ) {
				$add( qs_t( 'body' ), 'en' === qs_lang() ? $p[2] : $p[1] );
			}
		}
	}
	$add( qs_t( 'condition' ), qs_m( $id, 'cond' ) ? qs_opt( 'cond', qs_m( $id, 'cond' ) ) : '' );
	$add( qs_t( 'kms' ), '' !== qs_m( $id, 'km' ) ? qs_fmt( qs_m( $id, 'km' ) ) . ' ' . qs_t( 'km' ) : '' );
	foreach ( array( 'gear', 'fuel', 'cyl', 'specs', 'color' ) as $k ) {
		$add( qs_t( $k ), '' !== qs_m( $id, $k ) ? qs_opt( $k, qs_m( $id, $k ) ) : '' );
	}
	$add( qs_t( 'deal' ), qs_m( $id, 'deal' ) ? qs_opt( 'deal', qs_m( $id, 'deal' ) ) : '' );
	$add( qs_t( 'period' ), qs_m( $id, 'period' ) ? qs_opt( 'period', qs_m( $id, 'period' ) ) : '' );
} elseif ( 'general' === $group ) {
	$add( qs_t( 'cat_detail' ), qs_term_name( $lv[2] ) );
	$add( qs_t( 'condition' ), qs_m( $id, 'cond' ) ? qs_opt( 'item_cond', qs_m( $id, 'cond' ) ) : '' );
} elseif ( 'item' === $group ) {
	$add( qs_t( 'item_type' ), qs_term_name( $lv[2] ) );
	$add( qs_t( 'part_brand' ), qs_m( $id, 'brand' ) );
	$add( qs_t( 'compat_make' ), $make ? qs_term_name( $make ) : '' );
	$add( qs_t( 'compat_model' ), qs_model_label( $id, $model ) );
	$add( qs_t( 'compat_year' ), qs_m( $id, 'year' ) );
	$add( qs_t( 'condition' ), qs_m( $id, 'cond' ) ? qs_opt( 'item_cond', qs_m( $id, 'cond' ) ) : '' );
	$add( qs_t( 'part_type' ), qs_m( $id, 'part_type' ) ? qs_opt( 'part_type', qs_m( $id, 'part_type' ) ) : '' );
	$add( qs_t( 'sku' ), qs_m( $id, 'sku' ) );
}
$add( qs_t( 'location' ), $addr );
$add( qs_t( 'ad_no' ), 'QS-' . ( 1000 + $id ) );
$similar = get_posts( array( 'post_type' => 'listing', 'fields' => 'ids', 'posts_per_page' => 5, 'post__not_in' => array( $id ), 'tax_query' => array( array( 'taxonomy' => 'qs_cat', 'terms' => ( $lv[2] ?: $lv[1] ?: $lv[0] )->term_id ?? 0 ) ) ) );
$crumbs  = array();
if ( $lv[0] && isset( qs_sections()[ $lv[0]->slug ] ) && ( $cars = qs_term( 'cars' ) ) ) {
	$crumbs[] = array( qs_term_name( $cars ), get_term_link( $cars ) );
}
foreach ( array_filter( $lv ) as $t ) {
	$crumbs[] = array( qs_term_name( $t ), get_term_link( $t ) );
}
$crumbs[] = array( qs_title( $id ) );

/* Report form (opens in a modal). */
$report = '<h2>' . esc_html( qs_t( 'report' ) ) . '</h2><form class="m-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="qs_report"><input type="hidden" name="post_id" value="' . (int) $id . '">' . wp_nonce_field( 'qs_report', '_qsn', true, false );
foreach ( explode( '|', qs_t( 'reasons' ) ) as $i => $r ) {
	$report .= '<label class="check"><input type="radio" name="reason" value="' . esc_attr( $r ) . '"' . ( $i ? '' : ' checked' ) . '> ' . esc_html( $r ) . '</label>';
}
echo qs_modal_tpl( 'report', $report . '<button class="btn btn-dark btn-lg full" type="submit">' . esc_html( qs_t( 'report' ) ) . '</button></form>', 'sm' ); // phpcs:ignore
?>
<div class="wrap detail-wrap"><?php echo qs_notice(); // phpcs:ignore ?>
	<?php if ( 'publish' !== get_post_status( $id ) ) : ?><div class="warn-bar"><?php echo qs_ic( 'eye' ) . ' '; qs_e( 'status_' . get_post_status( $id ) ); ?></div><?php endif; ?>
	<?php echo qs_crumbs( $crumbs ); // phpcs:ignore ?>
	<div class="detail">
		<div class="d-main">
			<div class="gal"><div class="gal-main" id="galMain"><?php echo qs_art( $group ) . qs_photo( $id, 'qs-large' ); // phpcs:ignore ?>
				<div class="badges"><?php echo qs_m( $id, 'featured' ) ? '<span class="b b-y">' . esc_html( qs_t( 'featured' ) ) . '</span>' : ''; ?><?php if ( 'wanted' === qs_m( $id, 'deal' ) ) : ?><span class="b b-k"><?php qs_e( 'wanted' ); ?></span><?php endif; ?></div></div>
				<?php if ( count( $photos ) > 1 ) : ?><div class="gal-thumbs"><?php foreach ( $photos as $i => $pid ) { echo '<button class="th' . ( $i ? '' : ' on' ) . '" data-img="' . esc_url( wp_get_attachment_image_url( $pid, 'qs-large' ) ) . '"><img src="' . esc_url( wp_get_attachment_image_url( $pid, 'thumbnail' ) ) . '" alt=""></button>'; } ?></div><?php endif; ?>
			</div>
			<div class="box"><h2><?php qs_e( 'details' ); ?></h2><dl class="specs"><?php foreach ( $rows as $r ) { echo '<div><dt>' . esc_html( $r[0] ) . '</dt><dd>' . esc_html( $r[1] ) . '</dd></div>'; } ?></dl></div>
			
			<?php if ( trim( qs_desc( $id ) ) ) : ?><div class="box"><h2><?php qs_e( 'description' ); ?></h2><div class="desc"><?php echo wp_kses_post( wpautop( qs_desc( $id ) ) ); ?></div></div><?php endif; ?>
			<?php if ( $lat && $lng ) : ?>
			<div class="box"><h2><?php qs_e( 'location' ); ?></h2>
				<p><?php echo qs_ic( 'pin' ); ?> <b><?php echo esc_html( $addr ); ?></b></p>
				<?php if ( $lat && $lng ) : ?><?php echo qs_map( $lat, $lng ); // phpcs:ignore ?><a class="btn btn-line" href="<?php echo esc_url( 'https://www.google.com/maps?q=' . rawurlencode( $lat . ',' . $lng ) ); ?>" target="_blank" rel="noopener"><?php echo qs_ic( 'pin' ) . ' '; qs_e( 'open_maps' ); ?></a><?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		<aside class="d-side">
			<div class="box buy-box"><h1><?php echo esc_html( qs_title( $id ) ); ?></h1>
				<p class="muted"><?php echo qs_ic( 'eye' ) . ' ' . esc_html( qs_fmt( (int) qs_m( $id, 'views' ) ) . ' ' . qs_t( 'views' ) . ' · ' . qs_ago( $id ) ); // phpcs:ignore ?></p>
				<?php if ( 'service' !== $group ) : ?><div class="big-price"><?php echo qs_price( $id ); // phpcs:ignore ?></div><?php endif; ?>
				<?php if ( 'vehicle' === $group ) : ?><div class="facts"><?php
				if ( $y = qs_m( $id, 'year' ) ) { echo '<span>' . qs_ic( 'cal' ) . ' ' . esc_html( $y ) . '</span>'; } // phpcs:ignore
				if ( '' !== qs_m( $id, 'km' ) ) { echo '<span>' . qs_ic( 'gauge' ) . ' ' . esc_html( qs_fmt( qs_m( $id, 'km' ) ) . ' ' . qs_t( 'km' ) ) . '</span>'; } // phpcs:ignore
				if ( $g = qs_m( $id, 'gear' ) ) { echo '<span>' . qs_ic( 'gear' ) . ' ' . esc_html( qs_opt( 'gear', $g ) ) . '</span>'; } // phpcs:ignore
				?></div><?php endif; ?>
				<?php if ( $own ) : ?>
				<?php if ( qs_promos_enabled() && 'publish' === get_post_status( $id ) ) : ?><a class="btn btn-y2 btn-lg full" href="<?php echo esc_url( qs_page_url( 'account', array( 'tab' => 'promote', 'ad' => $id ) ) ); ?>"><?php echo qs_ic( 'star' ) . ' '; qs_e( 'promote' ); ?></a><?php endif; ?>
				<a class="btn btn-line btn-lg full" href="<?php echo esc_url( qs_page_url( 'post', array( 'edit' => $id ) ) ); ?>"><?php echo qs_ic( 'edit' ) . ' '; qs_e( 'edit' ); ?></a>
				<?php elseif ( $phone ) : ?>
				<a class="btn btn-y btn-lg full" href="tel:+<?php echo esc_attr( $phone ); ?>" data-act="reveal" data-phone="<?php echo esc_attr( qs_phone_fmt( $phone ) ); ?>"><?php echo qs_ic( 'phone' ); ?> <span><?php qs_e( 'show_phone' ); ?></span></a>
				<a class="btn btn-wa btn-lg full" href="<?php echo esc_url( qs_wa_link( $phone, qs_title( $id ) . ' — ' . get_permalink( $id ) ) ); ?>" target="_blank" rel="noopener"><?php echo qs_ic( 'wa' ) . ' '; qs_e( 'whatsapp' ); ?></a>
				<?php endif; ?>
				<div class="row-acts"><?php echo qs_fav_btn( $id, true ); // phpcs:ignore ?><button class="act" data-act="share"><?php echo qs_ic( 'share' ) . ' '; qs_e( 'share' ); ?></button><button class="act" data-modal="report"><?php echo qs_ic( 'flag' ) . ' '; qs_e( 'report' ); ?></button></div>
			</div>
			<?php if ( $author ) : ?>
			<div class="box seller"><h3><?php qs_e( 'advertiser' ); ?></h3><a class="co-row" href="<?php echo esc_url( get_author_posts_url( $author->ID ) ); ?>"><div class="co-logo"><?php echo esc_html( mb_substr( $author->display_name, 0, 1 ) ); ?></div><div><b><?php echo esc_html( $author->display_name ); ?></b><small><?php echo esc_html( qs_t( 'member_since' ) . ' ' . gmdate( 'Y', strtotime( $author->user_registered ) ) ); ?></small></div></a>
				<a class="btn btn-line full" href="<?php echo esc_url( get_author_posts_url( $author->ID ) ); ?>"><?php qs_e( 'his_ads' ); ?></a></div>
			<?php endif; ?>
			<?php echo qs_sponsored( 'ad_side', $lv[0] ); // phpcs:ignore ?>
			<div class="box tips"><h3><?php echo qs_ic( 'shield' ) . ' '; qs_e( 'safety' ); ?></h3><ul><li><?php qs_e( 'safety1' ); ?></li><li><?php qs_e( 'safety2' ); ?></li><li><?php qs_e( 'safety3' ); ?></li></ul></div>
		</aside>
	</div>
	<?php if ( $similar ) : ?><section class="sec"><?php echo qs_sec_head( qs_t( 'similar' ), $lv[1] ? get_term_link( $lv[1] ) : '' ); // phpcs:ignore ?><div class="grid g5"><?php foreach ( $similar as $s ) { echo qs_card( $s ); } // phpcs:ignore ?></div></section><?php endif; ?>
</div>
<?php
get_footer();
