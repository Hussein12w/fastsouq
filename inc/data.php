<?php
/**
 * Classification from the client's spreadsheet: sections → sub-sections → item types,
 * vehicle makes (by origin) with popular models, emirates → cities, and option lists.
 * Strings are "Arabic|English". The dashboard can add / rename anything later.
 */
defined( 'ABSPATH' ) || exit;

/* Main sections. 'g' = field group used by the post form and filters. */
function qs_sections() {
	return array(
		'vehicles'    => array( 'السيارات والمركبات|Cars & Vehicles', 'car', 'vehicle' ),
		'spare-parts' => array( 'قطع غيار السيارات|Spare Parts', 'wrench', 'item' ),
		'accessories' => array( 'إكسسوارات السيارات|Car Accessories', 'star', 'item' ),
		'tools'       => array( 'معدات وأدوات السيارات|Tools & Equipment', 'box', 'item' ),
		'services'    => array( 'خدمات السيارات|Car Services', 'shield', 'service' ),
	);
}

/* Sub-sections (and their item types, when the spreadsheet lists them). */
function qs_tree() {
	return array(
		'vehicles'    => array(
			'cars-for-sale|سيارات للبيع|Cars for sale'         => array(),
			'cars-for-rent|سيارات للإيجار|Cars for rent'       => array(),
			'motorcycles|دراجات نارية|Motorcycles'             => array(),
			'scooters|سكوترات|Scooters'                        => array(),
			'atv-utv|مركبات ATV / Quad / UTV / Buggy|ATV / Quad / UTV / Buggy' => array(),
			'trucks|شاحنات|Trucks'                             => array(),
			'buses|حافلات|Buses'                               => array(),
			'heavy-equipment|معدات ثقيلة|Heavy equipment'      => array(),
			'boats|قوارب ويخوت|Boats & yachts'                 => array(),
			'jet-ski|جت سكي|Jet ski'                           => array(),
			'trailers|مقطورات وكرفانات|Trailers & caravans'    => array(),
			'number-plates|لوحات أرقام|Number plates'          => array(),
		),
		'spare-parts' => array(
			'engine-parts|المحرك وملحقاته|Engine & parts' => array( 'محرك كامل|Complete engine', 'بلوك المحرك|Engine block', 'رأس المحرك Cylinder Head|Cylinder head', 'بساتم|Pistons', 'كرنك|Crankshaft', 'كام شفت|Camshaft', 'صمامات|Valves', 'جنزير تايمن|Timing chain', 'سير تايمن|Timing belt', 'طرمبة زيت|Oil pump', 'طرمبة وقود|Fuel pump', 'فلتر زيت|Oil filter', 'فلتر هواء|Air filter', 'فلتر وقود|Fuel filter', 'بواجي|Spark plugs', 'كويلات|Ignition coils', 'حساس أكسجين|Oxygen sensor', 'حساس هواء MAF|MAF sensor', 'حساس كرنك|Crankshaft sensor', 'حساس كام|Camshaft sensor', 'قواعد محرك|Engine mounts', 'تيربو|Turbo', 'سوبرتشارجر|Supercharger' ),
			'transmission|ناقل الحركة ونظام الدفع|Transmission & drivetrain' => array( 'جيربوكس كامل|Complete gearbox', 'قطع جيربوكس|Gearbox parts', 'قير أوتوماتيك|Automatic transmission', 'قير عادي|Manual transmission', 'كلتش|Clutch', 'ديسك وصحن|Clutch disc & plate', 'فحمة كلتش|Clutch bearing', 'دفرنس|Differential', 'عمود كردان|Propeller shaft', 'عكوس CV Axles|CV axles', 'رأس عكس|CV joint', 'ترانسفير Transfer Case|Transfer case', 'قواعد جير|Gearbox mounts', 'فلتر زيت الجير|Transmission filter' ),
			'suspension-steering|التعليق والتوجيه|Suspension & steering' => array( 'مساعدات|Shock absorbers', 'يايات|Springs', 'مقصات|Control arms', 'أذرعة دركسون|Tie rods', 'علبة دركسون|Steering rack', 'طرمبة دركسون|Power steering pump', 'أذرعة توازن|Stabilizer links', 'جلد مقصات|Control arm bushings', 'رمان بلي|Wheel bearings', 'قواعد مساعدات|Strut mounts', 'نظام تعليق هوائي|Air suspension' ),
			'brakes|الفرامل|Brakes' => array( 'فحمات فرامل|Brake pads', 'هوبات فرامل|Brake discs', 'كليبرات|Calipers', 'ماستر بريك|Brake master cylinder', 'بوستر بريك|Brake booster', 'مضخة ABS|ABS pump', 'حساسات ABS|ABS sensors', 'ليات فرامل|Brake hoses', 'فرامل يد|Hand brake', 'قطع فرامل إلكترونية|Electronic brake parts' ),
			'body-parts|الهيكل والقطع الخارجية|Body & exterior parts' => array( 'صدام أمامي|Front bumper', 'صدام خلفي|Rear bumper', 'كبوت|Bonnet', 'رفارف|Fenders', 'أبواب|Doors', 'شنطة خلفية|Tailgate / boot lid', 'شبك أمامي|Front grille', 'مرايات جانبية|Side mirrors', 'مقابض أبواب|Door handles', 'دعاسات جانبية|Side steps', 'عتبات أبواب|Door sills', 'سقف السيارة|Roof', 'شاصي|Chassis', 'قواعد صدام|Bumper brackets', 'جناح خلفي|Rear spoiler', 'زجاج أمامي|Windscreen', 'زجاج خلفي|Rear glass', 'زجاج أبواب|Door glass' ),
			'electrical|الكهرباء والإلكترونيات|Electrical & electronics' => array( 'دينمو|Alternator', 'سلف|Starter motor', 'بطاريات|Batteries', 'كمبيوتر ECU|ECU', 'كمبيوتر قير TCU|TCU', 'ضفيرة كهرباء|Wiring harness', 'فيوزات|Fuses', 'ريليهات|Relays', 'حساسات|Sensors', 'شاشات أصلية|OEM screens', 'وحدة BCM|BCM unit', 'وحدة Airbag|Airbag unit', 'وحدات تحكم الإضاءة|Lighting control units', 'كاميرات أصلية|OEM cameras', 'وحدات GPS|GPS units' ),
			'cooling-ac|التبريد والتكييف|Cooling & A/C' => array( 'رديتر|Radiator', 'مروحة تبريد|Cooling fan', 'طرمبة ماء|Water pump', 'ثرموستات|Thermostat', 'قربة ماء|Coolant reservoir', 'ليات رديتر|Radiator hoses', 'كمبروسر مكيف|A/C compressor', 'كوندنسر|Condenser', 'مبخر مكيف|Evaporator', 'فلتر مكيف|Cabin filter', 'رديتر تدفئة|Heater core', 'مراوح مكيف|A/C blowers' ),
			'fuel-exhaust|الوقود والعادم|Fuel & exhaust' => array( 'تانكي وقود|Fuel tank', 'طرمبة بنزين|Petrol pump', 'بخاخات|Injectors', 'فلتر وقود|Fuel filter', 'ثروتل بدي|Throttle body', 'مجمع عادم|Exhaust manifold', 'دبة تلوث Catalytic Converter|Catalytic converter', 'شكمان|Muffler', 'علبة البيئة|DPF', 'حساسات عادم|Exhaust sensors', 'خطوط وقود|Fuel lines' ),
			'wheels-tyres|الجنوط والإطارات|Wheels & tyres' => array( 'إطارات جديدة|New tyres', 'إطارات مستعملة|Used tyres', 'جنوط ألمنيوم|Alloy rims', 'جنوط رياضية|Sport rims', 'جنوط أوف رود|Off-road rims', 'جنوط حديد|Steel rims', 'إطارات رملية|Sand tyres', 'إطارات All Terrain|All-terrain tyres', 'إطارات Mud Terrain|Mud-terrain tyres', 'إطارات Run Flat|Run-flat tyres', 'إطارات احتياطية|Spare tyres', 'أغطية جنوط|Hub caps', 'صواميل جنوط|Wheel nuts', 'حساسات TPMS|TPMS sensors' ),
		),
		'accessories' => array(
			'interior|إكسسوارات داخلية|Interior accessories' => array( 'فرش أرضيات|Floor mats', 'فرش جلد للمقاعد|Leather seat upholstery', 'أغطية مقاعد|Seat covers', 'مساند يد|Armrests', 'مساند رأس|Headrests', 'أغطية دركسون|Steering wheel covers', 'دواسات رياضية|Sport pedals', 'ديكورات داخلية|Interior trims', 'إضاءة داخلية LED|LED interior lights', 'إضاءة Ambient Light|Ambient lighting', 'حامل جوال|Phone holders', 'حامل أكواب|Cup holders', 'منظمات تخزين|Organizers', 'ستائر داخلية|Sun shades', 'شماعات ملابس|Clothes hangers', 'معطرات سيارات|Air fresheners', 'شواحن USB|USB chargers', 'شواحن لاسلكية|Wireless chargers', 'مرايا داخلية إضافية|Extra interior mirrors', 'أغطية لوحة قيادة|Dashboard covers' ),
			'exterior|إكسسوارات خارجية|Exterior accessories' => array( 'شبك أمامي رياضي|Sport grilles', 'صدامات معدلة|Custom bumpers', 'جناح خلفي|Rear spoilers', 'دفيوزر|Diffusers', 'عتبات جانبية|Side skirts', 'حماية أبواب|Door guards', 'حماية مقابض أبواب|Door handle protectors', 'واقيات طين Mud Flaps|Mud flaps', 'حماية كبوت|Bonnet protectors', 'حماية رفارف|Fender flares', 'مظلات سيارات|Car awnings', 'أغطية سيارات|Car covers', 'ملصقات سيارات|Car stickers', 'تظليل زجاج|Window tint', 'حامل سقف Roof Rack|Roof racks', 'صندوق سقف|Roof boxes', 'حامل دراجات|Bike carriers', 'حامل إطار احتياطي|Spare tyre carriers', 'كشافات إضافية|Auxiliary lights' ),
			'audio-screens|الصوتيات والشاشات|Audio & screens' => array( 'شاشات Android|Android screens', 'شاشات Apple CarPlay|Apple CarPlay screens', 'شاشات Android Auto|Android Auto screens', 'شاشات أصلية OEM|OEM screens', 'مسجلات سيارات|Car stereos', 'سماعات|Speakers', 'Subwoofers|Subwoofers', 'مضخمات صوت Amplifiers|Amplifiers', 'كاميرات خلفية|Rear cameras', 'كاميرات 360 درجة|360° cameras', 'كاميرات أمامية|Front cameras', 'Dash Cam|Dash cams', 'أجهزة GPS|GPS devices', 'أجهزة Bluetooth|Bluetooth devices', 'محولات CarPlay|CarPlay adapters', 'أجهزة راديو|Radios' ),
			'lighting|إضاءة السيارات|Car lighting' => array( 'شمعات أمامية|Headlights', 'شمعات خلفية|Tail lights', 'LED Headlights|LED headlights', 'Xenon Lights|Xenon lights', 'Fog Lights|Fog lights', 'DRL|DRL', 'لمبات داخلية|Interior bulbs', 'إضاءة أرضية|Underglow', 'إضاءة أبواب|Door lights', 'إضاءة شعار السيارة|Logo lights', 'كشافات أوف رود|Off-road lights', 'Light Bars|Light bars', 'لمبات إشارات|Indicator bulbs' ),
			'offroad|الدفع الرباعي والرحلات|4x4 & trips' => array( 'ونش Winch|Winches', 'سنوركل Snorkel|Snorkels', 'صدامات حديد|Steel bumpers', 'حمايات سفلية Skid Plates|Skid plates', 'Lift Kit|Lift kits', 'مساعدات أوف رود|Off-road shocks', 'يايات رفع|Lift springs', 'كشافات LED|LED lights', 'سلال سقف|Roof baskets', 'خيمة سقف Roof Tent|Roof tents', 'مظلات رحلات|Trip awnings', 'أدراج خلفية|Drawer systems', 'ثلاجات سيارات|Car fridges', 'خزانات ماء|Water tanks', 'خزانات وقود إضافية|Extra fuel tanks', 'حبال سحب|Tow ropes', 'ألواح إنقاذ الرمال|Recovery boards', 'مجارف|Shovels', 'كمبروسر هواء|Air compressors', 'حامل إطارات|Tyre carriers', 'أجهزة لاسلكية|Radios / walkie-talkies', 'معدات تخييم|Camping gear' ),
			'safety|السلامة والطوارئ|Safety & emergency' => array( 'طفايات حريق|Fire extinguishers', 'حقيبة إسعافات أولية|First-aid kits', 'مثلث تحذير|Warning triangles', 'كاميرات مراقبة|Security cameras', 'حساسات ركن|Parking sensors', 'أجهزة إنذار|Alarms', 'أقفال مقود|Steering locks', 'أقفال إطارات|Wheel locks', 'أحزمة أمان|Seat belts', 'كراسي أطفال|Child seats', 'أجهزة تتبع GPS|GPS trackers', 'أجهزة مراقبة ضغط الإطارات|Tyre pressure monitors', 'Jump Starter|Jump starters' ),
			'care-cleaning|العناية والتنظيف|Care & cleaning' => array( 'شامبو غسيل|Car shampoo', 'واكس|Wax', 'ملمع طلاء|Paint polish', 'ملمع إطارات|Tyre shine', 'منظف داخلي|Interior cleaner', 'منظف جلد|Leather cleaner', 'منظف زجاج|Glass cleaner', 'فوط مايكروفايبر|Microfibre towels', 'فرش تنظيف|Cleaning brushes', 'مكانس كهربائية|Vacuum cleaners', 'أجهزة تلميع|Polishers', 'أجهزة بخار|Steam cleaners', 'أجهزة غسيل ضغط|Pressure washers', 'معطرات|Fresheners', 'حماية نانو سيراميك|Nano-ceramic coating', 'أفلام حماية PPF|PPF films', 'مواد إزالة خدوش|Scratch removers' ),
			'oils-batteries|الزيوت والسوائل والبطاريات|Oils, fluids & batteries' => array( 'زيت محرك|Engine oil', 'زيت جير|Gear oil', 'زيت دفرنس|Differential oil', 'زيت فرامل|Brake fluid', 'سائل تبريد|Coolant', 'ماء رديتر|Radiator water', 'سائل تنظيف زجاج|Washer fluid', 'إضافات وقود|Fuel additives', 'بطاريات سيارات|Car batteries', 'شواحن بطاريات|Battery chargers', 'أجهزة فحص بطاريات|Battery testers' ),
		),
		'tools'       => array(
			'diagnostics|أجهزة الفحص والبرمجة|Diagnostics & programming' => array( 'أجهزة فحص OBD|OBD scanners', 'أجهزة برمجة سيارات|Programming tools', 'أجهزة فحص بطاريات|Battery testers', 'أجهزة قياس ضغط المحرك|Compression testers' ),
			'workshop|معدات الورش|Workshop equipment' => array( 'رافعات سيارات|Car lifts', 'كراكات|Jacks', 'مفاتيح صواميل|Wrenches', 'أدوات فك وتركيب|Hand tools', 'كمبروسرات هواء|Air compressors', 'معدات سمكرة|Body repair tools', 'معدات دهان|Painting equipment', 'أجهزة ترصيص إطارات|Wheel balancers', 'أجهزة تغيير إطارات|Tyre changers' ),
		),
		'services'    => array(
			'maintenance|صيانة وإصلاح|Maintenance & repair' => array(), 'mechanic|ميكانيك|Mechanic' => array(), 'auto-electric|كهرباء سيارات|Auto electrician' => array(),
			'body-paint|سمكرة ودهان|Body & paint' => array(), 'tyre-change|تبديل إطارات|Tyre change' => array(), 'wash-polish|غسيل وتلميع|Wash & polish' => array(),
			'inspection|فحص سيارات|Car inspection' => array(), 'towing|سحب وإنقاذ|Towing & recovery' => array(), 'programming|برمجة سيارات|Car programming' => array(),
			'tuning|تعديل وتجهيز سيارات|Tuning & modification' => array(), 'car-ac|تكييف سيارات|Car A/C' => array(), 'accessory-fitting|تركيب إكسسوارات|Accessory fitting' => array(),
		),
	);
}

