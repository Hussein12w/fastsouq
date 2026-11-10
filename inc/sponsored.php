<?php
/**
 * Sponsored ad spaces (إعلانات ممولة): banners sold to companies, managed from the dashboard → "المساحات الإعلانية".
 * Each banner: image (desktop) + optional phone image, link, places on the site, start / end dates,
 * optional main categories, and counters for views and clicks.
 */
defined( 'ABSPATH' ) || exit;

/** Places on the site: key => [Arabic label, recommended size]. */
function qs_spons_places() {
	return array(
		'home_top' => array( 'الرئيسية — بانر عريض أعلى الصفحة (تحت الفئات)', '1200×200' ),
		'home_mid' => array( 'الرئيسية — بانر عريض بين أقسام الإعلانات', '1200×200' ),
		'cat_side' => array( 'صفحات الفئات والبحث — بانر طولي بجانب الإعلانات', '300×600' ),
		'cat_list' => array( 'صفحات الفئات والبحث — بانر عريض داخل قائمة الإعلانات', '900×160' ),
		'ad_side'  => array( 'صفحة الإعلان — بانر بجانب تفاصيل الإعلان', '300×250' ),
	);
}

add_action(
	'init',
	function () {
		register_post_type(
			'qs_banner',
			array(
				'labels'          => array( 'name' => 'المساحات الإعلانية', 'singular_name' => 'إعلان ممول', 'add_new' => 'إضافة إعلان ممول', 'add_new_item' => 'إضافة إعلان ممول', 'edit_item' => 'تعديل الإعلان الممول', 'all_items' => 'كل الإعلانات الممولة', 'menu_name' => 'المساحات الإعلانية', 'featured_image' => 'صورة الإعلان (للكمبيوتر)', 'set_featured_image' => 'اختيار صورة الإعلان', 'remove_featured_image' => 'إزالة الصورة', 'use_featured_image' => 'استخدام كصورة الإعلان' ),
				'public'          => false,
				'show_ui'         => true,
				'menu_icon'       => 'dashicons-megaphone',
				'menu_position'   => 6,
				'supports'        => array( 'title', 'thumbnail' ),
				'capability_type' => 'post',
			)
		);
	}
);

