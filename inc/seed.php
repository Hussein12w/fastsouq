<?php
/** Creates the classification (on theme activation) and optional demo ads (Tools → FastSouq — data). */
defined( 'ABSPATH' ) || exit;

function qs_seed_term( $tax, $slug, $ar, $en, $sort, $parent = 0, $meta = array() ) {
	$t = get_term_by( 'slug', $slug, $tax );
	if ( $t ) {
		$id = $t->term_id;
	} else {
		$r = wp_insert_term( $ar, $tax, array( 'slug' => $slug, 'parent' => $parent ) );
		if ( is_wp_error( $r ) ) {
			return isset( $r->error_data['term_exists'] ) ? (int) $r->error_data['term_exists'] : 0;
		}
		$id = $r['term_id'];
	}
	foreach ( array( 'name_en' => $en, 'sort' => $sort ) + $meta as $k => $v ) {
		if ( '' === get_term_meta( $id, $k, true ) && '' !== (string) $v ) {
			update_term_meta( $id, $k, $v );
		}
	}
	return $id;
}
/** Safe to run again: only adds what is missing. */
function qs_seed_terms() {
	@set_time_limit( 600 ); // phpcs:ignore
	/* "Cars" (السيارات) holds the five original car sections. */
	$cars = qs_seed_term( 'qs_cat', 'cars', 'السيارات', 'Cars', 0, 0, array( 'icon' => 'car' ) );
	$i    = 0;
	foreach ( qs_sections() as $slug => $x ) {
		$p   = explode( '|', $x[0] );
		$sid = qs_seed_term( 'qs_cat', $slug, $p[0], $p[1], ++$i, $cars, array( 'icon' => $x[1] ) );
		if ( $sid && $cars && (int) get_term( $sid, 'qs_cat' )->parent !== (int) $cars ) {
			wp_update_term( $sid, 'qs_cat', array( 'parent' => $cars ) ); // sites from v1.x: move under "Cars"
		}
		$j = 0;
		foreach ( qs_tree()[ $slug ] as $key => $types ) {
			list( $s, $ar, $en ) = explode( '|', $key );
			$subid = qs_seed_term( 'qs_cat', $s, $ar, $en, ++$j, $sid );
			$k     = 0;
			foreach ( $types as $t ) {
				list( $tar, $ten ) = explode( '|', $t );
				qs_seed_term( 'qs_cat', $s . '-' . sanitize_title( $ten ), $tar, $ten, ++$k, $subid );
			}
		}
	}
	/* The other main categories (spreadsheet). */
	foreach ( qs_mains() as $slug => $x ) {
		$p   = explode( '|', $x[0] );
		$mid = 'cars' === $slug ? $cars : qs_seed_term( 'qs_cat', $slug, $p[0], $p[1], 0, 0, array( 'icon' => $x[1] ) );
		foreach ( $x[2] as $key => $details ) {
			list( $s, $ar, $en ) = explode( '|', $key );
			$subid = qs_seed_term( 'qs_cat', $s, $ar, $en, 0, $mid );
			foreach ( $details as $d ) {
				list( $dar, $den ) = explode( '|', $d );
				qs_seed_term( 'qs_cat', $s . '-' . sanitize_title( $den ), $dar, $den, 0, $subid );
			}
		}
	}
	if ( ! get_option( 'qs_menu_set' ) ) {
		foreach ( qs_default_menu() as $slug => $pos ) {
			if ( $t = get_term_by( 'slug', $slug, 'qs_cat' ) ) {
				update_term_meta( $t->term_id, 'menu', $pos );
			}
		}
		update_option( 'qs_menu_set', 1 );
	}
	$i = 0;
	foreach ( qs_makes() as $origin => $makes ) {
		foreach ( $makes as $make => $models ) {
			$mid = qs_seed_term( 'qs_make', sanitize_title( $make ), $make, $make, ++$i, 0, array( 'origin' => $origin ) );
			foreach ( array_filter( explode( ',', $models ) ) as $k => $m ) {
				qs_seed_term( 'qs_make', sanitize_title( $make . '-' . $m ), $m, $m, $k + 1, $mid );
			}
		}
	}
	$i = 0;
	foreach ( qs_cities() as $key => $cities ) {
		list( $s, $ar, $en ) = explode( '|', $key );
		$eid = qs_seed_term( 'qs_city', $s, $ar, $en, ++$i );
		foreach ( $cities as $k => $c ) {
			list( $cs, $car, $cen ) = explode( '|', $c );
			qs_seed_term( 'qs_city', $cs, $car, $cen, $k + 1, $eid );
		}
	}
	update_option( 'qs_terms_version', QS_TERMS_VERSION );
	clean_term_cache( array(), 'qs_cat' );
}

