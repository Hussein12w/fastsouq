<?php
/** Home: photo slider, main categories slider, sponsored banners, latest ads of each main category (5 per row). */
defined( 'ABSPATH' ) || exit;
get_header();
$latest = function ( $term ) {
	return get_posts( array( 'post_type' => 'listing', 'fields' => 'ids', 'no_found_rows' => true, 'posts_per_page' => 10, 'tax_query' => array( array( 'taxonomy' => 'qs_cat', 'terms' => $term->term_id ) ), 'meta_query' => array( 'feat' => array( 'key' => '_qs_featured', 'type' => 'NUMERIC' ), 'bump' => array( 'key' => '_qs_bump', 'type' => 'NUMERIC' ) ), 'orderby' => array( 'feat' => 'DESC', 'bump' => 'DESC' ) ) );
};
$count = function ( $t ) {
	$q = new WP_Query( array( 'post_type' => 'listing', 'fields' => 'ids', 'posts_per_page' => 1, 'tax_query' => array( array( 'taxonomy' => 'qs_cat', 'terms' => $t->term_id ) ) ) );
	return (int) $q->found_posts;
};
$slides = array();
foreach ( array( 1, 2 ) as $i ) {
	if ( $img = get_theme_mod( 'qs_slide' . $i ) ) {
		$slides[] = array( 'img' => $img, 'link' => get_theme_mod( 'qs_slide' . $i . '_link' ) );
	}
}
if ( ! $slides && get_theme_mod( 'qs_hero' ) ) {
	$slides[] = array( 'img' => get_theme_mod( 'qs_hero' ), 'link' => '' );
}
$nav = qs_nav();
/* Up to 10 ads in rows of 5 (on phones each row slides sideways). */
function qs_ad_rows( $ids ) {
	$h = '<div class="ad-rows">';
	foreach ( array_chunk( array_slice( $ids, 0, 10 ), 5 ) as $row ) {
		$h .= '<div class="grid g5 ad-row">' . implode( '', array_map( 'qs_card', $row ) ) . '</div>';
	}
	return $h . '</div>';
}
?>
<section class="hs" data-hs>
	<div class="hs-track">
		<?php if ( $slides ) : ?>
			<?php foreach ( $slides as $i => $sl ) : ?>
			<div class="hs-slide<?php echo $i ? '' : ' on'; ?>"><img src="<?php echo esc_url( $sl['img'] ); ?>" alt="" <?php echo $i ? 'loading="lazy"' : 'fetchpriority="high"'; ?>><?php if ( $sl['link'] ) : ?><a class="hs-link" href="<?php echo esc_url( $sl['link'] ); ?>" aria-label="<?php echo esc_attr( qs_t( 'view_all' ) ); ?>"></a><?php endif; ?></div>
			<?php endforeach; ?>
		<?php else : ?>
			<div class="hs-slide hs-blank on"><?php echo qs_art( 'vehicle' ); // phpcs:ignore ?></div>
		<?php endif; ?>
	</div>
	<div class="hs-shade"></div>
	<div class="wrap hs-txt">
		<h1><?php qs_e( 'hero_t' ); ?></h1>
		<p class="lead"><?php qs_e( 'hero_p' ); ?></p>
		<a class="btn btn-y btn-lg" href="<?php echo esc_url( qs_page_url( 'post' ) ); ?>"><?php echo qs_ic( 'plus' ) . ' '; qs_e( 'post_ad' ); ?></a>
	</div>
	<?php if ( count( $slides ) > 1 ) : ?>
	<button class="hs-arr prev" data-act="hs" data-d="-1" aria-label="prev"><?php echo qs_ic( 'chevl', 'flip' ); ?></button>
	<button class="hs-arr next" data-act="hs" data-d="1" aria-label="next"><?php echo qs_ic( 'chev', 'flip' ); ?></button>
	<div class="hs-dots"><?php foreach ( $slides as $i => $sl ) { echo '<button data-act="hs-to" data-i="' . (int) $i . '" class="' . ( $i ? '' : 'on' ) . '" aria-label="' . (int) ( $i + 1 ) . '"></button>'; } ?></div>
	<?php endif; ?>
</section>

<section class="wrap sec cat-sec"><?php echo qs_sec_head( qs_t( 'browse_cats' ) ); // phpcs:ignore ?>
	<div class="cs" data-cs>
		<button class="cs-arr prev" data-act="cs" data-d="-1" aria-label="prev"><?php echo qs_ic( 'chevl', 'flip' ); ?></button>
		<div class="cs-track">
			<?php foreach ( $nav as $n ) : ?>
			<a class="cs-item" href="<?php echo esc_url( $n['url'] ); ?>"><span class="cs-ic"><?php echo qs_ic( $n['icon'] ); ?></span><b><?php echo esc_html( $n['label'] ); ?></b><small><?php echo esc_html( qs_tf( 'ads_n', array( 'n' => $count( $n['term'] ) ) ) ); ?></small></a>
			<?php endforeach; ?>
		</div>
		<button class="cs-arr next" data-act="cs" data-d="1" aria-label="next"><?php echo qs_ic( 'chev', 'flip' ); ?></button>
	</div>
</section>

<?php if ( $b = qs_sponsored( 'home_top' ) ) : ?><section class="wrap sec-spons"><?php echo $b; // phpcs:ignore ?></section><?php endif; ?>

<?php
$shown = 0;
foreach ( qs_menu_split()[0] as $n ) {
	$list = $latest( $n['term'] );
	if ( ! $list ) {
		continue;
	}
	echo '<section class="wrap sec">' . qs_sec_head( $n['label'], $n['url'], $n['icon'] ) . qs_ad_rows( $list ) . '</section>'; // phpcs:ignore
	if ( 1 === ++$shown ) {
		$mid = qs_sponsored( 'home_mid' );
		echo $mid ? '<section class="wrap sec-spons">' . $mid . '</section>' : '<section class="wrap"><div class="promo qs-promo"><div class="promo-txt"><h2>' . esc_html( qs_t( 'promo_t' ) ) . '</h2><p>' . esc_html( qs_t( 'promo_p' ) ) . '</p><a class="btn btn-y2 btn-lg" href="' . esc_url( qs_page_url( 'post' ) ) . '">' . qs_ic( 'plus' ) . ' ' . esc_html( qs_t( 'post_ad' ) ) . '</a></div><img src="' . esc_url( QS_URI . '/assets/img/logo-icon.png' ) . '" alt="" class="promo-logo"></div></section>'; // phpcs:ignore
	}
}
/* Other main categories with ads. */
foreach ( qs_menu_split()[1] as $n ) {
	if ( $list = $latest( $n['term'] ) ) {
		echo '<section class="wrap sec">' . qs_sec_head( $n['label'], $n['url'], $n['icon'] ) . qs_ad_rows( $list ) . '</section>'; // phpcs:ignore
	}
}
get_footer();