/* ---------- Dashboard ---------- */
add_action(
	'add_meta_boxes_qs_banner',
	function () {
		add_meta_box( 'qs_banner_box', 'إعدادات الإعلان الممول', 'qs_banner_box', 'qs_banner', 'normal', 'high' );
		add_meta_box( 'qs_banner_stats', 'الإحصائيات', 'qs_banner_stats_box', 'qs_banner', 'side' );
	}
);
add_action(
	'admin_enqueue_scripts',
	function () {
		if ( 'qs_banner' === get_current_screen()->post_type ) {
			wp_enqueue_media();
		}
	}
);
function qs_banner_box( $post ) {
	wp_nonce_field( 'qs_banner_save', 'qs_banner_nonce' );
	$m      = fn( $k ) => get_post_meta( $post->ID, '_qsb_' . $k, true );
	$places = (array) $m( 'places' );
	$cats   = (array) $m( 'cats' );
	echo '<p style="background:#f0f6fc;border-inline-start:4px solid #2271b1;padding:8px 12px">ارفع صورة الإعلان من مربع «صورة الإعلان» على الجانب. المقاس المقترح مكتوب بجانب كل مكان. الإعلان يظهر فقط وهو «منشور» وضمن تاريخَي البداية والنهاية.</p>';
	echo '<table class="form-table"><tbody>';
	echo '<tr><th>رابط الإعلان</th><td><input type="url" name="qsb[url]" value="' . esc_attr( $m( 'url' ) ) . '" class="large-text" dir="ltr" placeholder="https://"><p class="description">موقع الشركة، أو رابط واتساب مثل https://wa.me/9715xxxxxxxx</p></td></tr>';
	echo '<tr><th>اسم المعلن</th><td><input name="qsb[client]" value="' . esc_attr( $m( 'client' ) ) . '" class="regular-text"><p class="description">للتنظيم فقط، لا يظهر في الموقع.</p></td></tr>';
	echo '<tr><th>أماكن الظهور</th><td>';
	foreach ( qs_spons_places() as $k => $p ) {
		echo '<label style="display:block;margin:4px 0"><input type="checkbox" name="qsb[places][]" value="' . esc_attr( $k ) . '"' . checked( in_array( $k, $places, true ), true, false ) . '> ' . esc_html( $p[0] ) . ' <code dir="ltr">' . esc_html( $p[1] ) . '</code></label>';
	}
	echo '</td></tr>';
	$img = $m( 'mobile' );
	echo '<tr><th>صورة للجوال (اختيارية)</th><td><input type="url" name="qsb[mobile]" id="qsb-mobile" value="' . esc_attr( $img ) . '" class="large-text" dir="ltr"> <button type="button" class="button" id="qsb-pick">اختيار صورة</button><p class="description">تظهر على الشاشات الصغيرة بدل صورة الكمبيوتر. مقترح للبانر العريض: 800×300.</p></td></tr>';
	echo '<tr><th>تاريخ البداية</th><td><input type="date" name="qsb[start]" value="' . esc_attr( $m( 'start' ) ) . '"> <span class="description">فارغ = يبدأ فوراً</span></td></tr>';
	echo '<tr><th>تاريخ النهاية</th><td><input type="date" name="qsb[end]" value="' . esc_attr( $m( 'end' ) ) . '"> <span class="description">فارغ = بدون نهاية. بعد هذا اليوم يتوقف الإعلان تلقائياً.</span></td></tr>';
	echo '<tr><th>الفئات</th><td><select name="qsb[cats][]" multiple size="8" style="min-width:320px">';
	foreach ( qs_children( 0 ) as $t ) {
		echo '<option value="' . esc_attr( $t->slug ) . '"' . selected( in_array( $t->slug, $cats, true ), true, false ) . '>' . esc_html( $t->name ) . '</option>';
	}
	echo '</select><p class="description">اختر فئة أو أكثر ليظهر الإعلان في صفحاتها وإعلاناتها فقط (Ctrl / Cmd للاختيار المتعدد). بدون اختيار = يظهر في كل الفئات.</p></td></tr>';
	echo '<tr><th>فتح في نافذة جديدة</th><td><label><input type="checkbox" name="qsb[blank]" value="1"' . checked( '0' !== (string) $m( 'blank' ), true, false ) . '> نعم</label></td></tr>';
	echo '</tbody></table>';
	?>
	<script>
	jQuery(function ($) {
		$('#qsb-pick').on('click', function (e) {
			e.preventDefault();
			var f = wp.media({ title: 'صورة الجوال', multiple: false, library: { type: 'image' } });
			f.on('select', function () { $('#qsb-mobile').val(f.state().get('selection').first().toJSON().url); });
			f.open();
		});
	});
	</script>
	<?php
}
function qs_banner_stats_box( $post ) {
	$v = (int) get_post_meta( $post->ID, '_qsb_views', true );
	$c = (int) get_post_meta( $post->ID, '_qsb_clicks', true );
	echo '<p>المشاهدات: <b>' . esc_html( number_format( $v ) ) . '</b><br>النقرات: <b>' . esc_html( number_format( $c ) ) . '</b><br>نسبة النقر: <b>' . esc_html( $v ? round( 100 * $c / $v, 2 ) . '%' : '—' ) . '</b></p>';
	echo '<label><input type="checkbox" name="qsb_reset" value="1"> تصفير الإحصائيات</label>';
}
add_action(
	'save_post_qs_banner',
	function ( $id ) {
		if ( ! isset( $_POST['qs_banner_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qs_banner_nonce'] ) ), 'qs_banner_save' ) || ! current_user_can( 'edit_post', $id ) || wp_is_post_revision( $id ) ) {
			return;
		}
		$in    = isset( $_POST['qsb'] ) ? (array) wp_unslash( $_POST['qsb'] ) : array();
		$date  = fn( $d ) => preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $d ) ? $d : '';
		$valid = array_keys( qs_spons_places() );
		update_post_meta( $id, '_qsb_url', esc_url_raw( $in['url'] ?? '' ) );
		update_post_meta( $id, '_qsb_client', sanitize_text_field( $in['client'] ?? '' ) );
		update_post_meta( $id, '_qsb_mobile', esc_url_raw( $in['mobile'] ?? '' ) );
		update_post_meta( $id, '_qsb_places', array_values( array_intersect( array_map( 'sanitize_key', (array) ( $in['places'] ?? array() ) ), $valid ) ) );
		update_post_meta( $id, '_qsb_cats', array_values( array_map( 'sanitize_title', (array) ( $in['cats'] ?? array() ) ) ) );
		update_post_meta( $id, '_qsb_start', $date( $in['start'] ?? '' ) );
		update_post_meta( $id, '_qsb_end', $date( $in['end'] ?? '' ) );
		update_post_meta( $id, '_qsb_blank', empty( $in['blank'] ) ? '0' : '1' );
		if ( ! empty( $_POST['qsb_reset'] ) ) {
			update_post_meta( $id, '_qsb_views', 0 );
			update_post_meta( $id, '_qsb_clicks', 0 );
		}
	}
);
add_filter(
	'manage_qs_banner_posts_columns',
	fn( $c ) => array( 'cb' => $c['cb'], 'qsb_img' => '', 'title' => $c['title'], 'qsb_places' => 'الأماكن', 'qsb_dates' => 'المدة', 'qsb_state' => 'الحالة', 'qsb_stats' => 'مشاهدات / نقرات' )
);
add_action(
	'manage_qs_banner_posts_custom_column',
	function ( $col, $id ) {
		$p = qs_spons_places();
		switch ( $col ) {
			case 'qsb_img':
				echo get_the_post_thumbnail( $id, array( 90, 60 ) ); // phpcs:ignore
				break;
			case 'qsb_places':
				echo esc_html( implode( ' | ', array_map( fn( $k ) => $p[ $k ][0] ?? $k, (array) get_post_meta( $id, '_qsb_places', true ) ) ) );
				break;
			case 'qsb_dates':
				echo esc_html( ( get_post_meta( $id, '_qsb_start', true ) ?: '…' ) . ' ← ' . ( get_post_meta( $id, '_qsb_end', true ) ?: '…' ) );
				break;
			case 'qsb_state':
				echo qs_banner_live( $id ) ? '<b style="color:#16a34a">● يعمل</b>' : '<span style="color:#888">● متوقف</span>';
				break;
			case 'qsb_stats':
				echo esc_html( number_format( (int) get_post_meta( $id, '_qsb_views', true ) ) . ' / ' . number_format( (int) get_post_meta( $id, '_qsb_clicks', true ) ) );
				break;
		}
	},
	10,
	2
);

