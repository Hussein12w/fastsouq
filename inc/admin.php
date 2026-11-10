<?php
/** Dashboard: ad details box, list columns (photo, type, price, featured, reports), English names on terms. */
defined( 'ABSPATH' ) || exit;

function qs_admin_fields() {
	$o = fn( $l ) => array( '' => '—' ) + array_map( fn( $v ) => explode( '|', $v )[0], qs_options( $l ) );
	return array(
		'featured'       => array( 'مميز (يظهر أولاً)', 'check' ),
		'featured_until' => array( 'مميز حتى (طابع زمني — يُضبط تلقائياً عند الدفع)', 'number' ),
		'price'          => array( 'السعر (درهم)', 'number' ),
		'deal'           => array( 'نوع العرض', 'select', $o( 'deal' ) ),
		'year'           => array( 'سنة الصنع', 'number' ),
		'cond'           => array( 'الحالة', 'select', $o( 'cond' ) ),
		'km'             => array( 'الكيلومترات', 'number' ),
		'gear'           => array( 'ناقل الحركة', 'select', $o( 'gear' ) ),
		'fuel'           => array( 'الوقود', 'select', $o( 'fuel' ) ),
		'cyl'            => array( 'الأسطوانات', 'select', $o( 'cyl' ) ),
		'specs'          => array( 'المواصفات', 'select', $o( 'specs' ) ),
		'color'          => array( 'اللون', 'select', $o( 'color' ) ),
		'body'           => array( 'النوع (sedan, suv…)', 'text' ),
		'brand'          => array( 'الشركة المصنعة للقطعة', 'text' ),
		'part_type'      => array( 'نوع القطعة', 'select', $o( 'part_type' ) ),
		'sku'            => array( 'رقم القطعة', 'text' ),
		'period'         => array( 'مدة الإيجار', 'select', $o( 'period' ) ),
		'area'           => array( 'المنطقة / العنوان', 'text' ),
		'lat'            => array( 'Latitude', 'text' ),
		'lng'            => array( 'Longitude', 'text' ),
		'phone'          => array( 'الهاتف (9715XXXXXXXX)', 'text' ),
		'title_en'       => array( 'Title (English)', 'text' ),
		'desc_en'        => array( 'Description (English)', 'textarea' ),
	);
}
add_action(
	'add_meta_boxes_listing',
	function () {
		add_meta_box( 'qs_details', 'تفاصيل الإعلان', 'qs_details_box', 'listing', 'normal', 'high' );
		add_meta_box( 'qs_terms', 'القسم والخدمات', 'qs_terms_box', 'listing', 'side', 'high' );
		add_meta_box( 'qs_reports', 'البلاغات', 'qs_reports_box', 'listing', 'side' );
	}
);
function qs_details_box( $post ) {
	wp_nonce_field( 'qs_admin_save', 'qs_admin_nonce' );
	echo '<table class="form-table"><tbody>';
	foreach ( qs_admin_fields() as $k => $f ) {
		$v  = get_post_meta( $post->ID, '_qs_' . $k, true );
		$nm = 'qsf[' . esc_attr( $k ) . ']';
		echo '<tr><th><label>' . esc_html( $f[0] ) . '</label></th><td>';
		if ( 'select' === $f[1] ) {
			echo '<select name="' . $nm . '">'; // phpcs:ignore
			foreach ( $f[2] as $ov => $ol ) {
				echo '<option value="' . esc_attr( $ov ) . '"' . selected( (string) $v, (string) $ov, false ) . '>' . esc_html( $ol ) . '</option>';
			}
			echo '</select>';
		} elseif ( 'check' === $f[1] ) {
			echo '<input type="checkbox" name="' . $nm . '" value="1"' . checked( (bool) $v, true, false ) . '>'; // phpcs:ignore
		} elseif ( 'textarea' === $f[1] ) {
			echo '<textarea name="' . $nm . '" rows="3" class="large-text" dir="auto">' . esc_textarea( $v ) . '</textarea>'; // phpcs:ignore
		} else {
			echo '<input type="' . esc_attr( $f[1] ) . '" name="' . $nm . '" value="' . esc_attr( $v ) . '" class="regular-text" dir="auto" step="any">'; // phpcs:ignore
		}
		echo '</td></tr>';
	}
	echo '<tr><th>المشاهدات</th><td>' . (int) get_post_meta( $post->ID, '_qs_views', true ) . '</td></tr></tbody></table>';
}
function qs_terms_box( $post ) {
	$sel = function ( $tax, $name, $label, $depth ) use ( $post ) {
		$cur = wp_get_object_terms( $post->ID, $tax, array( 'fields' => 'ids' ) );
		echo '<p><b>' . esc_html( $label ) . '</b><br><select name="' . esc_attr( $name ) . '" style="width:100%"><option value="">—</option>';
		$walk = function ( $parent, $lvl ) use ( &$walk, $tax, $cur, $depth ) {
			foreach ( qs_children( $parent, $tax ) as $t ) {
				echo '<option value="' . (int) $t->term_id . '"' . selected( in_array( $t->term_id, $cur, true ), true, false ) . '>' . esc_html( str_repeat( '— ', $lvl ) . $t->name ) . '</option>';
				if ( $lvl + 1 < $depth ) {
					$walk( $t, $lvl + 1 );
				}
			}
		};
		$walk( 0, 0 );
		echo '</select></p>';
	};
	$sel( 'qs_cat', 'qs_cat', 'القسم (اختر أدق مستوى)', 4 );
	$sel( 'qs_make', 'qs_make', 'الماركة / الموديل', 2 );
	$sel( 'qs_city', 'qs_city', 'الإمارة / المدينة', 2 );
}
function qs_reports_box( $post ) {
	$list = array_filter( (array) get_post_meta( $post->ID, '_qs_report_list', true ) );
	if ( ! $list ) {
		echo '<p>لا توجد بلاغات.</p>';
		return;
	}
	echo '<ul>';
	foreach ( array_reverse( $list ) as $r ) {
		echo '<li>' . esc_html( ( $r['reason'] ?? '' ) . ' — ' . wp_date( 'Y-m-d H:i', $r['time'] ?? 0 ) ) . '</li>';
	}
	echo '</ul><label><input type="checkbox" name="qs_clear_reports" value="1"> مسح البلاغات</label>';
}
add_action(
	'save_post_listing',
	function ( $id ) {
		if ( ! isset( $_POST['qs_admin_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qs_admin_nonce'] ) ), 'qs_admin_save' ) || ! current_user_can( 'edit_post', $id ) || wp_is_post_revision( $id ) ) {
			return;
		}
		$in = isset( $_POST['qsf'] ) ? (array) wp_unslash( $_POST['qsf'] ) : array();
		foreach ( qs_admin_fields() as $k => $f ) {
			$v = $in[ $k ] ?? '';
			$v = 'check' === $f[1] ? ( $v ? 1 : 0 ) : ( 'textarea' === $f[1] ? sanitize_textarea_field( $v ) : sanitize_text_field( $v ) );
			if ( in_array( $k, array( 'price', 'year', 'km' ), true ) && '' !== $v ) {
				$v = (float) $v;
			}
			update_post_meta( $id, '_qs_' . $k, $v );
		}
		foreach ( array( 'qs_cat', 'qs_make', 'qs_city' ) as $tax ) {
			if ( isset( $_POST[ $tax ] ) ) {
				$t = (int) $_POST[ $tax ];
				wp_set_object_terms( $id, $t ? array_merge( array( $t ), get_ancestors( $t, $tax ) ) : array(), $tax );
			}
		}
		$top = qs_levels( $id )[0];
		if ( $top ) {
			update_post_meta( $id, '_qs_group', qs_group_of( $top->slug ) );
		}
		if ( ! empty( $_POST['qs_clear_reports'] ) ) {
			delete_post_meta( $id, '_qs_report_list' );
			update_post_meta( $id, '_qs_reports', 0 );
		}
	}
);

