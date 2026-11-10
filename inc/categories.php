<?php
/**
 * Main categories (from the client's spreadsheet "dubizzle_uae_categories_ar.xlsx").
 * "cars" holds the original car sections (inc/data.php). Every other category uses the general ad fields.
 * main slug => [ "Arabic|English", icon, [ "sub-slug|Arabic|English" => [ "Arabic|English", … ] ] ]
 * Detail slugs are built as "<sub-slug>-<english>".
 */
defined( 'ABSPATH' ) || exit;

function qs_mains() {
	return array(
		'cars'                 => array( 'السيارات|Cars', 'car', array() ),
		'musical-instruments'  => array(
			'الآلات الموسيقية|Musical Instruments',
			'music',
			array(
				'instruments|الآلات|Instruments'                            => array( 'آلات نفخ|Wind instruments', 'بيانو وأورغ|Pianos & organs', 'جيتار|Guitars', 'طبول|Drums', 'عود|Oud', 'كمان|Violins' ),
				'audio-recording|معدات الصوت والتسجيل|Audio & recording gear' => array( 'معدات DJ|DJ equipment', 'معدات تسجيل|Recording equipment', 'مكبرات صوت|Amplifiers & speakers', 'ميكروفونات|Microphones', 'واجهات صوت|Audio interfaces' ),
			),
		),
		'home-garden'          => array(
			'الأثاث والمنزل والحديقة|Furniture, Home & Garden',
			'sofa',
			array(
				'furniture|الأثاث|Furniture'                               => array( 'أثاث أطفال|Kids furniture', 'أثاث خارجي|Outdoor furniture', 'أثاث مطبخ|Kitchen furniture', 'أثاث مكتبي|Office furniture', 'أسرّة|Beds', 'خزائن ودواليب|Wardrobes & cabinets', 'طاولات|Tables', 'غرف نوم|Bedroom sets', 'كراسي|Chairs', 'كنب وأرائك|Sofas & couches', 'مراتب|Mattresses' ),
				'tools-diy|الأدوات وتحسين المنزل|Tools & home improvement' => array( 'أدوات دهان|Painting tools', 'أدوات كهربائية|Power tools', 'عدد وأدوات|Hand tools', 'معدات صيانة منزلية|Home maintenance equipment', 'مواد وأدوات تركيب|Fixing materials & tools' ),
				'lighting-fans|الإضاءة والمراوح|Lighting & fans'           => array( 'إضاءة خارجية|Outdoor lighting', 'ثريات|Chandeliers', 'مراوح أرضية|Floor fans', 'مراوح سقف|Ceiling fans', 'مصابيح|Lamps' ),
				'garden-outdoor|الحدائق والمساحات الخارجية|Garden & outdoor' => array( 'أثاث حدائق|Garden furniture', 'أحواض ونوافير|Ponds & fountains', 'شوايات|BBQ grills', 'مسابح|Swimming pools', 'مظلات|Umbrellas & shades', 'معدات زراعة|Gardening equipment' ),
				'rugs-curtains|السجاد والستائر|Rugs & curtains'           => array( 'ستائر|Curtains', 'ستائر رول|Roller blinds', 'سجاد|Rugs', 'مفروشات|Home textiles', 'موكيت|Carpeting' ),
				'home-decor|ديكور وإكسسوارات المنزل|Home décor & accessories' => array( 'إكسسوارات ديكور|Décor accessories', 'تحف ومزهريات|Ornaments & vases', 'ساعات حائط|Wall clocks', 'شموع وحوامل|Candles & holders', 'لوحات فنية|Wall art', 'مرايا|Mirrors' ),
			),
		),
		'home-appliances'      => array(
			'الأجهزة المنزلية|Home Appliances',
			'appliance',
			array(
				'other-appliances|أجهزة أخرى|Other appliances'             => array( 'أجهزة عناية شخصية|Personal care appliances', 'أجهزة منزلية متنوعة|Miscellaneous appliances', 'سخانات مياه|Water heaters' ),
				'cleaning-appliances|أجهزة التنظيف|Cleaning appliances'    => array( 'أجهزة تنظيف بالبخار|Steam cleaners', 'روبوتات تنظيف|Robot cleaners', 'غسالات ضغط|Pressure washers', 'مكانس كهربائية|Vacuum cleaners' ),
				'small-kitchen|أجهزة المطبخ الصغيرة|Small kitchen appliances' => array( 'أجهزة طهي|Cooking appliances', 'خلاطات|Blenders', 'عصارات|Juicers', 'قلايات هوائية|Air fryers', 'ماكينات قهوة|Coffee machines', 'محامص|Toasters', 'ميكروويف|Microwaves' ),
				'large-appliances|الأجهزة الكبيرة|Large appliances'        => array( 'أفران|Ovens', 'ثلاجات|Refrigerators', 'جلايات صحون|Dishwashers', 'غسالات|Washing machines', 'فريزرات|Freezers', 'مواقد|Cookers', 'نشافات|Dryers' ),
				'cooling|التكييف والتبريد|Air conditioning & cooling'      => array( 'مبردات هواء|Air coolers', 'مراوح|Fans', 'مكيفات|Air conditioners', 'منقيات هواء|Air purifiers' ),
			),
		),
		'business-industrial'  => array(
			'الأعمال والمعدات الصناعية|Business & Industrial',
			'factory',
			array(
				'pro-tools-safety|الأدوات المهنية والسلامة|Professional tools & safety' => array( 'أدوات مهنية|Professional tools', 'معدات سلامة|Safety equipment', 'معدات قياس|Measuring equipment', 'ملابس عمل|Workwear' ),
				'businesses|الأعمال التجارية|Businesses'                   => array( 'امتياز تجاري|Franchises', 'شركات للبيع|Businesses for sale', 'مستلزمات أعمال|Business supplies', 'معدات مكاتب|Office equipment' ),
				'construction-equipment|معدات البناء|Construction equipment' => array( 'رافعات|Cranes', 'سقالات|Scaffolding', 'معدات حفر|Excavation equipment', 'معدات خلط وصب|Mixing & concrete equipment', 'معدات طرق|Road equipment' ),
				'factory-warehouse|معدات المصانع والمستودعات|Factory & warehouse equipment' => array( 'رافعات شوكية|Forklifts', 'رفوف مستودعات|Warehouse racking', 'معدات تعبئة وتغليف|Packaging equipment', 'معدات صناعية|Industrial machinery', 'مولدات كهربائية|Generators' ),
				'restaurant-equipment|معدات المطاعم والمطابخ|Restaurant & kitchen equipment' => array( 'أفران تجارية|Commercial ovens', 'ثلاجات تجارية|Commercial refrigerators', 'معدات القهوة|Coffee equipment', 'معدات تحضير الطعام|Food preparation equipment', 'معدات غسيل|Dishwashing equipment' ),
			),
		),
		'toys-gaming'          => array(
			'الألعاب والترفيه|Toys & Gaming',
			'gamepad',
			array(
				'consoles|أجهزة الألعاب|Gaming consoles'                   => array( 'Nintendo|Nintendo', 'PlayStation|PlayStation', 'Xbox|Xbox', 'أجهزة ألعاب محمولة|Handheld consoles' ),
				'kids-toys|ألعاب الأطفال|Kids toys'                        => array( 'ألعاب تركيب|Building toys', 'ألعاب تعليمية|Educational toys', 'ألعاب جماعية|Board & party games', 'دمى ومجسمات|Dolls & figures' ),
				'video-games|ألعاب الفيديو وملحقاتها|Video games & accessories' => array( 'أذرع تحكم|Controllers', 'ألعاب فيديو|Video games', 'سماعات Gaming|Gaming headsets', 'كراسي Gaming|Gaming chairs', 'ملحقات ألعاب|Gaming accessories' ),
			),
		),
		'electronics'          => array(
			'الإلكترونيات|Electronics',
			'tv',
			array(
				'smart-security|الأجهزة الذكية والأمن|Smart home & security' => array( 'أجهزة شبكات منزلية|Home networking devices', 'أجهزة منزل ذكي|Smart home devices', 'أنظمة إنذار|Alarm systems', 'كاميرات مراقبة|Security cameras' ),
				'tvs-displays|التلفزيونات والشاشات|TVs & displays'         => array( 'أجهزة عرض Projectors|Projectors', 'تلفزيونات ذكية|Smart TVs', 'حوامل تلفزيون|TV mounts', 'شاشات عرض|Display screens' ),
				'audio-home-theater|الصوت والترفيه المنزلي|Audio & home entertainment' => array( 'أجهزة استقبال|Receivers', 'أنظمة صوت|Sound systems', 'سماعات|Headphones', 'مسرح منزلي|Home theater', 'مكبرات صوت|Speakers', 'ميكروفونات|Microphones' ),
			),
		),
		'tickets-vouchers'     => array(
			'التذاكر والقسائم|Tickets & Vouchers',
			'ticket',
			array(
				'tickets|التذاكر|Tickets'                                  => array( 'تذاكر ترفيه|Entertainment tickets', 'تذاكر حفلات|Concert tickets', 'تذاكر فعاليات|Event tickets', 'تذاكر مباريات|Sports tickets' ),
				'vouchers|القسائم والعروض|Vouchers & offers'               => array( 'قسائم تسوق|Shopping vouchers', 'قسائم خصومات|Discount vouchers', 'قسائم فنادق|Hotel vouchers', 'قسائم مطاعم|Restaurant vouchers' ),
			),
		),
		'sports-fitness'       => array(
			'الرياضة واللياقة|Sports & Fitness',
			'dumbbell',
			array(
				'gym-equipment|أجهزة ومعدات الجيم|Gym equipment'           => array( 'أجهزة مشي|Treadmills', 'أجهزة مقاومة|Strength machines', 'أوزان ودمبل|Weights & dumbbells', 'دراجات رياضية|Exercise bikes', 'معدات لياقة|Fitness equipment', 'مقاعد تمارين|Workout benches' ),
				'camping-outdoor|التخييم والأنشطة الخارجية|Camping & outdoor' => array( 'حقائب تخييم|Camping bags', 'خيام|Tents', 'صيد الأسماك|Fishing', 'معدات رحلات|Trip gear', 'معدات رياضات مائية|Water sports gear', 'معدات مغامرات|Adventure gear' ),
				'team-sports|الرياضات الجماعية|Team sports'                => array( 'التنس|Tennis', 'الريشة الطائرة|Badminton', 'كرة السلة|Basketball', 'كرة الطائرة|Volleyball', 'كرة القدم|Football' ),
				'individual-sports|الرياضات الفردية|Individual sports'     => array( 'الجري|Running', 'الدراجات الهوائية|Bicycles', 'السباحة|Swimming', 'الفنون القتالية|Martial arts', 'الملاكمة|Boxing', 'اليوغا|Yoga' ),
			),
		),
		'real-estate'          => array(
			'العقارات|Real Estate',
			'building',
			array(
				're-services|خدمات ومشاركة سكن|Services & shared housing' => array( 'إدارة العقارات|Property management', 'خدمات عقارية|Real estate services', 'مشاركة السكن|Shared housing', 'مشاريع عقارية جديدة|New projects' ),
				'commercial-property|عقارات تجارية|Commercial property'    => array( 'أراضٍ تجارية|Commercial land', 'مبانٍ استثمارية|Investment buildings', 'محلات|Shops', 'مستودعات|Warehouses', 'مصانع|Factories', 'مكاتب تجارية|Commercial offices' ),
				'property-for-rent|عقارات للإيجار|Property for rent'       => array( 'استوديو|Studios', 'بيوت عطلات|Holiday homes', 'شقق|Apartments', 'غرف مستقلة|Private rooms', 'غرف مشتركة|Shared rooms', 'فلل|Villas', 'محلات تجارية|Retail shops', 'مساحات تجارية|Commercial spaces', 'مستودعات|Warehouses', 'مكاتب|Offices' ),
				'property-for-sale|عقارات للبيع|Property for sale'         => array( 'أراضٍ تجارية|Commercial land', 'أراضٍ سكنية|Residential land', 'استوديو|Studios', 'تاون هاوس|Townhouses', 'شقق سكنية|Apartments', 'عقارات قيد الإنشاء|Off-plan property', 'فلل ومنازل|Villas & houses', 'مبانٍ كاملة|Whole buildings', 'محلات تجارية|Retail shops', 'مستودعات|Warehouses', 'مكاتب|Offices' ),
			),
		),
		'cameras-photography'  => array(
			'الكاميرات والتصوير|Cameras & Photography',
			'camera',
			array(
				'cameras|الكاميرات|Cameras'                                => array( 'طائرات درون|Drones', 'كاميرات DSLR|DSLR cameras', 'كاميرات Mirrorless|Mirrorless cameras', 'كاميرات فورية|Instant cameras', 'كاميرات فيديو|Video cameras', 'كاميرات مراقبة|Security cameras' ),
				'photo-gear|معدات التصوير|Photography gear'                => array( 'إضاءة تصوير|Photo lighting', 'حقائب تصوير|Camera bags', 'حوامل ثلاثية|Tripods', 'عدسات|Lenses', 'فلاشات|Flashes', 'معدات استوديو|Studio equipment', 'ملحقات تصوير|Photo accessories' ),
			),
		),
		'books-education'      => array(
			'الكتب والتعليم|Books & Education',
			'book',
			array(
				'stationery|القرطاسية ومستلزمات الدراسة|Stationery & study supplies' => array( 'أدوات مكتبية|Office supplies', 'حقائب مدرسية|School bags', 'دفاتر وأقلام|Notebooks & pens', 'مستلزمات دراسة|Study supplies' ),
				'books|الكتب|Books'                                        => array( 'روايات وقصص|Novels & stories', 'كتب إنجليزية|English books', 'كتب جامعية|University books', 'كتب عربية|Arabic books', 'كتب علمية|Science books', 'كتب مدرسية|School books', 'مجلات وموسوعات|Magazines & encyclopedias' ),
			),
		),
		'computers-networking' => array(
			'الكمبيوتر والشبكات|Computers & Networking',
			'laptop',
			array(
				'computers|أجهزة الكمبيوتر|Computers'                      => array( 'أجهزة All-in-One|All-in-One PCs', 'أجهزة Workstation|Workstations', 'سيرفرات|Servers', 'كمبيوتر مكتبي|Desktop PCs', 'لابتوبات|Laptops' ),
				'monitors-peripherals|الشاشات والملحقات|Monitors & peripherals' => array( 'سماعات رأس|Headsets', 'شاشات كمبيوتر|Monitors', 'فأرات|Mice', 'قواعد وحوامل|Stands & mounts', 'كاميرات ويب|Webcams', 'لوحات مفاتيح|Keyboards' ),
				'networking|الشبكات والاتصالات|Networking & communications' => array( 'Access Points|Access points', 'راوترات|Routers', 'سويتشات|Switches', 'كابلات شبكات|Network cables', 'معدات Rack|Rack equipment', 'معدات اتصالات|Telecom equipment', 'مودمات|Modems' ),
				'printers-scanners|الطابعات والماسحات|Printers & scanners' => array( 'أحبار وتونر|Ink & toner', 'طابعات حبر|Inkjet printers', 'طابعات ليزر|Laser printers', 'طابعات متعددة الوظائف|Multifunction printers', 'ماسحات ضوئية|Scanners' ),
				'pc-components|مكونات الكمبيوتر|Computer components'       => array( 'أقراص HDD|Hard drives (HDD)', 'أنظمة تبريد|Cooling systems', 'ذاكرة RAM|Memory (RAM)', 'كروت شاشة GPU|Graphics cards (GPU)', 'كيسات الكمبيوتر|PC cases', 'لوحات أم|Motherboards', 'مزودات طاقة|Power supplies', 'معالجات CPU|Processors (CPU)', 'وحدات تخزين SSD|SSD storage' ),
			),
		),
		'jewelry-watches'      => array(
			'المجوهرات والساعات|Jewelry & Watches',
			'diamond',
			array(
				'watches|الساعات|Watches'                                  => array( 'إكسسوارات ساعات|Watch accessories', 'ساعات ذكية|Smart watches', 'ساعات رجالية|Men\'s watches', 'ساعات فاخرة|Luxury watches', 'ساعات نسائية|Women\'s watches' ),
				'jewelry|المجوهرات|Jewelry'                                => array( 'أحجار كريمة|Gemstones', 'أساور|Bracelets', 'أقراط|Earrings', 'ألماس|Diamonds', 'خواتم|Rings', 'ذهب|Gold', 'سلاسل وقلائد|Chains & necklaces', 'فضة|Silver' ),
			),
		),
		'collectibles-hobbies' => array(
			'المقتنيات والهوايات|Collectibles & Hobbies',
			'trophy',
			array(
				'collectibles|المقتنيات|Collectibles'                      => array( 'تحف نادرة|Rare antiques', 'طوابع|Stamps', 'عملات|Coins', 'مجسمات|Models & figurines', 'مقتنيات تذكارية|Memorabilia' ),
				'music-movies|الموسيقى والأفلام|Music & movies'            => array( 'أسطوانات|Vinyl records', 'أفلام DVD|DVD movies', 'أقراص موسيقى|Music CDs' ),
			),
		),
		'clothing-accessories' => array(
			'الملابس والإكسسوارات|Clothing & Accessories',
			'shirt',
			array(
				'personal-care|إكسسوارات وعناية شخصية|Accessories & personal care' => array( 'أحزمة وربطات|Belts & ties', 'إكسسوارات موضة|Fashion accessories', 'عطور|Perfumes', 'مستحضرات تجميل|Cosmetics', 'نظارات شمسية|Sunglasses' ),
				'shoes|الأحذية|Shoes'                                      => array( 'أحذية أطفال|Kids shoes', 'أحذية رجالية|Men\'s shoes', 'أحذية رياضية|Sports shoes', 'أحذية نسائية|Women\'s shoes' ),
				'bags-wallets|الحقائب والمحافظ|Bags & wallets'             => array( 'حقائب سفر|Luggage', 'حقائب ظهر|Backpacks', 'حقائب يد|Handbags', 'محافظ|Wallets' ),
				'clothing|الملابس|Clothing'                                => array( 'ملابس أطفال|Kids clothing', 'ملابس تقليدية|Traditional wear', 'ملابس رجالية|Men\'s clothing', 'ملابس رسمية|Formal wear', 'ملابس رياضية|Sportswear', 'ملابس نسائية|Women\'s clothing' ),
			),
		),
		'phones-tablets'       => array(
			'الهواتف والأجهزة اللوحية|Mobile Phones & Tablets',
			'mobile',
			array(
				'phone-accessories|إكسسوارات الهواتف|Phone accessories'    => array( 'أغطية وحافظات|Cases & covers', 'بطاريات|Batteries & power banks', 'حوامل هواتف|Phone holders', 'ساعات ذكية|Smart watches', 'سماعات|Headphones & earbuds', 'شواحن وكابلات|Chargers & cables', 'واقيات شاشة|Screen protectors' ),
				'tablets|الأجهزة اللوحية|Tablets'                          => array( 'Apple iPad|Apple iPad', 'Huawei tablets|Huawei tablets', 'Samsung Galaxy Tab|Samsung Galaxy Tab', 'أجهزة لوحية أخرى|Other tablets' ),
				'smartphones|الهواتف الذكية|Smartphones'                   => array( 'Apple iPhone|Apple iPhone', 'Google Pixel|Google Pixel', 'Huawei|Huawei', 'OnePlus|OnePlus', 'Oppo|Oppo', 'Samsung|Samsung', 'Xiaomi|Xiaomi', 'هواتف علامات أخرى|Other brands' ),
			),
		),
		'jobs'                 => array(
			'الوظائف|Jobs',
			'briefcase',
			array(
				'admin-business|الإدارة والأعمال|Administration & business' => array( 'الإدارة|Management', 'السكرتارية|Secretarial', 'الموارد البشرية|Human resources', 'خدمة العملاء|Customer service' ),
				'education-health|التعليم والصحة|Education & health'       => array( 'التعليم والتدريب|Teaching & training', 'التمريض|Nursing', 'الصيدلة|Pharmacy', 'الطب والرعاية الصحية|Medical & healthcare' ),
				'tech-engineering|التقنية والهندسة|Technology & engineering' => array( 'الدعم الفني|Technical support', 'الهندسة الكهربائية|Electrical engineering', 'الهندسة المدنية|Civil engineering', 'الهندسة الميكانيكية|Mechanical engineering', 'تكنولوجيا المعلومات|Information technology' ),
				'hospitality-services|الضيافة والخدمات|Hospitality & services' => array( 'التجميل والصالونات|Beauty & salons', 'السائقون والتوصيل|Drivers & delivery', 'الضيافة والسياحة|Hospitality & tourism', 'المطاعم والطبخ|Restaurants & cooking' ),
				'finance-accounting|المالية والمحاسبة|Finance & accounting' => array( 'التدقيق|Auditing', 'الخدمات المصرفية|Banking', 'المالية|Finance', 'المحاسبة|Accounting' ),
				'sales-marketing|المبيعات والتسويق|Sales & marketing'      => array( 'الإعلان والعلاقات العامة|Advertising & PR', 'التسويق|Marketing', 'المبيعات|Sales', 'تطوير الأعمال|Business development' ),
				'other-jobs|المهن والقطاعات الأخرى|Other professions'      => array( 'الأمن والحراسة|Security & guarding', 'الإعلام والترفيه|Media & entertainment', 'البناء والإنشاءات|Construction', 'التصميم والإبداع|Design & creative', 'التصنيع والمستودعات|Manufacturing & warehousing', 'الخدمات اللوجستية|Logistics', 'العقارات|Real estate' ),
			),
		),
	);
}

/** Menu places of the main categories by default (1–6). The rest go under "More". Editable per category in the dashboard. */
function qs_default_menu() {
	return array( 'cars' => 1, 'real-estate' => 2, 'jobs' => 3, 'phones-tablets' => 4, 'electronics' => 5, 'home-garden' => 6 );
}