/* Vehicle "type" options per sub-section (sedan, SUV… / sport, touring… ). */
function qs_body_options() {
	$cars = array( 'sedan|سيدان|Sedan', 'suv|SUV|SUV', 'crossover|كروس أوفر|Crossover', '4x4|دفع رباعي 4x4|4x4', 'hatchback|هاتشباك|Hatchback', 'coupe|كوبيه|Coupe', 'convertible|مكشوفة|Convertible', 'pickup|بيك أب|Pickup', 'van|فان|Van', 'minivan|ميني فان|Minivan', 'sports|رياضية|Sports', 'luxury|فاخرة|Luxury', 'electric|كهربائية|Electric', 'hybrid|هايبرد|Hybrid', 'classic|كلاسيكية|Classic', 'modified|معدلة|Modified', 'commercial|تجارية|Commercial' );
	$moto = array( 'sport|Sport|Sport', 'touring|Touring|Touring', 'cruiser|Cruiser|Cruiser', 'naked|Naked|Naked', 'dirt|Dirt Bike|Dirt bike', 'adventure|Adventure|Adventure', 'scooter|Scooter|Scooter' );
	return array(
		'cars-for-sale'   => $cars,
		'cars-for-rent'   => $cars,
		'motorcycles'     => $moto,
		'scooters'        => $moto,
		'trucks'          => array( 'light|خفيفة|Light', 'medium|متوسطة|Medium', 'heavy|ثقيلة|Heavy', 'tipper|قلّاب|Tipper', 'chiller|براد|Chiller', 'refrigerated|مبردة|Refrigerated', 'flatbed|سطحة|Flatbed' ),
		'heavy-equipment' => array( 'excavators|حفارات|Excavators', 'bulldozers|جرافات|Bulldozers', 'cranes|رافعات|Cranes', 'forklifts|فوركلفت|Forklifts', 'loaders|لودر|Loaders', 'road-equipment|معدات طرق|Road equipment', 'generators|مولدات|Generators' ),
		'boats'           => array( 'fishing|قارب صيد|Fishing boat', 'yacht|يخت|Yacht', 'leisure|قارب ترفيهي|Leisure boat', 'inflatable|قارب مطاطي|Inflatable boat' ),
	);
}