add_filter(
	'manage_listing_posts_columns',
	function ( $c ) {
		return array( 'cb' => $c['cb'], 'qs_thumb' => '', 'title' => $c['title'], 'taxonomy-qs_cat' => 'القسم', 'taxonomy-qs_city' => 'الموقع', 'qs_price' => 'السعر', 'qs_flags' => 'مميز', 'qs_reports' => 'بلاغات', 'author' => 'المعلن', 'date' => $c['date'] );
	}
);
add_action(
	'manage_listing_posts_custom_column',
	function ( $col, $id ) {
		if ( 'qs_thumb' === $col ) {
			echo get_the_post_thumbnail( $id, array( 64, 48 ), array( 'style' => 'border-radius:6px;object-fit:cover;width:64px;height:48px' ) ) ?: '—'; // phpcs:ignore
		} elseif ( 'qs_price' === $col ) {
			echo wp_kses_post( qs_price( $id ) );
		} elseif ( 'qs_flags' === $col ) {
			$u = (int) get_post_meta( $id, '_qs_featured_until', true );
			echo get_post_meta( $id, '_qs_featured', true ) ? '★' . ( $u ? ' <small>' . esc_html( wp_date( 'Y-m-d', $u ) ) . '</small>' : '' ) : '—'; // phpcs:ignore
		} elseif ( 'qs_reports' === $col ) {
			$n = (int) get_post_meta( $id, '_qs_reports', true );
			echo $n ? '<b style="color:#b91c1c">' . $n . '</b>' : '0'; // phpcs:ignore
		}
	},
	10,
	2
);
add_action(
	'admin_menu',
	function () {
		global $menu;
		$n = (int) wp_count_posts( 'listing' )->pending;
		foreach ( $menu as $i => $m ) {
			if ( $n && 'edit.php?post_type=listing' === $m[2] ) {
				$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . $n . '</span></span>'; // phpcs:ignore
			}
		}
	},
	99
);

