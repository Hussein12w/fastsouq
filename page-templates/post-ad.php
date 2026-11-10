<?php
/**
 * Template Name: FastSouq — Post an ad
 */
defined( 'ABSPATH' ) || exit;
get_header();
$uid  = get_current_user_id();
$head = function ( $title ) {
	echo '<section class="page-head slim"><div class="wrap">' . qs_crumbs( array( array( qs_t( 'post_ad' ) ) ) ) . '<h1>' . esc_html( $title ) . '</h1><p>' . esc_html( qs_t( 'post_sub' ) ) . '</p></div></section>'; // phpcs:ignore
};
if ( ! $uid ) {
	$head( qs_t( 'post_title' ) );
	echo '<div class="wrap post"><div class="post-card gate">' . qs_ic( 'shield' ) . '<h2>' . esc_html( qs_t( 'login_first_t' ) ) . '</h2><p>' . esc_html( qs_t( 'login_first_p' ) ) . '</p><div class="row-btns center"><a class="btn btn-y btn-lg" href="' . esc_url( qs_page_url( 'login', array( 'redirect' => qs_page_url( 'post' ) ) ) ) . '">' . qs_ic( 'user' ) . ' ' . esc_html( qs_t( 'login' ) ) . '</a><a class="btn btn-line btn-lg" href="' . esc_url( qs_page_url( 'login', array( 'tab' => 'register', 'redirect' => qs_page_url( 'post' ) ) ) ) . '">' . esc_html( qs_t( 'reg_tab' ) ) . '</a></div></div></div>'; // phpcs:ignore
	get_footer();
	return;
}
if ( $done = absint( qs_f( 'done' ) ) ) {
	$head( qs_t( 'post_title' ) );
	echo '<div class="wrap post"><div class="post-card done"><div class="done-ic">' . qs_ic( 'eye' ) . '</div><h2>' . esc_html( qs_t( 'post_ok_t' ) ) . '</h2><p>' . esc_html( qs_t( qs_f( 'edited' ) ? 'edit_ok_p' : 'post_ok_p' ) ) . '</p><p class="ref">' . esc_html( qs_t( 'ad_no' ) ) . ': <b>QS-' . (int) ( 1000 + $done ) . '</b></p><div class="done-acts"><a class="btn btn-y" href="' . esc_url( qs_page_url( 'post' ) ) . '">' . qs_ic( 'plus' ) . ' ' . esc_html( qs_t( 'post_another' ) ) . '</a><a class="btn btn-line" href="' . esc_url( qs_page_url( 'account', array( 'tab' => 'ads' ) ) ) . '">' . esc_html( qs_t( 'my_ads' ) ) . '</a></div></div></div>'; // phpcs:ignore
	get_footer();
	return;
}

$edit = absint( qs_f( 'edit' ) );
if ( $edit && ( 'listing' !== get_post_type( $edit ) || (int) get_post_field( 'post_author', $edit ) !== $uid ) ) {
	$edit = 0;
}
$val     = qs_post_values( $edit );
$section = $val['section'] ?? ( qs_is_section( qs_f( 'section' ) ) ? qs_f( 'section' ) : '' );
$main    = $val['main'] ?? ( isset( qs_sections()[ $section ] ) || 'cars' === qs_f( 'main' ) ? 'cars' : $section );
$secs    = array_merge( array_keys( qs_sections() ), array_filter( wp_list_pluck( qs_nav(), 'slug' ), fn( $s ) => 'cars' !== $s ) );
$v       = fn( $k ) => $val[ $k ] ?? '';
$req     = '<em class="req">' . esc_html( qs_t( 'required' ) ) . '</em>';
$head( $edit ? qs_t( 'edit' ) . ' — ' . qs_title( $edit ) : qs_t( 'post_title' ) );

/* Models for the make → model suggestions. */
$models = array();
foreach ( qs_children( 0, 'qs_make' ) as $mk ) {
	$models[ $mk->slug ] = array_map( 'qs_term_name', qs_children( $mk, 'qs_make' ) );
}