/* Simple option lists: key => "Arabic|English". */
function qs_options( $list ) {
	$o = array(
		'cond'      => array( 'new' => 'جديد|New', 'used' => 'مستعمل|Used', 'damaged' => 'متضرر / حوادث|Damaged / accident', 'scrap' => 'سكراب|Scrap' ),
		'item_cond' => array( 'new' => 'جديد|New', 'used' => 'مستعمل|Used' ),
		'gear'      => array( 'auto' => 'أوتوماتيك|Automatic', 'manual' => 'عادي|Manual' ),
		'fuel'      => array( 'petrol' => 'بنزين|Petrol', 'diesel' => 'ديزل|Diesel', 'electric' => 'كهرباء|Electric', 'hybrid' => 'هايبرد|Hybrid' ),
		'cyl'       => array( '3' => '3|3', '4' => '4|4', '5' => '5|5', '6' => '6|6', '8' => '8|8', '10' => '10|10', '12' => '12|12', '0' => 'كهربائي|Electric' ),
		'specs'     => array( 'gcc' => 'خليجي GCC|GCC', 'american' => 'أمريكي|American', 'japanese' => 'ياباني|Japanese', 'korean' => 'كوري|Korean', 'european' => 'أوروبي|European', 'chinese' => 'صيني|Chinese', 'other' => 'مستورد آخر|Other import' ),
		'color'     => array( 'white' => 'أبيض|White', 'black' => 'أسود|Black', 'silver' => 'فضي|Silver', 'grey' => 'رمادي|Grey', 'red' => 'أحمر|Red', 'blue' => 'أزرق|Blue', 'green' => 'أخضر|Green', 'beige' => 'بيج|Beige', 'brown' => 'بني|Brown', 'gold' => 'ذهبي|Gold', 'orange' => 'برتقالي|Orange', 'yellow' => 'أصفر|Yellow', 'other' => 'لون آخر|Other' ),
		'part_type' => array( 'oem' => 'أصلي OEM|Original OEM', 'aftermarket' => 'تجاري Aftermarket|Aftermarket' ),
		'deal'      => array( 'sale' => 'للبيع|For sale', 'wanted' => 'مطلوب|Wanted' ),
		'period'    => array( 'day' => 'يومي|Per day', 'week' => 'أسبوعي|Per week', 'month' => 'شهري|Per month' ),
	);
	return $o[ $list ] ?? array();
}