/* ---------- Demo ads ---------- */
function qs_demo_ads() {
	// [section, sub, item type (slug or ''), title ar, title en, price, make, model, year, km, extra meta, emirate, city]
	return array(
		array( 'vehicles', 'cars-for-sale', '', 'تويوتا لاندكروزر GXR 2021 خليجي', 'Toyota Land Cruiser GXR 2021 GCC', 235000, 'toyota', 'Land Cruiser', 2021, 64000, array( 'body' => 'suv', 'cond' => 'used', 'gear' => 'auto', 'fuel' => 'petrol', 'cyl' => '8', 'specs' => 'gcc', 'color' => 'white', 'featured' => 1 ), 'dubai', 'al-quoz' ),
		array( 'vehicles', 'cars-for-sale', '', 'نيسان باترول بلاتينيوم 2022', 'Nissan Patrol Platinum 2022', 268000, 'nissan', 'Patrol', 2022, 38000, array( 'body' => 'suv', 'cond' => 'used', 'gear' => 'auto', 'fuel' => 'petrol', 'cyl' => '8', 'specs' => 'gcc', 'color' => 'black', 'featured' => 1 ), 'abu-dhabi', 'khalifa-city' ),
		array( 'vehicles', 'cars-for-sale', '', 'سوزوكي جمني 2023 أوتوماتيك', 'Suzuki Jimny 2023 automatic', 79000, 'suzuki', 'Jimny', 2023, 21000, array( 'body' => '4x4', 'cond' => 'used', 'gear' => 'auto', 'fuel' => 'petrol', 'cyl' => '4', 'specs' => 'gcc', 'color' => 'green' ), 'sharjah', 'sharjah-city' ),
		array( 'vehicles', 'cars-for-sale', '', 'مرسيدس E300 موديل 2020', 'Mercedes-Benz E300 2020', 129000, 'mercedes-benz', 'E-Class', 2020, 72000, array( 'body' => 'sedan', 'cond' => 'used', 'gear' => 'auto', 'fuel' => 'petrol', 'cyl' => '4', 'specs' => 'european', 'color' => 'silver' ), 'dubai', 'dubai-city' ),
		array( 'vehicles', 'cars-for-sale', '', 'تسلا موديل 3 لونج رينج 2023', 'Tesla Model 3 Long Range 2023', 142000, 'tesla', 'Model 3', 2023, 15000, array( 'body' => 'electric', 'cond' => 'used', 'gear' => 'auto', 'fuel' => 'electric', 'cyl' => '0', 'specs' => 'american', 'color' => 'blue', 'featured' => 1 ), 'dubai', 'jebel-ali' ),
		array( 'vehicles', 'cars-for-sale', '', 'هيونداي إلنترا 2024 جديدة', 'Brand-new Hyundai Elantra 2024', 72000, 'hyundai', 'Elantra', 2024, 0, array( 'body' => 'sedan', 'cond' => 'new', 'gear' => 'auto', 'fuel' => 'petrol', 'cyl' => '4', 'specs' => 'gcc', 'color' => 'grey' ), 'ajman', 'ajman-city' ),
		array( 'vehicles', 'cars-for-rent', '', 'تأجير كيا بيجاس يومي وشهري', 'Kia Pegas daily & monthly rent', 90, 'kia', 'Pegas', 2024, 5000, array( 'body' => 'sedan', 'cond' => 'used', 'gear' => 'auto', 'fuel' => 'petrol', 'specs' => 'gcc', 'period' => 'day' ), 'dubai', 'deira' ),
		array( 'vehicles', 'motorcycles', '', 'دراجة ياماها R1 موديل 2021', 'Yamaha R1 2021', 58000, '', '', 2021, 9000, array( 'body' => 'sport', 'cond' => 'used', 'gear' => 'manual', 'fuel' => 'petrol' ), 'ras-al-khaimah', 'al-hamra' ),
		array( 'vehicles', 'trucks', '', 'شاحنة ميتسوبيشي كانتر 2019 سطحة', 'Mitsubishi Canter 2019 flatbed', 61000, 'mitsubishi', '', 2019, 180000, array( 'body' => 'flatbed', 'cond' => 'used', 'gear' => 'manual', 'fuel' => 'diesel' ), 'sharjah', 'al-dhaid' ),
		array( 'vehicles', 'number-plates', '', 'لوحة دبي مميزة رقم ثلاثي', 'Special 3-digit Dubai plate', 95000, '', '', '', '', array(), 'dubai', 'dubai-city' ),
		array( 'spare-parts', 'brakes', 'brakes-brake-pads', 'فحمات فرامل أمامية لاندكروزر أصلية', 'Genuine front brake pads — Land Cruiser', 450, 'toyota', 'Land Cruiser', 2020, '', array( 'cond' => 'new', 'part_type' => 'oem', 'brand' => 'Toyota', 'sku' => '04465-60320', 'featured' => 1 ), 'dubai', 'al-quoz' ),
		array( 'spare-parts', 'engine-parts', 'engine-parts-turbo', 'تيربو باترول نيسمو مستعمل', 'Used turbo for Patrol Nismo', 3800, 'nissan', 'Patrol', 2018, '', array( 'cond' => 'used', 'part_type' => 'oem', 'brand' => 'Garrett' ), 'sharjah', 'sharjah-city' ),
		array( 'spare-parts', 'wheels-tyres', 'wheels-tyres-all-terrain-tyres', '4 إطارات BFGoodrich KO2 مقاس 17', '4 × BFGoodrich KO2 17" tyres', 2600, '', '', '', '', array( 'cond' => 'new', 'part_type' => 'aftermarket', 'brand' => 'BFGoodrich' ), 'abu-dhabi', 'mussafah' ),
		array( 'spare-parts', 'electrical', 'electrical-alternator', 'دينمو مرسيدس C200 أصلي', 'Original alternator — Mercedes C200', 900, 'mercedes-benz', 'C-Class', 2017, '', array( 'cond' => 'used', 'part_type' => 'oem', 'brand' => 'Bosch', 'sku' => 'A2711540402' ), 'ajman', 'ajman-city' ),
		array( 'accessories', 'audio-screens', 'audio-screens-apple-carplay-screens', 'شاشة كاربلاي 12 إنش مع كاميرا خلفية', '12" CarPlay screen with rear camera', 1250, 'toyota', 'Prado', 2019, '', array( 'cond' => 'new', 'part_type' => 'aftermarket', 'featured' => 1 ), 'dubai', 'deira' ),
		array( 'accessories', 'offroad', 'offroad-roof-tents', 'خيمة سقف للرحلات تتسع لشخصين', '2-person roof tent', 3200, '', '', '', '', array( 'cond' => 'used', 'part_type' => 'aftermarket' ), 'ras-al-khaimah', 'rak-city' ),
		array( 'accessories', 'interior', 'interior-floor-mats', 'فرش أرضيات 5D مفصّلة', 'Tailored 5D floor mats', 280, 'hyundai', 'Tucson', 2022, '', array( 'cond' => 'new', 'part_type' => 'aftermarket' ), 'sharjah', 'sharjah-city' ),
		array( 'accessories', 'lighting', 'lighting-led-headlights', 'شمعات LED أمامية لتويوتا هايلكس', 'LED headlights for Toyota Hilux', 1900, 'toyota', 'Hilux', 2021, '', array( 'cond' => 'new', 'part_type' => 'aftermarket' ), 'fujairah', 'fujairah-city' ),
		array( 'tools', 'diagnostics', 'diagnostics-obd-scanners', 'جهاز فحص OBD احترافي لكل السيارات', 'Pro OBD scanner for all cars', 650, '', '', '', '', array( 'cond' => 'new', 'part_type' => 'aftermarket', 'brand' => 'Launch' ), 'dubai', 'al-quoz' ),
		array( 'tools', 'workshop', 'workshop-car-lifts', 'رافعة سيارات عمودين 4 طن', '2-post car lift, 4 t', 7800, '', '', '', '', array( 'cond' => 'used' ), 'abu-dhabi', 'mussafah' ),
		array( 'services', 'mechanic', '', 'ورشة ميكانيك وصيانة شاملة', 'Full mechanical repair workshop', '', '', '', '', '', array( 'featured' => 1 ), 'dubai', 'al-quoz' ),
		array( 'services', 'towing', '', 'سطحة 24 ساعة لكل الإمارات', '24/7 recovery truck — all UAE', '', '', '', '', '', array(), 'sharjah', 'sharjah-city' ),
		array( 'services', 'wash-polish', '', 'غسيل وتلميع متنقل في موقعك', 'Mobile wash & polish at your location', '', '', '', '', '', array(), 'abu-dhabi', 'abu-dhabi-city' ),
		array( 'services', 'tuning', '', 'تعديل وتجهيز سيارات الدفع الرباعي', '4x4 tuning & build-ups', '', '', '', '', '', array(), 'ras-al-khaimah', 'al-hamra' ),
	);
}
function qs_seed_demo() {
	qs_seed_terms();
	$uid = username_exists( 'demo_seller' );
	if ( ! $uid ) {
		$uid = wp_insert_user( array( 'user_login' => 'demo_seller', 'user_pass' => wp_generate_password( 20 ), 'user_email' => 'demo_seller@example.com', 'display_name' => 'معلن تجريبي', 'role' => 'subscriber' ) );
		update_user_meta( $uid, 'qs_phone', '971501234567' );
		update_user_meta( $uid, 'qs_demo', 1 );
	}
	$n = 0;
	foreach ( qs_demo_ads() as $i => $a ) {
		list( $sec, $sub, $it, $ar, $en, $price, $make, $model, $year, $km, $extra, $em, $city ) = $a;
		$time = time() - 300 - $i * 5 * HOUR_IN_SECONDS;
		$id   = wp_insert_post( array( 'post_type' => 'listing', 'post_status' => 'publish', 'post_author' => $uid, 'post_title' => $ar, 'post_content' => 'إعلان تجريبي لعرض شكل الموقع — احذفه قبل الإطلاق من أدوات ← FastSouq — البيانات.', 'post_date' => get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $time ) ) ) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		$n++;
		wp_set_object_terms( $id, array_filter( array( $sec, $sub, $it ) ), 'qs_cat' );
		$mm = array();
		if ( $make ) {
			$mm[] = $make;
			if ( $model ) {
				$mm[] = sanitize_title( qs_term( $make, 'qs_make' )->name . '-' . $model );
			}
		}
		wp_set_object_terms( $id, $mm, 'qs_make' );
		wp_set_object_terms( $id, array( $em, $city ), 'qs_city' );
		$m = array_merge(
			array( 'demo' => 1, 'group' => qs_group_of( $sec ), 'price' => $price, 'year' => $year, 'km' => $km, 'title_en' => $en, 'desc_en' => 'Demo ad to show the layout — delete it before launch (Tools → FastSouq — data).', 'phone' => '971501234567', 'views' => 30 + $i * 17, 'featured' => 0, 'bump' => $time, 'expires' => time() + 60 * DAY_IN_SECONDS, 'deal' => 'sale' ),
			$extra
		);
		if ( ! empty( $m['featured'] ) ) {
			$m['featured_until'] = time() + 7 * DAY_IN_SECONDS;
		}
		foreach ( $m as $k => $v ) {
			update_post_meta( $id, '_qs_' . $k, $v );
		}
	}
	return $n;
}
function qs_remove_demo() {
	foreach ( get_posts( array( 'post_type' => 'listing', 'post_status' => 'any', 'meta_key' => '_qs_demo', 'fields' => 'ids', 'posts_per_page' => -1 ) ) as $id ) {
		wp_delete_post( $id, true );
	}
	require_once ABSPATH . 'wp-admin/includes/user.php';
	foreach ( get_users( array( 'meta_key' => 'qs_demo', 'meta_value' => 1, 'fields' => 'ID' ) ) as $u ) {
		wp_delete_user( $u );
	}
}
add_action(
	'admin_menu',
	function () {
		add_management_page(
			'FastSouq data',
			'FastSouq — البيانات',
			'manage_options',
			'qs-demo',
			function () {
				$msg = '';
				if ( isset( $_POST['qs_demo'] ) && check_admin_referer( 'qs_demo' ) ) {
					if ( 'add' === $_POST['qs_demo'] ) {
						$msg = 'تمت إضافة ' . qs_seed_demo() . ' إعلان تجريبي.';
					} elseif ( 'remove' === $_POST['qs_demo'] ) {
						qs_remove_demo();
						$msg = 'تم حذف البيانات التجريبية.';
					} else {
						qs_seed_terms();
						qs_create_pages();
						flush_rewrite_rules();
						$msg = 'تم إنشاء الأقسام والماركات والإمارات والصفحات.';
					}
				}
				echo '<div class="wrap"><h1>FastSouq — البيانات</h1>' . ( $msg ? '<div class="notice notice-success"><p>' . esc_html( $msg ) . '</p></div>' : '' ) . '<p>الأقسام والماركات والموديلات والإمارات تُنشأ تلقائياً عند تفعيل القالب. يمكنك إضافة إعلانات تجريبية لعرض الموقع ثم حذفها قبل الإطلاق.</p><form method="post">';
				wp_nonce_field( 'qs_demo' );
				echo '<p><button class="button" name="qs_demo" value="terms">إعادة إنشاء التصنيفات والصفحات</button></p><p><button class="button button-primary" name="qs_demo" value="add">إضافة الإعلانات التجريبية</button> <button class="button button-link-delete" name="qs_demo" value="remove" onclick="return confirm(\'حذف البيانات التجريبية؟\')">حذف البيانات التجريبية</button></p></form></div>';
			}
		);
	}
);