/* Arabic / English names on sections, cities, makes and models; menu place of the main categories (categories are listed alphabetically). */
foreach ( array( 'qs_cat', 'qs_city', 'qs_make' ) as $tax ) {
	add_action(
		$tax . '_edit_form_fields',
		function ( $term ) use ( $tax ) {
			if ( 'qs_cat' === $term->taxonomy && ! $term->parent ) {
				$pos = (int) get_term_meta( $term->term_id, 'menu', true );
				echo '<tr class="form-field"><th><label>مكانها في القائمة العلوية</label></th><td><select name="qs_t[menu]"><option value="0"' . selected( $pos, 0, false ) . '>تحت «المزيد»</option>';
				for ( $i = 1; $i <= 6; $i++ ) {
					echo '<option value="' . $i . '"' . selected( $pos, $i, false ) . '>' . $i . '</option>';
				}
				echo '</select><p class="description">تظهر 6 فئات رئيسية فقط في القائمة العلوية بالترتيب من 1 إلى 6، والباقي تحت «المزيد». إذا اخترت رقماً مستخدماً لفئة أخرى، تنتقل تلك الفئة إلى «المزيد».</p></td></tr>';
			}
			foreach ( array( 'name_ar' => 'الاسم بالعربي', 'name_en' => 'Name (English)' ) as $k => $l ) {
				echo '<tr class="form-field"><th><label>' . esc_html( $l ) . '</label></th><td><input name="qs_t[' . esc_attr( $k ) . ']" value="' . esc_attr( get_term_meta( $term->term_id, $k, true ) ) . '" dir="' . ( 'name_ar' === $k ? 'rtl' : 'ltr' ) . '"></td></tr>';
			}
		}
	);
	add_action(
		$tax . '_add_form_fields',
		function () use ( $tax ) {
			foreach ( array( 'name_ar' => 'الاسم بالعربي', 'name_en' => 'Name (English)' ) as $k => $l ) {
				echo '<div class="form-field"><label>' . esc_html( $l ) . '</label><input name="qs_t[' . esc_attr( $k ) . ']" dir="' . ( 'name_ar' === $k ? 'rtl' : 'ltr' ) . '"></div>';
			}
		}
	);
	$save = function ( $term_id ) {
		if ( current_user_can( 'manage_categories' ) && ! empty( $_POST['qs_t'] ) ) {
			foreach ( (array) wp_unslash( $_POST['qs_t'] ) as $k => $v ) {
				if ( 'menu' === $k ) {
					$pos = min( 6, absint( $v ) );
					if ( $pos ) { // one category per place
						foreach ( get_terms( array( 'taxonomy' => 'qs_cat', 'parent' => 0, 'hide_empty' => false, 'exclude' => array( $term_id ), 'meta_key' => 'menu', 'meta_value' => $pos ) ) as $other ) {
							update_term_meta( $other->term_id, 'menu', 0 );
						}
					}
					update_term_meta( $term_id, 'menu', $pos );
				} elseif ( in_array( $k, array( 'name_ar', 'name_en', 'sort' ), true ) ) {
					update_term_meta( $term_id, $k, sanitize_text_field( $v ) );
				}
			}
		}
	};
	add_action( 'created_' . $tax, $save );
	add_action( 'edited_' . $tax, $save );
}