/* Makes by origin (from the spreadsheet) with popular models (more models are added automatically when ads use them). */
function qs_makes() {
	return array(
		'يابانية|Japanese' => array( 'Toyota' => 'Land Cruiser,Prado,Camry,Corolla,Hilux,Yaris,RAV4,Fortuner,FJ Cruiser,Sequoia,Tundra,Supra,Avalon,Highlander', 'Nissan' => 'Patrol,Sunny,Altima,X-Trail,Pathfinder,Navara,Kicks,Maxima,GT-R,Juke', 'Lexus' => 'LX,GX,RX,ES,IS,LS,NX,UX', 'Honda' => 'Accord,Civic,CR-V,Pilot,City,HR-V,Odyssey', 'Mazda' => 'Mazda 3,Mazda 6,CX-3,CX-5,CX-9,MX-5', 'Mitsubishi' => 'Pajero,L200,Outlander,ASX,Lancer,Attrage', 'Suzuki' => 'Jimny,Swift,Vitara,Dzire,Ertiga,Ciaz,Baleno', 'Infiniti' => 'QX80,QX60,QX50,Q50', 'Subaru' => 'Forester,Outback,XV,WRX,Impreza', 'Isuzu' => 'D-Max,MU-X', 'Acura' => 'MDX,RDX,TLX', 'Daihatsu' => 'Terios,Sirion' ),
		'ألمانية|German'   => array( 'Mercedes-Benz' => 'C-Class,E-Class,S-Class,G-Class,GLE,GLS,GLC,A-Class,CLA,AMG GT,V-Class', 'BMW' => '3 Series,5 Series,7 Series,X1,X3,X5,X6,X7,M3,M5', 'Audi' => 'A3,A4,A6,A8,Q3,Q5,Q7,Q8,RS', 'Volkswagen' => 'Golf,Passat,Tiguan,Touareg,Jetta,Teramont', 'Porsche' => 'Cayenne,Macan,911,Panamera,Taycan', 'Opel' => 'Astra,Corsa,Insignia', 'Smart' => 'ForTwo,#1', 'Maybach' => 'S-Class,GLS' ),
		'أمريكية|American' => array( 'Ford' => 'F-150,Explorer,Expedition,Mustang,Ranger,Edge,Bronco,Territory', 'Chevrolet' => 'Tahoe,Silverado,Camaro,Malibu,Suburban,Traverse,Captiva,Corvette', 'GMC' => 'Yukon,Sierra,Acadia,Terrain', 'Cadillac' => 'Escalade,CT5,XT5,XT6', 'Jeep' => 'Wrangler,Grand Cherokee,Cherokee,Compass,Gladiator', 'Dodge' => 'Charger,Challenger,Durango,RAM', 'Chrysler' => '300C,Pacifica', 'Lincoln' => 'Navigator,Aviator,Nautilus', 'Tesla' => 'Model 3,Model Y,Model S,Model X,Cybertruck', 'Rivian' => 'R1T,R1S', 'Hummer' => 'H2,H3,EV', 'RAM' => '1500,2500' ),
		'كورية|Korean'     => array( 'Hyundai' => 'Elantra,Accent,Sonata,Tucson,Santa Fe,Creta,Palisade,Kona', 'Kia' => 'Sportage,Sorento,Cerato,Pegas,K5,Seltos,Telluride,Carnival', 'Genesis' => 'G70,G80,G90,GV70,GV80', 'KG Mobility (SsangYong)' => 'Rexton,Tivoli,Korando' ),
		'بريطانية|British' => array( 'Land Rover' => 'Defender,Discovery,Discovery Sport', 'Range Rover' => 'Range Rover,Sport,Velar,Evoque', 'Jaguar' => 'F-Pace,XF,XE,F-Type', 'Bentley' => 'Bentayga,Continental GT,Flying Spur', 'Rolls-Royce' => 'Cullinan,Ghost,Phantom,Wraith', 'Aston Martin' => 'DBX,Vantage,DB11', 'McLaren' => '720S,Artura,GT', 'Mini' => 'Cooper,Countryman', 'Lotus' => 'Emira,Eletre' ),
		'إيطالية|Italian'  => array( 'Ferrari' => '296,F8,Roma,SF90,Purosangue', 'Lamborghini' => 'Urus,Huracan,Revuelto', 'Maserati' => 'Ghibli,Levante,Grecale,MC20', 'Alfa Romeo' => 'Giulia,Stelvio', 'Fiat' => '500,Tipo', 'Lancia' => 'Ypsilon' ),
		'فرنسية|French'    => array( 'Renault' => 'Duster,Koleos,Megane,Symbol', 'Peugeot' => '208,3008,5008,508,Partner', 'Citroën' => 'C3,C5 Aircross,Berlingo', 'DS Automobiles' => 'DS7,DS4', 'Bugatti' => 'Chiron' ),
		'صينية|Chinese'    => array( 'BYD' => 'Atto 3,Seal,Han,Song Plus', 'MG' => 'MG ZS,MG5,MG RX5,MG HS,MG GT', 'Geely' => 'Coolray,Monjaro,Emgrand,Tugella', 'Changan' => 'CS35,CS75,CS85,UNI-K,Alsvin', 'Haval' => 'H6,Jolion,H9', 'Great Wall' => 'Wingle,Poer', 'GAC' => 'GS3,GS8,Emkoo,GN8', 'Jetour' => 'X70,T2,Dashing', 'Chery' => 'Tiggo 4,Tiggo 7,Tiggo 8,Arrizo', 'Exeed' => 'TXL,VX,RX', 'Hongqi' => 'H5,HS5,E-HS9', 'JAC' => 'S3,JS4,T8', 'Dongfeng' => 'Aeolus,Shine', 'BAIC' => 'X7,BJ40', 'Tank' => '300,500', 'Zeekr' => '001,X,7X', 'Lynk & Co' => '01,05', 'NIO' => 'ET5,ES6', 'XPeng' => 'G6,G9', 'Li Auto' => 'L7,L9' ),
		'أخرى|Other'       => array( 'Volvo' => 'XC90,XC60,XC40,S60,S90', 'Polestar' => '2,3,4', 'Saab' => '9-3,9-5', 'Škoda' => 'Octavia,Kodiaq,Superb', 'SEAT' => 'Leon,Ibiza', 'Cupra' => 'Formentor,Born', 'Dacia' => 'Duster,Sandero', 'Lada' => 'Niva,Vesta' ),
	);
}