/* One field for one group. */
$field = function ( $k, $f, $g ) use ( $v, $req, $secs ) {
	$is_req = in_array( $g, explode( ' ', $f[3] ), true );
	$label  = '<span>' . esc_html( qs_t( qs_field_label( $k, $g ) ) ) . ( $is_req ? ' <i>*</i>' : '' ) . '</span>';
	$attr   = ' data-g="' . esc_attr( $g ) . '"' . ( $is_req ? ' data-required' : '' );
	$full   = in_array( $k, array( 'title', 'desc' ), true ) ? ' full' : '';
	$cur    = (string) $v( $k );
	list( $kind, $src ) = array_pad( explode( ':', $f[1] ), 2, '' );
	if ( 'period' === $k ) {
		$attr .= ' data-subs="cars-for-rent"';
	}
	if ( 'chips' === $kind ) {
		$h = '<div class="fld' . $full . '"' . $attr . '>' . $label . '<div class="chips">';
		foreach ( qs_field_options( $src, $g ) as $o ) {
			$on = '' !== $cur && $cur === (string) $o[0];
			if ( 'deal' === $k && '' === $cur ) {
				$on = 'sale' === $o[0];
			}
			$h .= '<label class="chip' . ( $on ? ' on' : '' ) . '"><input type="radio" name="' . esc_attr( $k ) . '" value="' . esc_attr( $o[0] ) . '"' . checked( $on, true, false ) . ' hidden>' . esc_html( $o[1] ) . '</label>';
		}
		return $h . '</div>' . $req . '</div>';
	}
	$h = '<label class="fld' . $full . '"' . $attr . '>' . $label;
	if ( 'make' === $kind ) {
		$h .= '<select name="make"><option value="">' . esc_html( qs_t( 'pick' ) ) . '</option>';
		foreach ( qs_children( 0, 'qs_make' ) as $mk ) {
			$h .= '<option value="' . esc_attr( $mk->slug ) . '"' . selected( $cur, $mk->slug, false ) . '>' . esc_html( qs_term_name( $mk ) ) . '</option>';
		}
		$h .= '</select>';
	} elseif ( 'model' === $kind ) {
		$shown = ( 'ar' === qs_lang() && qs_make_ar( $cur ) ) ? qs_make_ar( $cur ) : $cur;
		$h    .= '<input name="model" list="qs-models" autocomplete="off" value="' . esc_attr( $shown ) . '">';
	} elseif ( 'body' === $kind ) {
		$h  = '<label class="fld"' . $attr . ' data-hide-empty>' . $label . '<select name="body" data-dep="sub"><option value="">' . esc_html( qs_t( 'pick' ) ) . '</option>';
		foreach ( qs_body_options() as $sub => $list ) {
			foreach ( $list as $o ) {
				$p  = explode( '|', $o );
				$h .= '<option value="' . esc_attr( $p[0] ) . '" data-p="' . esc_attr( $sub ) . '"' . selected( $cur, $p[0], false ) . '>' . esc_html( 'en' === qs_lang() ? $p[2] : $p[1] ) . '</option>';
			}
		}
		$h .= '</select>';
	} elseif ( 'itype' === $kind ) {
		$h  = '<label class="fld"' . $attr . ' data-hide-empty>' . $label . '<select name="itype" data-dep="sub"><option value="">' . esc_html( qs_t( 'pick' ) ) . '</option>';
		foreach ( array_merge( array( 'spare-parts', 'accessories', 'tools' ), array_slice( $secs, 5 ) ) as $sec ) {
			foreach ( qs_children( $sec ) as $sub ) {
				foreach ( qs_children( $sub ) as $it ) {
					$h .= '<option value="' . esc_attr( $it->slug ) . '" data-p="' . esc_attr( $sub->slug ) . '"' . selected( $cur, $it->slug, false ) . '>' . esc_html( qs_term_name( $it ) ) . '</option>';
				}
			}
		}
		$h .= '</select>';
	} elseif ( 'select' === $kind ) {
		$h .= '<select name="' . esc_attr( $k ) . '"><option value="">' . esc_html( qs_t( 'pick' ) ) . '</option>';
		foreach ( qs_field_options( $src, $g ) as $o ) {
			$h .= '<option value="' . esc_attr( $o[0] ) . '"' . selected( $cur, (string) $o[0], false ) . '>' . esc_html( $o[1] ) . '</option>';
		}
		$h .= '</select>';
	} elseif ( 'textarea' === $kind ) {
		$h .= '<textarea name="desc" rows="5" placeholder="' . esc_attr( qs_t( 'about_ph' ) ) . '">' . esc_textarea( $cur ) . '</textarea>';
	} else {
		$h .= '<input type="' . esc_attr( $kind ) . '" name="' . esc_attr( $k ) . '" value="' . esc_attr( $cur ) . '"' . ( 'number' === $kind ? ' min="0" step="any" inputmode="decimal"' : '' ) . ( 'title' === $k ? ' placeholder="' . esc_attr( qs_t( 'title_ph' ) ) . '"' : '' ) . '>';
	}
	return $h . $req . '</label>';
};
?>
<div class="wrap post">
	<?php echo qs_notice(); // phpcs:ignore ?>
	<form class="post-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" novalidate data-postform>
		<input type="hidden" name="action" value="qs_post_ad"><?php wp_nonce_field( 'qs_post_ad', '_qsn' ); ?><input type="hidden" name="edit" value="<?php echo (int) $edit; ?>">
		<script type="application/json" id="qs-models-data"><?php echo wp_json_encode( $models ); ?></script><datalist id="qs-models"></datalist>

		<div class="post-card"><h2><span class="step-n">1</span> <?php qs_e( 'choose_section' ); ?></h2>
			<div class="type-grid mains">
				<?php foreach ( qs_nav() as $n ) : ?>
				<label class="type-card sec-card main-card<?php echo $main === $n['slug'] ? ' on' : ''; ?>"><input type="radio" name="main" value="<?php echo esc_attr( $n['slug'] ); ?>" data-group="<?php echo esc_attr( qs_group_of( $n['slug'] ) ); ?>"<?php checked( $main, $n['slug'] ); ?> hidden><span class="sc-ic"><?php echo qs_ic( $n['icon'] ); ?></span><b><?php echo esc_html( $n['label'] ); ?></b><i class="tc-check"><?php echo qs_ic( 'check' ); ?></i></label>
				<?php endforeach; ?>
			</div>
			<div class="car-secs" data-car-secs<?php echo 'cars' === $main ? '' : ' hidden'; ?>><h3><?php qs_e( 'choose_car_sec' ); ?></h3>
			<div class="type-grid five">
				<?php foreach ( qs_sections() as $slug => $x ) : $t = qs_term( $slug ); if ( ! $t ) { continue; } ?>
				<label class="type-card sec-card<?php echo $section === $slug ? ' on' : ''; ?>"><input type="radio" name="section" value="<?php echo esc_attr( $slug ); ?>" data-group="<?php echo esc_attr( $x[2] ); ?>"<?php checked( $section, $slug ); ?> hidden><span class="sc-ic"><?php echo qs_ic( $x[1] ); ?></span><b><?php echo esc_html( qs_term_name( $t ) ); ?></b><small><?php qs_e( 'd_' . str_replace( '-', '_', $slug ) ); ?></small><i class="tc-check"><?php echo qs_ic( 'check' ); ?></i></label>
				<?php endforeach; ?>
			</div></div>
		</div>

		<div class="post-card" data-after-type<?php echo $section ? '' : ' hidden'; ?>><h2><span class="step-n">2</span> <?php qs_e( 'details' ); ?></h2>
			<div class="form-grid">
				<?php foreach ( $secs as $slug ) : ?>
				<label class="fld" data-sec="<?php echo esc_attr( $slug ); ?>" data-required><span><?php qs_e( 'subsection' ); ?> <i>*</i></span><select name="sub"><option value=""><?php qs_e( 'choose_sub' ); ?></option><?php foreach ( qs_children( $slug ) as $c ) { echo '<option value="' . esc_attr( $c->slug ) . '"' . selected( $v( 'sub' ), $c->slug, false ) . '>' . esc_html( qs_term_name( $c ) ) . '</option>'; } ?></select><?php echo $req; // phpcs:ignore ?></label>
				<?php endforeach; ?>
				<?php
				foreach ( qs_fields() as $k => $f ) {
					foreach ( explode( ' ', $f[2] ) as $g ) {
						echo $field( $k, $f, $g ); // phpcs:ignore
					}
				}
				?>
				<label class="fld full"><span><?php qs_e( 'title_en' ); ?></span><input name="title_en" class="ltr-in" dir="ltr" value="<?php echo esc_attr( $v( 'title_en' ) ); ?>"></label>
			</div>
		</div>

		<div class="post-card" data-after-type<?php echo $section ? '' : ' hidden'; ?>><h2><span class="step-n">3</span> <?php qs_e( 'photos' ); ?></h2>
			<label class="drop"><input type="file" name="photos[]" accept="image/*" multiple hidden data-photos><?php echo qs_ic( 'upload' ); ?><b><?php qs_e( 'up_t' ); ?></b><small><?php qs_e( 'photos_hint' ); ?></small></label>
			<?php if ( $edit && has_post_thumbnail( $edit ) ) : ?><p class="tip"><?php echo qs_ic( 'info' ) . ' '; qs_e( 'photos_keep' ); ?></p><?php endif; ?>
			<div class="pthumbs" id="pthumbs"><?php if ( $edit && has_post_thumbnail( $edit ) ) { echo '<div class="pthumb">' . get_the_post_thumbnail( $edit, 'thumbnail' ) . '</div>'; } // phpcs:ignore ?></div>
		</div>

		<div class="post-card" data-after-type<?php echo $section ? '' : ' hidden'; ?>><h2><span class="step-n">4</span> <?php qs_e( 'location' ); ?></h2>
			<div class="form-grid">
				<label class="fld" data-required><span><?php qs_e( 'emirate' ); ?> <i>*</i></span><select name="emirate"><option value=""><?php qs_e( 'pick' ); ?></option><?php foreach ( qs_children( 0, 'qs_city' ) as $e ) { echo '<option value="' . esc_attr( $e->slug ) . '"' . selected( $v( 'emirate' ), $e->slug, false ) . '>' . esc_html( qs_term_name( $e ) ) . '</option>'; } ?></select><?php echo $req; // phpcs:ignore ?></label>
				<label class="fld"><span><?php qs_e( 'city' ); ?></span><select name="city" data-dep="emirate"><option value=""><?php qs_e( 'pick' ); ?></option><?php foreach ( qs_children( 0, 'qs_city' ) as $e ) { foreach ( qs_children( $e, 'qs_city' ) as $c ) { echo '<option value="' . esc_attr( $c->slug ) . '" data-p="' . esc_attr( $e->slug ) . '"' . selected( $v( 'city' ), $c->slug, false ) . '>' . esc_html( qs_term_name( $c ) ) . '</option>'; } } ?></select></label>
				<label class="fld"><span><?php qs_e( 'area' ); ?></span><input name="area" value="<?php echo esc_attr( $v( 'area' ) ); ?>"></label>
				<label class="fld" data-required><span><?php qs_e( 'phone' ); ?> <i>*</i></span><div class="phone-in"><span class="cc">+971</span><input name="phone" class="ltr-in" inputmode="tel" value="<?php echo esc_attr( preg_replace( '/^971/', '', (string) ( $v( 'phone' ) ?: get_user_meta( $uid, 'qs_phone', true ) ) ) ); ?>"></div><?php echo $req; // phpcs:ignore ?></label>
				<div class="fld full"><span><?php qs_e( 'map_pick' ); ?></span>
					<?php echo qs_map( $v( 'lat' ), $v( 'lng' ), true ); // phpcs:ignore ?>
					<div class="map-tools"><small class="muted"><?php echo qs_ic( 'info', 'sm' ) . ' '; qs_e( 'map_hint' ); ?></small><button type="button" class="btn btn-sm btn-line" data-act="locate"><?php echo qs_ic( 'pin' ) . ' '; qs_e( 'my_location' ); ?></button></div>
					<input type="hidden" name="lat" value="<?php echo esc_attr( $v( 'lat' ) ); ?>"><input type="hidden" name="lng" value="<?php echo esc_attr( $v( 'lng' ) ); ?>"></div>
			</div>
		</div>

		<div class="post-card" data-after-type<?php echo $section ? '' : ' hidden'; ?>><h2><span class="step-n">5</span> <?php qs_e( 'confirm' ); ?></h2>
			<div class="review-note"><?php echo qs_ic( 'eye' ) . ' '; qs_e( 'review_note' ); ?></div>
			<label class="check"><input type="checkbox" name="agree" value="1"> <?php qs_e( 'declare' ); ?></label>
			<div class="post-foot"><span class="muted small" data-form-err hidden><?php echo qs_ic( 'info', 'sm' ) . ' '; qs_e( 'err_required' ); ?></span><button class="btn btn-y btn-lg" type="submit"><?php echo qs_ic( 'send' ) . ' '; qs_e( 'publish' ); ?></button></div>
		</div>
	</form>
</div>
<?php
get_footer();
