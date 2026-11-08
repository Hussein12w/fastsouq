<?php
/** Results page with the side filter (fields from the spreadsheet, per section). */
defined( 'ABSPATH' ) || exit;

/**
 * $o: title, icon, crumbs, chips (html), section (top term or null), sub (sub-section term or null), keep (hidden GET params)
 */
function qs_browse( $o ) {
	global $wp_query;
	$o       = array_merge( array( 'keep' => array(), 'icon' => '', 'chips' => '', 'section' => null, 'sub' => null ), $o );
	$sec     = $o['section'];
	$group   = $sec ? qs_group_of( $sec->slug ) : '';
	$found   = (int) $wp_query->found_posts;
	$base    = strtok( $_SERVER['REQUEST_URI'] ?? '', '?' );
	$f       = 'qs_f';
	$opt     = function ( $name, $list, $placeholder ) use ( $f ) {
		$h = '<select name="' . esc_attr( $name ) . '"><option value="">' . esc_html( $placeholder ) . '</option>';
		foreach ( qs_options( $list ) as $k => $v ) {
			$h .= '<option value="' . esc_attr( $k ) . '"' . selected( $f( $name ), (string) $k, false ) . '>' . esc_html( qs_pair( $v ) ) . '</option>';
		}
		return $h . '</select>';
	};
	$chips   = function ( $name, $list ) use ( $f ) {
		$h = '<div class="chips"><label class="chip' . ( '' === $f( $name ) ? ' on' : '' ) . '"><input type="radio" name="' . esc_attr( $name ) . '" value=""' . checked( $f( $name ), '', false ) . ' hidden>' . esc_html( qs_t( 'any' ) ) . '</label>';
		foreach ( qs_options( $list ) as $k => $v ) {
			$h .= '<label class="chip' . ( $f( $name ) === (string) $k ? ' on' : '' ) . '"><input type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $k ) . '"' . checked( $f( $name ), (string) $k, false ) . ' hidden>' . esc_html( qs_pair( $v ) ) . '</label>';
		}
		return $h . '</div>';
	};
	$range   = function ( $a, $b, $pa, $pb, $years = false ) use ( $f ) {
		if ( $years ) {
			$ys = range( (int) gmdate( 'Y' ) + 1, 1960 );
			$s  = fn( $n, $p ) => '<select name="' . $n . '"><option value="">' . esc_html( $p ) . '</option>' . implode( '', array_map( fn( $y ) => '<option' . selected( $f( $n ), (string) $y, false ) . '>' . $y . '</option>', $ys ) ) . '</select>';
			return '<div class="two-in">' . $s( $a, $pa ) . $s( $b, $pb ) . '</div>';
		}
		return '<div class="two-in"><input type="number" min="0" inputmode="numeric" name="' . $a . '" value="' . esc_attr( $f( $a ) ) . '" placeholder="' . esc_attr( $pa ) . '"><input type="number" min="0" inputmode="numeric" name="' . $b . '" value="' . esc_attr( $f( $b ) ) . '" placeholder="' . esc_attr( $pb ) . '"></div>';
	};
	$grp     = fn( $label, $html ) => '<div class="flt-g"><label>' . esc_html( qs_t( $label ) ) . '</label>' . $html . '</div>';
	$makes   = function ( $label ) use ( $f ) {
		$h = '<select name="make"><option value="">' . esc_html( qs_t( 'all_makes' ) ) . '</option>';
		$m = '<select name="model" data-dep="make"><option value="">' . esc_html( qs_t( 'all' ) ) . '</option>';
		foreach ( qs_children( 0, 'qs_make' ) as $mk ) {
			$h .= '<option value="' . esc_attr( $mk->slug ) . '"' . selected( $f( 'make' ), $mk->slug, false ) . '>' . esc_html( qs_term_name( $mk ) ) . '</option>';
			foreach ( qs_children( $mk, 'qs_make' ) as $md ) {
				$m .= '<option value="' . esc_attr( $md->slug ) . '" data-p="' . esc_attr( $mk->slug ) . '"' . selected( $f( 'model' ), $md->slug, false ) . '>' . esc_html( qs_term_name( $md ) ) . '</option>';
			}
		}
		return '<div class="flt-g"><label>' . esc_html( qs_t( $label ) ) . '</label>' . $h . '</select></div><div class="flt-g"><label>' . esc_html( qs_t( 'make' === $label ? 'model' : 'compat_model' ) ) . '</label>' . $m . '</select></div>';
	};
	$cities  = function () use ( $f ) {
		$e = '<select name="emirate"><option value="">' . esc_html( qs_t( 'all' ) ) . '</option>';
		$c = '<select name="city" data-dep="emirate"><option value="">' . esc_html( qs_t( 'all' ) ) . '</option>';
		foreach ( qs_children( 0, 'qs_city' ) as $em ) {
			$e .= '<option value="' . esc_attr( $em->slug ) . '"' . selected( $f( 'emirate' ), $em->slug, false ) . '>' . esc_html( qs_term_name( $em ) ) . '</option>';
			foreach ( qs_children( $em, 'qs_city' ) as $ci ) {
				$c .= '<option value="' . esc_attr( $ci->slug ) . '" data-p="' . esc_attr( $em->slug ) . '"' . selected( $f( 'city' ), $ci->slug, false ) . '>' . esc_html( qs_term_name( $ci ) ) . '</option>';
			}
		}
		return '<div class="flt-g"><label>' . esc_html( qs_t( 'emirate' ) ) . '</label>' . $e . '</select></div><div class="flt-g"><label>' . esc_html( qs_t( 'city' ) ) . '</label>' . $c . '</select></div>';
	};

	echo '<section class="page-head"><div class="wrap head-flex"><div>' . qs_crumbs( $o['crumbs'] ) . '<h1>' . ( $o['icon'] ? '<span class="h-ic">' . qs_ic( $o['icon'] ) . '</span>' : '' ) . esc_html( $o['title'] ) . ' <small class="muted cnt">' . esc_html( $found . ' ' . qs_t( 'results' ) ) . '</small></h1></div><a class="btn btn-y" href="' . esc_url( qs_page_url( 'post', $sec ? array( 'section' => $sec->slug ) : array() ) ) . '">' . qs_ic( 'plus' ) . ' ' . esc_html( qs_t( 'post_ad' ) ) . '</a></div>' . ( $o['chips'] ? '<div class="wrap">' . $o['chips'] . '</div>' : '' ) . '</section>'; // phpcs:ignore
	?>
	<div class="wrap browse">
	<form class="filters" id="filters" method="get" action="<?php echo esc_url( $base ); ?>" data-autosubmit>
		<?php foreach ( $o['keep'] as $k => $v ) { echo '<input type="hidden" name="' . esc_attr( $k ) . '" value="' . esc_attr( $v ) . '">'; } ?>
		<div class="flt-h"><b><?php echo qs_ic( 'filter' ) . ' '; qs_e( 'filters' ); ?></b><a class="link" href="<?php echo esc_url( add_query_arg( $o['keep'], $base ) ); ?>"><?php qs_e( 'reset' ); ?></a></div>
		<?php
		$h = '';
		if ( $sec && ! $o['sub'] ) {
			$h .= '<div class="flt-g"><label>' . esc_html( qs_t( 'subsection' ) ) . '</label><select name="cat"><option value="">' . esc_html( qs_t( 'all' ) ) . '</option>';
			foreach ( qs_children( $sec ) as $c ) {
				$h .= '<option value="' . esc_attr( $c->slug ) . '"' . selected( qs_f( 'cat' ), $c->slug, false ) . '>' . esc_html( qs_term_name( $c ) ) . '</option>';
			}
			$h .= '</select></div>';
		}
		if ( 'vehicle' === $group ) {
			$h .= $makes( 'make' ) . $grp( 'year', $range( 'ymin', 'ymax', qs_t( 'year_from' ), qs_t( 'year_to' ), true ) ) . $grp( 'price', $range( 'pmin', 'pmax', qs_t( 'price_min' ), qs_t( 'price_max' ) ) );
			$bodies = qs_body_options()[ $o['sub'] ? $o['sub']->slug : 'cars-for-sale' ] ?? array();
			if ( $bodies ) {
				$b = '<select name="body"><option value="">' . esc_html( qs_t( 'all' ) ) . '</option>';
				foreach ( $bodies as $x ) {
					$p  = explode( '|', $x );
					$b .= '<option value="' . esc_attr( $p[0] ) . '"' . selected( qs_f( 'body' ), $p[0], false ) . '>' . esc_html( 'en' === qs_lang() ? $p[2] : $p[1] ) . '</option>';
				}
				$h .= $grp( 'body', $b . '</select>' );
			}
			$h .= $grp( 'condition', $opt( 'cond', 'cond', qs_t( 'any' ) ) ) . $grp( 'kms', $range( 'kmin', 'kmax', qs_t( 'km_from' ), qs_t( 'km_to' ) ) ) . $grp( 'gear', $chips( 'gear', 'gear' ) ) . $grp( 'fuel', $opt( 'fuel', 'fuel', qs_t( 'any' ) ) ) . $grp( 'cyl', $opt( 'cyl', 'cyl', qs_t( 'any' ) ) ) . $grp( 'specs', $opt( 'specs', 'specs', qs_t( 'any' ) ) ) . $grp( 'color', $opt( 'color', 'color', qs_t( 'any' ) ) );
		} elseif ( 'item' === $group ) {
			$sub_slug = $o['sub'] ? $o['sub']->slug : qs_f( 'cat' );
			if ( $sub_slug && ( $st = qs_term( $sub_slug ) ) && qs_children( $st ) ) {
				$it = '<select name="itype"><option value="">' . esc_html( qs_t( 'all' ) ) . '</option>';
				foreach ( qs_children( $st ) as $c ) {
					$it .= '<option value="' . esc_attr( $c->slug ) . '"' . selected( qs_f( 'itype' ), $c->slug, false ) . '>' . esc_html( qs_term_name( $c ) ) . '</option>';
				}
				$h .= $grp( 'item_type', $it . '</select>' );
			}
			$h .= $grp( 'part_brand', '<input name="brand" value="' . esc_attr( qs_f( 'brand' ) ) . '" placeholder="Bosch, Denso…">' ) . $makes( 'compat_make' ) . $grp( 'compat_year', $range( 'ymin', 'ymax', qs_t( 'year_from' ), qs_t( 'year_to' ), true ) ) . $grp( 'condition', $chips( 'cond', 'item_cond' ) ) . $grp( 'part_type', $chips( 'part_type', 'part_type' ) ) . $grp( 'price', $range( 'pmin', 'pmax', qs_t( 'price_min' ), qs_t( 'price_max' ) ) );
		} elseif ( ! $group ) {
			$h .= $grp( 'price', $range( 'pmin', 'pmax', qs_t( 'price_min' ), qs_t( 'price_max' ) ) );
		}
		echo $h . $cities(); // phpcs:ignore
		?>
		<button class="btn btn-y full" type="submit"><?php qs_e( 'apply' ); ?></button>
	</form>
	<div class="results"><div class="res-bar"><button class="btn btn-line only-m" type="button" data-act="toggle-filters"><?php echo qs_ic( 'filter' ) . ' '; qs_e( 'show_filters' ); ?></button><span class="muted"><?php echo esc_html( $found . ' ' . qs_t( 'results' ) ); ?></span>
		<label class="sort"><?php qs_e( 'sort' ); ?><select name="sort" form="filters"><option value=""><?php qs_e( 'newest' ); ?></option><option value="asc"<?php selected( qs_f( 'sort' ), 'asc' ); ?>><?php qs_e( 'price_low' ); ?></option><option value="desc"<?php selected( qs_f( 'sort' ), 'desc' ); ?>><?php qs_e( 'price_high' ); ?></option></select></label></div>
		<?php
		if ( have_posts() ) {
			echo '<div class="grid g3">';
			while ( have_posts() ) {
				the_post();
				echo qs_card( get_the_ID() ); // phpcs:ignore
			}
			echo '</div>' . qs_pagination(); // phpcs:ignore
		} else {
			echo qs_empty( 'search', '', '<a class="btn btn-line" href="' . esc_url( add_query_arg( $o['keep'], $base ) ) . '">' . esc_html( qs_t( 'reset' ) ) . '</a>' ); // phpcs:ignore
		}
		?>
	</div></div>
	<?php
}

/** Chips of the children of a term (sub-sections or item types). */
function qs_term_chips( $parent, $current = null ) {
	$kids = qs_children( $parent );
	if ( ! $kids ) {
		return '';
	}
	$h = '<div class="chips scroll sub-chips"><a class="chip' . ( ! $current ? ' on' : '' ) . '" href="' . esc_url( get_term_link( $parent ) ) . '">' . esc_html( qs_t( 'all' ) ) . '</a>';
	foreach ( $kids as $c ) {
		$h .= '<a class="chip' . ( $current && $current->term_id === $c->term_id ? ' on' : '' ) . '" href="' . esc_url( get_term_link( $c ) ) . '">' . esc_html( qs_term_name( $c ) ) . '</a>';
	}
	return $h . '</div>';
}
function qs_section_chips() {
	$h = '<div class="chips scroll sub-chips">';
	foreach ( qs_nav() as $n ) {
		$h .= '<a class="chip" href="' . esc_url( $n['url'] ) . '">' . qs_ic( $n['icon'], 'sm' ) . ' ' . esc_html( $n['label'] ) . '</a>';
	}
	return $h . '</div>';
}