/* Emirates → cities. */
function qs_cities() {
	return array(
		'abu-dhabi|أبوظبي|Abu Dhabi'                 => array( 'abu-dhabi-city|مدينة أبوظبي|Abu Dhabi City', 'al-ain|العين|Al Ain', 'mussafah|مصفح|Mussafah', 'khalifa-city|مدينة خليفة|Khalifa City', 'al-dhafra|الظفرة|Al Dhafra', 'ruwais|الرويس|Ruwais' ),
		'dubai|دبي|Dubai'                             => array( 'dubai-city|مدينة دبي|Dubai City', 'deira|ديرة|Deira', 'al-quoz|القوز|Al Quoz', 'jebel-ali|جبل علي|Jebel Ali', 'hatta|حتا|Hatta' ),
		'sharjah|الشارقة|Sharjah'                     => array( 'sharjah-city|مدينة الشارقة|Sharjah City', 'khor-fakkan|خورفكان|Khor Fakkan', 'kalba|كلباء|Kalba', 'dibba-al-hisn|دبا الحصن|Dibba Al Hisn', 'al-dhaid|الذيد|Al Dhaid' ),
		'ajman|عجمان|Ajman'                           => array( 'ajman-city|مدينة عجمان|Ajman City', 'masfout|مصفوت|Masfout', 'manama-ajman|المنامة|Al Manama' ),
		'umm-al-quwain|أم القيوين|Umm Al Quwain'      => array( 'uaq-city|مدينة أم القيوين|Umm Al Quwain City', 'falaj-al-mualla|فلج المعلا|Falaj Al Mualla' ),
		'ras-al-khaimah|رأس الخيمة|Ras Al Khaimah'    => array( 'rak-city|مدينة رأس الخيمة|Ras Al Khaimah City', 'al-hamra|الحمرا|Al Hamra', 'al-rams|الرمس|Al Rams' ),
		'fujairah|الفجيرة|Fujairah'                   => array( 'fujairah-city|مدينة الفجيرة|Fujairah City', 'dibba-fujairah|دبا الفجيرة|Dibba Al Fujairah', 'masafi|مسافي|Masafi' ),
	);
}