/* ---------- Front end ---------- */
function qs_banner_live( $id ) {
	$today = wp_date( 'Y-m-d' );
	$s     = get_post_meta( $id, '_qsb_start', true );
	$e     = get_post_meta( $id, '_qsb_end', true );
	return 'publish' === get_post_status( $id ) && has_post_thumbnail( $id ) && ( ! $s || $s <= $today ) && ( ! $e || $e >= $today );
}
/** Main category slug of a term (for car sections: "cars"). */
function qs_main_slug( $term ) {
	if ( ! $term || is_wp_error( $term ) ) {
		return '';
	}
	while ( $term->parent ) {
		$term = get_term( $term->parent, 'qs_cat' );
	}
	return $term->slug;
}
/**
 * One banner for a place (random when several are running). $term: the page's category, if any.
 * Shows the "Advertise here" box in the side places when nothing is running (Customizer → FastSouq).
 */
function qs_sponsored( $place, $term = null ) {
	static $used = array();
	$main = qs_main_slug( $term );
	$ids  = get_posts( array( 'post_type' => 'qs_banner', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => 50, 'no_found_rows' => true, 'meta_query' => array( array( 'key' => '_qsb_places', 'value' => '"' . $place . '"', 'compare' => 'LIKE' ) ) ) );
	$ids  = array_values(
		array_filter(
			$ids,
			function ( $id ) use ( $main, $used ) {
				$cats = array_filter( (array) get_post_meta( $id, '_qsb_cats', true ) );
				return ! in_array( $id, $used, true ) && qs_banner_live( $id ) && ( ! $cats || ( $main && in_array( $main, $cats, true ) ) );
			}
		)
	);
	if ( ! $ids ) {
		if ( in_array( $place, array( 'cat_side', 'ad_side' ), true ) && get_theme_mod( 'qs_ad_placeholder', 1 ) ) {
			$wa   = preg_replace( '/\D/', '', (string) get_theme_mod( 'qs_whatsapp', '' ) );
			$link = $wa ? 'https://wa.me/' . $wa . '?text=' . rawurlencode( qs_t( 'advertise_msg' ) ) : ( get_theme_mod( 'qs_email' ) ? 'mailto:' . get_theme_mod( 'qs_email' ) : '' );
			return '<div class="spons spons-' . esc_attr( $place ) . ' spons-empty">' . ( $link ? '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener">' : '<div>' ) . '<span class="spons-ic">' . qs_ic( 'megaphone' ) . '</span><b>' . esc_html( qs_t( 'advertise_t' ) ) . '</b><small>' . esc_html( qs_t( 'advertise_p' ) ) . '</small>' . ( $link ? '<span class="btn btn-sm btn-y">' . esc_html( qs_t( 'advertise_btn' ) ) . '</span></a>' : '</div>' ) . '</div>';
		}
		return '';
	}
	$id     = $ids[ array_rand( $ids ) ];
	$used[] = $id;
	update_post_meta( $id, '_qsb_views', (int) get_post_meta( $id, '_qsb_views', true ) + 1 );
	$desk   = get_the_post_thumbnail_url( $id, 'full' );
	$mob    = get_post_meta( $id, '_qsb_mobile', true );
	$url    = get_post_meta( $id, '_qsb_url', true );
	$img    = '<picture>' . ( $mob ? '<source media="(max-width:700px)" srcset="' . esc_url( $mob ) . '">' : '' ) . '<img src="' . esc_url( $desk ) . '" alt="' . esc_attr( get_the_title( $id ) ) . '" loading="lazy"></picture>';
	$blank  = '0' !== (string) get_post_meta( $id, '_qsb_blank', true );
	$inner  = $url ? '<a href="' . esc_url( add_query_arg( 'qs_bn', $id, home_url( '/' ) ) ) . '" rel="sponsored nofollow noopener"' . ( $blank ? ' target="_blank"' : '' ) . '>' . $img . '</a>' : $img;
	return '<div class="spons spons-' . esc_attr( $place ) . '">' . $inner . '<span class="spons-lbl">' . esc_html( qs_t( 'sponsored' ) ) . '</span></div>';
}
/* Click counter, then on to the advertiser. */
add_action(
	'template_redirect',
	function () {
		$id = isset( $_GET['qs_bn'] ) ? absint( $_GET['qs_bn'] ) : 0;
		if ( ! $id || 'qs_banner' !== get_post_type( $id ) ) {
			return;
		}
		$url = get_post_meta( $id, '_qsb_url', true );
		if ( ! $url || ! qs_banner_live( $id ) ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
		update_post_meta( $id, '_qsb_clicks', (int) get_post_meta( $id, '_qsb_clicks', true ) + 1 );
		wp_redirect( $url ); // phpcs:ignore -- the advertiser's own link, set by staff
		exit;
	},
	0
);
