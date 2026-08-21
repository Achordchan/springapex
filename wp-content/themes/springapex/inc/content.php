<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/content-enhancements.php';

function springapex_content(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }

    $data = [
        'brand' => [
            'name' => 'ApexSpring',
            'tagline' => 'SPRING MANUFACTURING EXPERT',
            'company' => 'Xuzhou APEX Spring Manufacturing Co., Ltd.',
            'email' => 'victoria@springapex.cn',
            'phone' => '+86 187 9642 2510',
            'whatsapp' => '+86 187 9642 2510',
            'address' => 'No. 15, Zhongnan Gaoke, Liuji Town, Tongshan District, Xuzhou City, Jiangsu Province, China 221112',
            'hours' => 'Monday – Friday, China Standard Time',
            'linkedin' => '',
            'facebook' => 'https://www.facebook.com/1088694501000892/',
            'x' => '',
            'instagram' => 'https://www.instagram.com/apexspring/',
            'tiktok' => '',
        ],
        'nav' => [
            ['label' => 'Home', 'slug' => 'home', 'href' => '/'],
            [
                'label' => 'About Us',
                'slug' => 'about-us',
                'href' => '/about/',
                'children' => [
                    ['label' => 'Company', 'href' => '/about/'],
                    ['label' => 'Sustainability', 'href' => '/sustainability/'],
                    ['label' => 'Download Center', 'href' => '/resources/'],
                ],
            ],
            ['label' => 'Products', 'slug' => 'products', 'href' => '/products/'],
            [
                'label' => 'Industries',
                'slug' => 'solutions',
                'href' => '/solutions/',
                'children' => [
                    ['label' => 'Industries', 'href' => '/solutions/'],
                    ['label' => 'Case Studies', 'href' => '/case-studies/'],
                ],
            ],
            [
                'label' => 'Custom Springs',
                'slug' => 'capabilities',
                'href' => '/capabilities/',
                'children' => [
                    ['label' => 'Capabilities', 'href' => '/capabilities/'],
                    ['label' => 'Manufacturing Videos', 'href' => '/manufacturing-videos/'],
                ],
            ],
            [
                'label' => 'News',
                'slug' => 'news',
                'href' => '/news/',
                'children' => [
                    ['label' => 'Industry News', 'href' => '/news/?news_type=industry-news'],
                    ['label' => 'Exhibitions', 'href' => '/news/?news_type=exhibitions'],
                    ['label' => 'Company News', 'href' => '/news/?news_type=company-news'],
                ],
            ],
            ['label' => 'Contact', 'slug' => 'contact', 'href' => '/contact/'],
        ],
        'home' => [
            'hero' => [
                'title' => "Precision Springs. Designed for Your Applications.",
                'subtitle' => 'From engineering design to mass production, we deliver reliable spring solutions for global industries.',
                'video_cta' => ['label' => 'Play a Video', 'youtube_id' => '5LUKHmIHPDY'],
                'quote_cta' => ['label' => 'Request a Quote', 'href' => '/contact/?intent=quote', 'icon' => 'arrow-right'],
                'image' => 'hero-spring-v3.png',
            ],
            'industries' => ['AUTOMOTIVE', 'INDUSTRIAL', 'MEDICAL', 'AEROSPACE', 'RAIL', 'ENERGY'],
            'pillars' => [
                ['icon' => 'pen', 'title' => 'Production-Ready Design', 'text' => 'Drawing review for reliable, manufacturable spring designs.'],
                ['icon' => 'cubes', 'title' => 'Prototype to Production', 'text' => 'Repeatable CNC precision from samples to volume orders.'],
                ['icon' => 'check-shield', 'title' => 'Verified Before Shipment', 'text' => 'Inspection, load testing and documentation for every batch.'],
                ['icon' => 'headset', 'title' => 'Fast Engineering Support', 'text' => 'Clear feedback and quotations within 24 hours.'],
            ],
            'applications' => [
                ['slug' => 'automotive', 'title' => 'Automotive', 'image' => 'home-automotive-v2.png'],
                ['slug' => 'industrial-equipment', 'title' => 'Industrial Equipment', 'image' => 'home-industrial-v2.png'],
                ['slug' => 'medical', 'title' => 'Medical Devices', 'image' => 'home-medical-v2.png'],
                ['slug' => 'energy', 'title' => 'Energy', 'image' => 'home-energy-v2.png'],
            ],
            'process' => [
                ['icon' => 'target', 'label' => 'Engineering'],
                ['icon' => 'cubes', 'label' => 'Prototyping'],
                ['icon' => 'gear', 'label' => 'Manufacturing'],
                ['icon' => 'check-shield', 'label' => 'Testing'],
                ['icon' => 'delivery', 'label' => 'Delivery'],
            ],
        ],
        'products' => [
            'hero' => [
                'title' => 'Products',
                'subtitle' => 'Precision springs engineered for dependable performance.',
                'primary_cta' => ['label' => 'Explore Product Range', 'href' => '/products/#product-families', 'icon' => 'arrow-right'],
                'image' => 'products-hero-v3.png',
                'mobile_image' => 'products-hero-mobile-v1.png',
            ],
            'categories' => [
                ['slug' => 'compression-springs', 'title' => 'Compression Springs', 'image' => 'product-compression-detail-v4.png', 'category_image' => 'product-compression-detail-v4.png', 'featured_image' => 'product-compression-card-v3.png', 'desc' => 'Designed for dependable axial load and return force.'],
                ['slug' => 'extension-springs', 'title' => 'Extension Springs', 'image' => 'product-extension-v2.png', 'category_image' => 'product-extension-category-v3.png', 'desc' => 'Built to deliver reliable tension and controlled extension.'],
                ['slug' => 'torsion-springs', 'title' => 'Torsion Springs', 'image' => 'product-torsion-v2.png', 'category_image' => 'product-torsion-category-v3.png', 'desc' => 'Engineered for repeatable rotational force and torque.'],
                ['slug' => 'disc-springs', 'title' => 'Disc Springs', 'image' => 'product-disc-v2.png', 'desc' => 'High load capacity in compact assemblies.'],
                ['slug' => 'wire-forms', 'title' => 'Wire Forms', 'image' => 'product-wire-form-v2.png', 'desc' => 'Complex formed-wire parts manufactured to specification.'],
                ['slug' => 'die-springs', 'title' => 'Die Springs', 'image' => 'product-disc-v2.png', 'desc' => 'Heavy-duty springs for stamping dies and industrial tooling.'],
                ['slug' => 'other-customized-springs', 'title' => 'Other Customized Springs', 'image' => 'product-wire-v2.png', 'desc' => 'Custom spring solutions for specialized applications and requirements.'],
            ],
            'featured' => ['compression-springs', 'extension-springs', 'torsion-springs', 'disc-springs'],
        ],
        'product_details' => [
            'compression-springs' => [
                'title' => 'Compression Springs',
                'subtitle' => 'Designed to resist axial compression and deliver reliable performance.',
                'overview' => 'Compression springs are open-coil helical springs that store energy under axial load and return toward their original length when that load is removed. Diameters, wire sizes, materials, ends and surface treatments can be engineered around the application.',
                'image' => 'product-compression-detail-v4.png',
                'gallery' => [
                    ['image' => 'product-compression-detail-v4.png'],
                    ['image' => 'product-compression-card-v3.png'],
                    ['image' => 'quality-inspection-original.jpg'],
                ],
                'catalog_url' => '',
                'specs' => [
                    ['label' => 'Wire Diameter', 'value' => '0.1 – 60 mm'],
                    ['label' => 'Outer Diameter', 'value' => '1 – 150 mm'],
                    ['label' => 'Free Length', 'value' => '2 – 600 mm'],
                    ['label' => 'Material', 'value' => 'Music Wire, Stainless Steel, Carbon Steel'],
                    ['label' => 'Surface Treatment', 'value' => 'Zinc Plating, Passivation, Phosphate, Shot Peening'],
                    ['label' => 'Ends', 'value' => 'Closed, Closed & Ground, Plain'],
                ],
                'materials' => [
                    ['title' => 'Music Wire', 'icon' => 'wire'],
                    ['title' => 'Stainless Steel', 'icon' => 'shield'],
                    ['title' => 'Carbon Steel', 'icon' => 'disc'],
                ],
                'applications' => [
                    ['title' => 'Automotive', 'icon' => 'car'],
                    ['title' => 'Industrial Equipment', 'icon' => 'gear'],
                    ['title' => 'Aerospace', 'icon' => 'rocket'],
                    ['title' => 'Consumer Products', 'icon' => 'box'],
                ],
            ],
        ],
        'solutions' => [
            'hero' => [
                'title' => 'Solutions',
                'subtitle' => 'Tailored spring solutions for your industry.',
                'image' => 'solutions-hero-v2.png',
                'mobile_image' => 'solutions-hero-mobile-v1.png',
            ],
            'items' => [
                ['slug' => 'automotive', 'title' => 'Automotive', 'tagline' => 'Built for performance', 'image' => 'solution-automotive-v2.png'],
                ['slug' => 'industrial-equipment', 'title' => 'Industrial Equipment', 'tagline' => 'Engineered for reliability', 'image' => 'solution-industrial-v3.png'],
                ['slug' => 'medical', 'title' => 'Medical', 'tagline' => 'Precision you can trust', 'image' => 'solution-medical-v3.png'],
                ['slug' => 'aerospace', 'title' => 'Aerospace', 'tagline' => 'Engineered to perform', 'image' => 'solution-aerospace-v2.png'],
                ['slug' => 'energy', 'title' => 'Energy', 'tagline' => 'Built to endure', 'image' => 'solution-energy-v2.png'],
                ['slug' => 'rail-transit', 'title' => 'Rail Transit', 'tagline' => 'Moving the future', 'image' => 'solution-rail-v2.png'],
            ],
        ],
        'case_studies' => [
            'hero' => [
                'title' => 'Case Studies',
                'subtitle' => 'Approved project examples from application review through repeat production.',
                'image' => 'solutions-hero-v2.png',
                'mobile_image' => 'solutions-hero-mobile-v1.png',
            ],
            'items' => [],
        ],
        'news' => [
            'hero' => [
                'title' => 'News',
                'subtitle' => 'Company and engineering updates.',
                'image' => 'generated/springapex-news-hero-v3.webp',
                'mobile_image' => 'news-hero-mobile-v1.png',
            ],
            'items' => [
                [
                    'slug' => 'manufacturing-expo-bangkok-2024',
                    'title' => 'ApexSpring at Manufacturing Expo 2024 in Bangkok',
                    'date' => '2024-06-20',
                    'date_label' => 'June 17–20, 2024',
                    'category' => 'Exhibition',
                    'news_type' => 'exhibitions',
                    'summary' => 'ApexSpring presented precision spring products and met visitors at Manufacturing Expo 2024 in Bangkok, Thailand.',
                    'image' => 'news/manufacturing-expo-bangkok-2024/hero.jpg',
                    'products' => ['compression-springs', 'torsion-springs', 'wire-forms'],
                    'content' => [
                        ['type' => 'p', 'text' => 'From June 17 to 20, 2024, ApexSpring exhibited at Manufacturing Expo in Bangkok, Thailand. The event brought manufacturing teams and suppliers together at BITEC to review production technologies and component solutions.'],
                        ['type' => 'p', 'text' => 'At the ApexSpring booth, visitors reviewed spring samples and discussed custom applications with our team. The conversations focused on turning drawings and operating requirements into practical production plans.'],
                        ['type' => 'h2', 'text' => 'Discussions at the booth'],
                        ['type' => 'list', 'items' => [
                            'Custom spring geometry, load and installation requirements.',
                            'Material and surface-treatment options for different operating environments.',
                            'Prototype, production and quality-documentation requirements.',
                        ]],
                        ['type' => 'h2', 'text' => 'Thank you for visiting'],
                        ['type' => 'p', 'text' => 'Thank you to everyone who visited the ApexSpring booth and shared their application requirements. Our team continues to support follow-up discussions with drawing review, product selection and quotation preparation.'],
                    ],
                    'gallery_title' => 'Manufacturing Expo 2024 in Bangkok',
                    'gallery' => [
                        ['image' => 'news/manufacturing-expo-bangkok-2024/venue.jpg', 'alt' => 'Manufacturing Expo 2024 entrance at BITEC in Bangkok', 'caption' => 'Manufacturing Expo 2024 at BITEC, Bangkok'],
                        ['image' => 'news/manufacturing-expo-bangkok-2024/visitor-discussion.jpg', 'alt' => 'ApexSpring team discussing spring samples with a visitor', 'caption' => 'Spring samples and application discussion'],
                        ['image' => 'news/manufacturing-expo-bangkok-2024/project-meeting.jpg', 'alt' => 'Visitors meeting with the ApexSpring team at the exhibition booth', 'caption' => 'Project requirements discussed at the booth'],
                        ['image' => 'news/manufacturing-expo-bangkok-2024/product-consultation.jpg', 'alt' => 'ApexSpring representative presenting products at Manufacturing Expo', 'caption' => 'Product consultation during the exhibition'],
                        ['image' => 'news/manufacturing-expo-bangkok-2024/technical-review.jpg', 'alt' => 'Technical discussion at the ApexSpring exhibition counter', 'caption' => 'Technical requirements review'],
                        ['image' => 'news/manufacturing-expo-bangkok-2024/exhibition-team.jpg', 'alt' => 'ApexSpring exhibition team at Manufacturing Expo 2024', 'caption' => 'The ApexSpring exhibition team'],
                    ],
                ],
            ],
        ],
        'about' => [
            'hero' => [
                'title' => 'About ApexSpring',
                'subtitle' => 'Precision spring manufacturing since 2001.',
                'image' => 'about-building-v3.png',
                'mobile_image' => 'about-hero-mobile-v1.png',
            ],
            'company_video' => [
                'title' => 'Inside ApexSpring',
                'youtube_id' => '5LUKHmIHPDY',
            ],
            'stats' => [
                ['icon' => 'users', 'value' => '2001', 'label' => 'Founded'],
                ['icon' => 'factory', 'value' => '3', 'label' => 'Production Facilities'],
                ['icon' => 'spring', 'value' => '120+', 'label' => 'Employees'],
                ['icon' => 'globe', 'value' => '2,000+', 'label' => 'Customers'],
            ],
            'why_choose' => [
                'eyebrow' => 'WHY APEXSPRING',
                'title' => 'From application challenge to repeat production.',
                'media' => [
                    [
                        'image' => 'generated/about-design-engineering-v1.png',
                        'label' => 'Design & Engineering',
                        'alt' => 'Spring engineer reviewing a CAD model and measuring a compression spring',
                    ],
                    [
                        'image' => 'generated/about-controlled-production-v1.png',
                        'label' => 'Controlled Production',
                        'alt' => 'CNC spring coiling equipment producing consistent compression springs',
                    ],
                ],
                'items' => [
                    ['icon_image' => 'generated/about-process-application-v1.png', 'title' => 'Application Review', 'text' => 'Drawing, sample or operating requirements.'],
                    ['icon_image' => 'generated/about-process-design-v1.png', 'title' => 'Design & Manufacturability', 'text' => 'Geometry, material and tolerances reviewed for production.'],
                    ['icon_image' => 'generated/about-process-prototype-v1.png', 'title' => 'Prototype & Validation', 'text' => 'Samples, dimensional checks and load verification.'],
                    ['icon_image' => 'generated/about-process-production-v1.png', 'title' => 'Controlled Production', 'text' => 'CNC forming, heat or surface processes and repeatable settings.'],
                    ['icon_image' => 'generated/about-process-delivery-v1.png', 'title' => 'Inspection & Delivery', 'text' => 'Batch checks, agreed records, identification and export support.'],
                ],
                'outcomes_title' => 'Why customers choose this approach',
                'outcomes' => [
                    ['icon' => 'network', 'text' => 'One Project Path'],
                    ['icon' => 'users', 'text' => 'Fewer Handovers'],
                    ['icon' => 'check-shield', 'text' => 'Repeatable Quality'],
                    ['icon' => 'globe', 'text' => 'Global Support'],
                ],
            ],
            'team' => [
                'eyebrow' => 'WOMEN-OWNED PRECISION MANUFACTURING',
                'statement_lead' => 'Women-owned precision.',
                'statement_signature' => 'Driven by innovation.',
                'founder' => [
                    'name' => 'Tan Longfeng',
                    'role' => 'Founder',
                    'image' => 'team/tan-longfeng.webp',
                ],
                'groups' => [
                    [
                        'title' => 'Engineering',
                        'members' => [
                            ['name' => 'Wu Chao', 'role' => 'Chairman', 'image' => 'team/wu-chao.webp'],
                            ['name' => 'Feng Yulin', 'role' => 'General Manager', 'image' => 'team/feng-yulin.webp'],
                            ['name' => 'Zhou Yang', 'role' => 'Senior Engineer', 'image' => 'team/zhou-yang.webp'],
                            ['name' => 'Chen Zhiyuan', 'role' => 'Senior Engineer', 'image' => 'team/chen-zhiyuan.webp'],
                        ],
                    ],
                    [
                        'title' => 'Global Support',
                        'members' => [
                            ['name' => 'Chen Shangrong', 'role' => 'Operations Director', 'image' => 'team/chen-shangrong.webp'],
                            ['name' => 'Xu Qinghe', 'role' => 'Engineer', 'image' => 'team/xu-qinghe.webp'],
                            ['name' => 'Lin Shuran', 'role' => 'Engineer', 'image' => 'team/lin-shuran.webp'],
                            ['name' => 'Ji Minli', 'role' => 'Export Engineer', 'image' => 'team/ji-minli.webp'],
                        ],
                    ],
                ],
            ],
        ],
        'capabilities' => [
            'hero' => [
                'title' => 'Custom Spring Manufacturing',
                'subtitle' => 'From application requirements and drawing review to controlled repeat production.',
                'cta' => ['label' => 'Upload Your Drawing', 'href' => '/contact/?intent=drawing', 'icon' => 'upload'],
                'image' => 'generated/springapex-capabilities-hero-v2.webp',
                'mobile_image' => 'capabilities-hero-mobile-v1.png',
            ],
            'intro' => [
                'title' => 'Built around your application.',
                'text' => 'Engineering review, forming, finishing and verification are aligned to the spring geometry, load and operating conditions you provide.',
            ],
            'items' => [
                ['icon' => 'cnc', 'title' => 'Precision Engineering', 'text' => 'Parameter analysis and manufacturable designs built to application requirements.'],
                ['icon' => 'qc', 'title' => 'CNC Coiling', 'text' => 'Controlled forming processes for repeatable dimensions and force.'],
                ['icon' => 'heat', 'title' => 'Heat Treatment & Surface', 'text' => 'Material and finishing options selected for durability and corrosion resistance.'],
                ['icon' => 'search', 'title' => 'Testing & Quality', 'text' => 'Inspection and validation throughout production and shipment.'],
            ],
            'project_brief' => [
                'eyebrow' => 'PROJECT INPUTS',
                'title' => 'Start with the information that drives spring performance.',
                'text' => 'A drawing is helpful, but an effective review also considers working load, movement, environment, quantity and required production records.',
                'image' => 'generated/springapex-engineering-desk-v1.webp',
                'items' => [
                    ['icon' => 'pen', 'title' => 'Geometry or Drawing', 'text' => 'Dimensions, available space, end details and installation constraints.'],
                    ['icon' => 'spring', 'title' => 'Load & Movement', 'text' => 'Required force or torque at the working position and expected travel.'],
                    ['icon' => 'heat', 'title' => 'Material & Environment', 'text' => 'Temperature, corrosion exposure, cleanliness and service-life expectations.'],
                    ['icon' => 'form', 'title' => 'Quantity & Records', 'text' => 'Prototype and production volume, inspection reports and traceability needs.'],
                ],
            ],
            'verification' => [
                'image' => 'product-detail/compression-dimension-guide-v2.png',
                'image_alt' => 'Spring dimension reference diagram with wire diameter, outer diameter and free length callouts',
            ],
        ],
        'manufacturing_videos' => [
            'eyebrow' => 'MANUFACTURING VIDEOS',
            'title' => 'See how precision is built.',
            'intro' => 'Explore the processes, inspection and testing behind repeatable spring production.',
            'hero_image' => 'manufacturing-videos/hero-engineering-studio-v2.webp',
            'featured' => [
                'category' => 'Manufacturing Process',
                'title' => 'From Wire to Verified Performance',
                'image' => 'manufacturing-videos/featured-cnc-coiling-v1.webp',
                'youtube_id' => '5LUKHmIHPDY',
                'duration' => '09:57',
            ],
            'categories' => [
                [
                    'title' => 'Manufacturing Processes',
                    'text' => 'Coiling, forming, heat treatment and controlled production steps.',
                    'image' => 'manufacturing-videos/manufacturing-processes-v1.webp',
                    'duration' => '03:12',
                ],
                [
                    'title' => 'Quality Inspection',
                    'text' => 'Dimensional checks and verification matched to the drawing.',
                    'image' => 'manufacturing-videos/quality-inspection-v1.webp',
                    'duration' => '02:45',
                ],
                [
                    'title' => 'Stamping & Forming',
                    'text' => 'Precision tooling and forming for repeatable component geometry.',
                    'image' => 'manufacturing-videos/stamping-forming-v1.webp',
                    'duration' => '03:36',
                ],
                [
                    'title' => 'Testing & Validation',
                    'text' => 'Controlled load and performance checks before release.',
                    'image' => 'manufacturing-videos/testing-validation-v1.webp',
                    'duration' => '02:58',
                ],
                [
                    'title' => 'Application Engineering',
                    'text' => 'Drawing review, spring measurement and design decisions before production.',
                    'image' => 'manufacturing-videos/application-engineering-v1.webp',
                    'duration' => '03:28',
                ],
                [
                    'title' => 'Machine Setup',
                    'text' => 'Tooling alignment and CNC setup for stable repeat production.',
                    'image' => 'manufacturing-videos/machine-setup-v1.webp',
                    'duration' => '04:06',
                ],
                [
                    'title' => 'Material Traceability',
                    'text' => 'Wire identification and material checks from receipt to production.',
                    'image' => 'manufacturing-videos/material-traceability-v1.webp',
                    'duration' => '02:34',
                ],
                [
                    'title' => 'Packaging & Delivery',
                    'text' => 'Final count, protective packing and shipment preparation.',
                    'image' => 'manufacturing-videos/packaging-delivery-v1.webp',
                    'duration' => '02:51',
                ],
            ],
        ],
        'contact' => [
            'hero' => [
                'title' => 'Contact',
                'subtitle' => 'Tell us what you need.',
                'points' => [
                    ['icon' => 'headset', 'title' => 'Fast, expert support', 'text' => 'Get answers from our engineering team.'],
                    ['icon' => 'shield', 'title' => 'Confidential & secure', 'text' => 'Your drawings and project details are handled with care.'],
                ],
                'image' => 'contact-springs-v2.png',
                'mobile_image' => 'contact-hero-mobile-v1.png',
            ],
            'inquiry_types' => ['Request a Quote', 'Upload a Drawing', 'Technical Support', 'Custom Design', 'Catalog / Technical Documents', 'Supplier Qualification', 'Feedback / Suggestions', 'Partnership', 'Other'],
        ],
    ];

    $data = array_replace($data, springapex_content_enhancements());
    $data = apply_filters('springapex_content', $data);
    return $data;
}

function springapex_get(string $path, mixed $default = null): mixed
{
    $node = springapex_content();
    foreach (explode('.', $path) as $part) {
        if (!is_array($node) || !array_key_exists($part, $node)) {
            return $default;
        }
        $node = $node[$part];
    }
    return $node;
}

function springapex_parse_meta_rows(mixed $rows): array
{
    if (is_array($rows)) {
        return array_values(array_filter($rows, 'is_array'));
    }
    if (!is_string($rows) || trim($rows) === '') {
        return [];
    }

    $parsed = [];
    foreach (preg_split('/\R/', $rows) ?: [] as $line) {
        $parts = array_map('trim', explode('|', $line, 2));
        if (($parts[0] ?? '') !== '') {
            $parsed[] = ['label' => $parts[0], 'value' => $parts[1] ?? ''];
        }
    }
    return $parsed;
}

function springapex_product_from_post(object $post): array
{
    $slug = (string) $post->post_name;
    $seed = springapex_product_seed($slug) ?? [];
    $thumbnail_id = function_exists('get_post_thumbnail_id') ? (int) get_post_thumbnail_id($post) : 0;
    $post_id = (int) $post->ID;
    $meta_or_seed = static function (string $key, mixed $fallback) use ($post_id): mixed {
        return metadata_exists('post', $post_id, $key)
            ? get_post_meta($post_id, $key, true)
            : $fallback;
    };

    $specs = $meta_or_seed('_springapex_specs', $seed['specs'] ?? []);
    $materials = $meta_or_seed('_springapex_materials', $seed['materials'] ?? []);
    $applications = $meta_or_seed('_springapex_applications', $seed['applications'] ?? []);
    $subtitle = $meta_or_seed('_springapex_subtitle', $seed['subtitle'] ?? '');
    $catalog_url = $meta_or_seed('_springapex_catalog_url', $seed['catalog_url'] ?? '');
    $seed_image = $meta_or_seed('_springapex_seed_image', $seed['image'] ?? '');
    $featured = $meta_or_seed('_springapex_featured', !empty($seed['featured']) ? '1' : '0');
    $database_image = ['id' => $thumbnail_id, 'file' => (string) $seed_image];
    $has_custom_file = (string) $seed_image !== '' && (string) $seed_image !== (string) ($seed['image'] ?? '');
    $listing_image = $thumbnail_id > 0 || $has_custom_file ? $database_image : null;

    // Hero gallery: ordered rows of {image, image_id}. Falls back to the seed
    // gallery, then to the single primary image, so every product resolves to at
    // least one image.
    if (metadata_exists('post', $post_id, '_springapex_gallery')) {
        $gallery_raw = get_post_meta($post_id, '_springapex_gallery', true);
    } elseif ($thumbnail_id > 0) {
        // Gallery support was added after Featured Images. Existing products must
        // keep their current primary image instead of silently reverting to seed art.
        $gallery_raw = [['image_id' => $thumbnail_id, 'image' => '']];
    } else {
        $gallery_raw = $seed['gallery'] ?? [];
    }
    $gallery = [];
    foreach ((array) $gallery_raw as $gallery_row) {
        if (!is_array($gallery_row)) {
            continue;
        }
        $gallery_id = (int) ($gallery_row['image_id'] ?? 0);
        $gallery_file = (string) ($gallery_row['image'] ?? '');
        if (
            $gallery_id > 0 &&
            function_exists('get_post_type') &&
            get_post_type($gallery_id) !== 'attachment'
        ) {
            $gallery_id = 0;
        }
        if ($gallery_id > 0 || $gallery_file !== '') {
            $gallery[] = ['id' => $gallery_id, 'file' => $gallery_file];
        }
    }
    if ($gallery === []) {
        $gallery = [$database_image];
    }

    return array_merge($seed, [
        'id' => $post_id,
        'slug' => $slug,
        'title' => get_the_title($post),
        'desc' => (string) ($post->post_excerpt ?? ''),
        'subtitle' => (string) $subtitle,
        'overview' => (string) ($post->post_content ?? ''),
        'image' => $database_image,
        'gallery' => $gallery,
        'category_image' => $listing_image ?? ($seed['category_image'] ?? $database_image),
        'featured_image' => $listing_image ?? ($seed['featured_image'] ?? $database_image),
        'specs' => springapex_parse_meta_rows($specs),
        'materials' => is_array($materials) ? array_values(array_filter($materials, 'is_array')) : [],
        'applications' => is_array($applications) ? array_values(array_filter($applications, 'is_array')) : [],
        'catalog_url' => (string) $catalog_url,
        'featured' => (bool) $featured,
    ]);
}

function springapex_product_for_view(int $post_id): ?array
{
    if (!function_exists('get_post')) {
        return null;
    }

    $post = get_post($post_id);
    if (!$post || (string) $post->post_type !== 'spring_product') {
        return null;
    }

    if (function_exists('post_password_required') && post_password_required($post)) {
        return null;
    }

    if (
        (string) $post->post_status !== 'publish' &&
        (!function_exists('current_user_can') || !current_user_can('read_post', $post_id))
    ) {
        return null;
    }

    return springapex_product_from_post($post);
}

function springapex_product_seed(string $slug): ?array
{
    $categories = springapex_get('products.categories', []);
    $details = springapex_get('product_details', []);
    foreach ($categories as $category) {
        if (($category['slug'] ?? '') !== $slug) {
            continue;
        }
        $seed = array_merge($category, $details[$slug] ?? [
            'subtitle' => $category['desc'] ?? '',
            'overview' => $category['desc'] ?? '',
            'specs' => [],
            'materials' => [],
            'applications' => [],
            'catalog_url' => '',
        ]);
        // Every product's hero gallery defaults to its own product image, so the
        // backend gallery is never empty and stays in step with the front end.
        // Products with an explicit gallery (e.g. compression) keep it.
        if (empty($seed['gallery']) && !empty($seed['image'])) {
            $seed['gallery'] = [['image' => (string) $seed['image']]];
        }
        return $seed;
    }
    return null;
}

function springapex_products(): array
{
    if (defined('SPRINGAPEX_PREVIEW')) {
        return array_map(static fn(array $item): array => springapex_product_seed((string) $item['slug']) ?? $item, springapex_get('products.categories', []));
    }

    if (!function_exists('get_posts') || !post_type_exists('spring_product')) {
        return [];
    }

    $posts = get_posts([
        'post_type' => 'spring_product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
    ]);

    return array_map('springapex_product_from_post', $posts ?: []);
}

function springapex_product(string $slug): ?array
{
    if (defined('SPRINGAPEX_PREVIEW')) {
        return springapex_product_seed($slug);
    }

    if (!function_exists('get_posts') || !post_type_exists('spring_product')) {
        return null;
    }

    $slug = sanitize_title($slug);
    if ($slug === '') {
        return null;
    }

    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'spring_product',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'orderby' => 'ID',
        'order' => 'ASC',
    ]);

    return isset($posts[0]) ? springapex_product_for_view((int) $posts[0]->ID) : null;
}

function springapex_featured_products(?array $products = null): array
{
    if (defined('SPRINGAPEX_PREVIEW')) {
        $slugs = springapex_get('products.featured', []);
        if ($products === null) {
            return array_values(array_filter(array_map('springapex_product_seed', $slugs)));
        }

        return array_values(array_filter(
            $products,
            static fn(array $product): bool => in_array((string) ($product['slug'] ?? ''), $slugs, true)
        ));
    }

    $products ??= springapex_products();
    $featured = array_values(array_filter($products, static fn(array $product): bool => !empty($product['featured'])));
    return array_slice($featured, 0, 4);
}

function springapex_solutions(): array
{
    if (defined('SPRINGAPEX_PREVIEW')) {
        return springapex_get('solutions.items', []);
    }

    if (!function_exists('get_posts') || !post_type_exists('spring_solution')) {
        return [];
    }

    $posts = get_posts([
        'post_type' => 'spring_solution',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
    ]);

    return array_map(static function (object $post): array {
        $seed_items = springapex_get('solutions.items', []);
        $seed = [];
        foreach ($seed_items as $item) {
            if (($item['slug'] ?? '') === $post->post_name) {
                $seed = $item;
                break;
            }
        }

        $post_id = (int) $post->ID;
        $seed_image = metadata_exists('post', $post_id, '_springapex_seed_image')
            ? get_post_meta($post_id, '_springapex_seed_image', true)
            : ($seed['image'] ?? '');

        return array_merge($seed, [
            'id' => $post_id,
            'slug' => (string) $post->post_name,
            'title' => get_the_title($post),
            'tagline' => (string) ($post->post_excerpt ?? ''),
            'image' => [
                'id' => (int) get_post_thumbnail_id($post),
                'file' => (string) $seed_image,
            ],
        ]);
    }, $posts ?: []);
}

/**
 * 「网站内容 → 行业方案页」的行业卡片与 spring_solution 文章同步。
 *
 * 前台行业列表查的是 CPT（springapex_solutions()），而运营者在设置页
 * repeater（solutions.items）里新增的卡片历史上只存进内容配置、没有对应
 * 文章，前台静默丢弃——表现为「后台加了前台还是 6 个」。同步规则：
 * - repeater 条目没有对应文章 → 创建已发布文章（标题/标语/配图来自条目），
 *   带 _springapex_from_content 标记，排在现有卡片之后；
 * - 标记过的卡片跟随 repeater 更新标题/标语/配图；
 * - repeater 里移除的标记卡片转草稿（前台消失，后台可恢复，不物理删除）。
 * 六个种子方案不带标记，仍由「行业方案」CPT 面板管理，行为不变。
 * 挂在 init 上幂等运行，稳态（两边一致时）无任何写入。
 */
function springapex_sync_solutions_from_content(): void
{
    if (defined('SPRINGAPEX_PREVIEW') || !function_exists('get_posts') || !post_type_exists('spring_solution')) {
        return;
    }
    // 稳态零锁零写：先只读预检（repeater 与文章已一致则直接返回），
    // 有差异才取 option 锁——取锁本身是插入 option、释放是删除，每个
    // 请求都跑等于每次页面访问多两笔写库。
    if (!springapex_solutions_sync_pending()) {
        return;
    }
    // 建卡路径非原子（先查 slug 再插入，post_name 无唯一约束）：两个未缓存
    // 的并发请求会各自插入、产出同 slug 的重复卡。整段用 option 锁串行化，
    // 抢不到锁的请求直接跳过（幂等，下个请求会校平）。
    $lock_name = 'springapex_solutions_sync_lock';
    $lock_token = springapex_acquire_option_lock($lock_name, 30);
    if ($lock_token === '') {
        return;
    }
    try {
        springapex_sync_solutions_from_content_locked();
    } finally {
        springapex_release_option_lock($lock_name, $lock_token);
    }
}

/**
 * 只读预检：repeater 与同步生成的文章是否存在差异。判断条件必须与
 * springapex_sync_solutions_from_content_locked() 的写入条件保持一致，
 * 改一处必须同步另一处。
 */
function springapex_solutions_sync_pending(): bool
{
    $state = springapex_solutions_sync_state();
    foreach ($state['items'] as $slug => $item) {
        $existing = $state['posts_by_slug'][$slug] ?? null;
        if ($existing === null) {
            return true;
        }
        if (get_post_meta((int) $existing->ID, '_springapex_from_content', true) !== '1') {
            continue;
        }
        if (springapex_solution_sync_differs($existing, $item)) {
            return true;
        }
    }
    foreach ($state['posts'] as $post) {
        if (
            get_post_meta((int) $post->ID, '_springapex_from_content', true) === '1'
            && !isset($state['items'][$post->post_name])
            && in_array($post->post_status, ['publish', 'future'], true)
        ) {
            return true;
        }
    }
    return false;
}

/** repeater 图片字段 → [附件ID, 主题文件名]；附件已被删除的数字 ID 归零。 */
function springapex_solution_sync_image(array $item): array
{
    $image = $item['image'] ?? '';
    $image_id = is_int($image) ? $image : (ctype_digit((string) $image) ? (int) $image : 0);
    if ($image_id > 0 && function_exists('get_post') && get_post($image_id) === null) {
        // 附件被永久删除后 ID 永远比对失败、set_post_thumbnail 也无法恢复，
        // 不归零会陷入每请求取锁重试的死循环。卡片回到无图状态，运营者可
        // 重选——注意文件名也必须为空，否则数字串会被当主题文件名落进
        // seed 渲染出坏图。
        return [0, ''];
    }
    return [$image_id, $image_id > 0 ? '' : (string) $image];
}

/** 单张已标记卡片与 repeater 条目是否有差异（与写入侧同一套比较）。 */
function springapex_solution_sync_differs(object $post, array $item): bool
{
    $title = sanitize_text_field((string) ($item['title'] ?? ''));
    $tagline = sanitize_text_field((string) ($item['tagline'] ?? ''));
    [$image_id, $image_file] = springapex_solution_sync_image($item);
    $post_id = (int) $post->ID;

    if ($post->post_status !== 'publish') {
        return true;
    }
    if ($title !== '' && (string) $post->post_title !== $title) {
        return true;
    }
    if ((string) $post->post_excerpt !== $tagline) {
        return true;
    }
    $seed_meta = metadata_exists('post', $post_id, '_springapex_seed_image')
        ? (string) get_post_meta($post_id, '_springapex_seed_image', true)
        : null;
    // seed 必须以「已存在的空串」形式收敛：meta 缺失时前台会回退到
    // repeater 的原始值（数字串会被当主题文件名渲染出坏图）。
    if ($seed_meta !== $image_file) {
        return true;
    }
    if ($image_id > 0) {
        if ((int) get_post_thumbnail_id($post_id) !== $image_id) {
            return true;
        }
    } elseif ((int) get_post_thumbnail_id($post_id) > 0) {
        return true;
    }
    return false;
}

/** repeater 条目与全状态文章（含回收站 desired slug）的只读快照。 */
function springapex_solutions_sync_state(): array
{
    $items = springapex_get('solutions.items', []);
    if (!is_array($items)) {
        $items = [];
    }
    $items_by_slug = [];
    foreach ($items as $item) {
        $slug = sanitize_title((string) ($item['slug'] ?? ''));
        if ($slug !== '') {
            $items_by_slug[$slug] = $item;
        }
    }

    $posts = get_posts([
        'post_type' => 'spring_solution',
        'post_status' => ['publish', 'draft', 'private', 'pending', 'future', 'trash'],
        'posts_per_page' => -1,
        'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
    ]);
    $posts_by_slug = [];
    $next_order = 0;
    foreach ($posts as $post) {
        // 回收站会改写 post_name（slug__trashed 后缀）：按 WP 记录的
        // _wp_desired_post_slug（或去掉后缀）还原期望 slug 再做键，否则
        // 查找漏配会转而新建替代文章、留下累积的回收站副本。
        $desired_slug = (string) $post->post_name;
        if ($post->post_status === 'trash' && str_ends_with($desired_slug, '__trashed')) {
            $meta_slug = (string) get_post_meta((int) $post->ID, '_wp_desired_post_slug', true);
            $desired_slug = $meta_slug !== '' ? $meta_slug : substr($desired_slug, 0, -strlen('__trashed'));
        }
        $posts_by_slug[$desired_slug] = $post;
        $next_order = max($next_order, (int) $post->menu_order + 1);
    }

    // 缺失文章的条目若用着 WP 保留 slug（feed/embed 等）或与其他类型已发布
    // 文章撞名，wp_insert_post 会被 wp_unique_post_slug 改名（feed-2），
    // 之后每个快照都找不到原 slug → 再插 feed-3……无限繁殖。这种条目
    // 直接从状态里剔除（稳态零额外查询：只在 slug 缺失时才探测）。
    foreach (array_keys($items_by_slug) as $missing_slug) {
        if (isset($posts_by_slug[$missing_slug])) {
            continue;
        }
        if (function_exists('wp_unique_post_slug') && wp_unique_post_slug($missing_slug, 0, 'publish', 'spring_solution', 0) !== $missing_slug) {
            unset($items_by_slug[$missing_slug]);
        }
    }

    // slug 改名识别：恰好一个缺配条目 + 恰好一个被标记的失配文章，且标题
    // 完全一致 → 视为运营者改了 slug（重命名保留原帖及其全部文章侧
    // meta/详情），而不是新建 + 转草稿。多对多或标题不同时不猜，退回
    // 原行为（新建 + 清理）。
    $renames = [];
    // 缺配条目 = items 有而 posts_by_slug 没有的 slug（不是全部条目键）
    $missing = array_values(array_diff(array_keys($items_by_slug), array_keys($posts_by_slug)));
    $orphan_ids = [];
    $desired_by_id = [];
    foreach ($posts_by_slug as $desired_slug => $post) {
        $desired_by_id[(int) $post->ID] = $desired_slug;
    }
    foreach ($posts as $post) {
        $is_flagged = get_post_meta((int) $post->ID, '_springapex_from_content', true) === '1';
        $is_orphan = !isset($items_by_slug[$desired_by_id[(int) $post->ID] ?? '']);
        if ($is_flagged && $is_orphan && in_array($post->post_status, ['publish', 'future'], true)) {
            $orphan_ids[] = (int) $post->ID;
        }
    }
    if (count($missing) === 1 && count($orphan_ids) === 1) {
        $new_slug = $missing[0];
        $orphan = get_post($orphan_ids[0]);
        $item = $items_by_slug[$new_slug];
        if ($orphan !== null
            && sanitize_text_field((string) ($item['title'] ?? '')) !== ''
            && sanitize_text_field((string) $orphan->post_title) === sanitize_text_field((string) ($item['title'] ?? ''))) {
            $renames[$new_slug] = $orphan;
        }
    }

    return [
        'items' => $items_by_slug,
        'posts' => $posts,
        'posts_by_slug' => $posts_by_slug,
        'next_order' => $next_order,
        'renames' => $renames,
    ];
}

function springapex_sync_solutions_from_content_locked(): void
{
    $state = springapex_solutions_sync_state();
    $items_by_slug = $state['items'];
    $posts = $state['posts'];
    $posts_by_slug = $state['posts_by_slug'];
    $next_order = $state['next_order'];

    // 改名配对：重命名保留原帖（文章侧 meta/详情不丢），并让它以新 slug
    // 参与下方常规字段同步。
    foreach ($state['renames'] as $new_slug => $orphan) {
        wp_update_post([
            'ID' => (int) $orphan->ID,
            'post_name' => $new_slug,
        ]);
        $posts_by_slug[$new_slug] = $orphan;
    }

    foreach ($items_by_slug as $slug => $item) {
        $title = sanitize_text_field((string) ($item['title'] ?? ''));
        $tagline = sanitize_text_field((string) ($item['tagline'] ?? ''));
        // 设置页图片字段存「附件 ID（数字）或主题文件名」：数字必须落成
        // 文章缩略图（附件已删的归零），文件名才落 seed meta。
        [$image_id, $image_file] = springapex_solution_sync_image($item);
        $existing = $posts_by_slug[$slug] ?? null;

        if ($existing === null) {
            $post_id = wp_insert_post([
                'post_type' => 'spring_solution',
                'post_status' => 'publish',
                'post_title' => $title !== '' ? $title : $slug,
                'post_name' => $slug,
                'post_excerpt' => $tagline,
                'menu_order' => $next_order++,
            ], true);
            if (is_wp_error($post_id) || (int) $post_id === 0) {
                continue;
            }
            if ($image_id > 0) {
                set_post_thumbnail((int) $post_id, $image_id);
            }
            // seed meta 一律持久化（含空串）：缺失时前台 metadata_exists
            // 回退会拿 repeater 原始值（数字串 → /assets/images/69 坏图）。
            update_post_meta((int) $post_id, '_springapex_seed_image', $image_file);
            update_post_meta((int) $post_id, '_springapex_from_content', '1');
        } elseif (get_post_meta((int) $existing->ID, '_springapex_from_content', true) === '1') {
            // 比对后再写：本函数挂在 init 上每个请求都会跑到，无条件
            // wp_update_post 会每次推进 post_modified、触发保存钩子、
            // 失效缓存。repeater 行存在即视为这张卡应有状态为 publish
            // （草稿/私有/待发/回收站一律恢复——想下架请删 repeater 行）。
            $changes = [];
            if ($title !== '' && (string) $existing->post_title !== $title) {
                $changes['post_title'] = $title;
            }
            if ((string) $existing->post_excerpt !== $tagline) {
                $changes['post_excerpt'] = $tagline;
            }
            if ($existing->post_status !== 'publish') {
                $changes['post_status'] = 'publish';
                if ($existing->post_status === 'future') {
                    // 定时发布的 post_date 在未来：不清日期 WP 会把它归一化回
                    // future，differs() 永远为真 → 每个请求取锁重写到定时点。
                    $changes['post_date'] = current_time('mysql');
                    $changes['post_date_gmt'] = gmdate('Y-m-d H:i:s');
                }
                if ($existing->post_status === 'trash') {
                    // 实测 wp_update_post 脱离 trash 时 WP 核心会剥 __trashed
                    // 后缀，但显式写回期望 slug 把恢复契约握在自己手里，
                    // 不依赖核心归一化的实现细节。
                    $changes['post_name'] = $slug;
                }
            }
            if ($changes !== []) {
                $changes['ID'] = (int) $existing->ID;
                wp_update_post($changes);
            }
            $existing_id = (int) $existing->ID;
            $current_seed = metadata_exists('post', $existing_id, '_springapex_seed_image')
                ? (string) get_post_meta($existing_id, '_springapex_seed_image', true)
                : null;
            // seed 的期望值就是派生出的文件名（数字场景为空串）：必须以
            // 「已存在的空串」收敛，缺失时前台会回退 repeater 原始值。
            if ($current_seed !== $image_file) {
                update_post_meta($existing_id, '_springapex_seed_image', $image_file);
            }
            if ($image_id > 0) {
                if ((int) get_post_thumbnail_id($existing_id) !== $image_id) {
                    set_post_thumbnail($existing_id, $image_id);
                }
            } elseif ((int) get_post_thumbnail_id($existing_id) > 0) {
                // 切回主题文件名时清缩略图：image['id'] 优先于 file，不清则永远走旧附件。
                delete_post_thumbnail($existing_id);
            }
        }
    }

    foreach ($posts as $post) {
        // 用新鲜数据判断：本轮可能刚重命名/更新过这篇文章，旧快照的
        // post_name/status 会误判（把刚改名的文章转草稿）。
        $fresh = get_post((int) $post->ID);
        if ($fresh === null) {
            continue;
        }
        $fresh_slug = (string) $fresh->post_name;
        if ($fresh->post_status === 'trash' && str_ends_with($fresh_slug, '__trashed')) {
            $meta_slug = (string) get_post_meta((int) $fresh->ID, '_wp_desired_post_slug', true);
            $fresh_slug = $meta_slug !== '' ? $meta_slug : substr($fresh_slug, 0 - strlen('__trashed'));
        }
        if (
            get_post_meta((int) $fresh->ID, '_springapex_from_content', true) === '1'
            && !isset($items_by_slug[$fresh_slug])
            && in_array($fresh->post_status, ['publish', 'future'], true)
        ) {
            wp_update_post(['ID' => (int) $fresh->ID, 'post_status' => 'draft']);
        }
    }
}
add_action('init', 'springapex_sync_solutions_from_content', 20);

function springapex_case_seed(string $slug): ?array
{
    foreach (springapex_get('case_studies.items', []) as $item) {
        if ((string) ($item['slug'] ?? '') === $slug) {
            return $item;
        }
    }
    return null;
}

function springapex_case_from_post(object $post): array
{
    $seed = springapex_case_seed((string) $post->post_name) ?? [];
    $post_id = (int) $post->ID;
    $seed_image = metadata_exists('post', $post_id, '_springapex_seed_image')
        ? (string) get_post_meta($post_id, '_springapex_seed_image', true)
        : (string) ($seed['image'] ?? '');

    return array_merge($seed, [
        'id' => $post_id,
        'slug' => (string) $post->post_name,
        'title' => get_the_title($post),
        'tagline' => (string) ($post->post_excerpt ?? ''),
        'image' => [
            'id' => (int) get_post_thumbnail_id($post),
            'file' => $seed_image,
        ],
        'content' => (string) apply_filters('the_content', (string) ($post->post_content ?? '')),
    ]);
}

function springapex_cases(): array
{
    if (defined('SPRINGAPEX_PREVIEW')) {
        return springapex_get('case_studies.items', []);
    }

    if (!function_exists('get_posts') || !post_type_exists('spring_case')) {
        return [];
    }

    $posts = get_posts([
        'post_type' => 'spring_case',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC'],
    ]);

    return array_map('springapex_case_from_post', $posts ?: []);
}

function springapex_case(string $slug): ?array
{
    $slug = sanitize_title($slug);
    if ($slug === '') {
        return null;
    }

    if (defined('SPRINGAPEX_PREVIEW')) {
        return springapex_case_seed($slug);
    }

    if (!function_exists('get_posts') || !post_type_exists('spring_case')) {
        return null;
    }

    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'spring_case',
        'post_status' => 'publish',
        'posts_per_page' => 1,
    ]);

    return isset($posts[0]) ? springapex_case_from_post((object) $posts[0]) : null;
}

function springapex_case_url(array $case): string
{
    if (!empty($case['id']) && !defined('SPRINGAPEX_PREVIEW') && function_exists('get_permalink')) {
        return (string) get_permalink((int) $case['id']);
    }
    return springapex_url('/case-studies/' . ($case['slug'] ?? '') . '/');
}

function springapex_news_seed(string $slug): ?array
{
    foreach (springapex_get('news.items', []) as $item) {
        if ((string) ($item['slug'] ?? '') !== $slug) {
            continue;
        }
        return $item;
    }
    return null;
}

function springapex_news_list(): array
{
    if (defined('SPRINGAPEX_PREVIEW')) {
        return springapex_get('news.items', []);
    }

    if (!function_exists('get_posts') || !post_type_exists('spring_news')) {
        return [];
    }

    $posts = get_posts([
        'post_type' => 'spring_news',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => ['date' => 'DESC', 'ID' => 'DESC'],
    ]);

    return array_map('springapex_news_from_post', $posts ?: []);
}

function springapex_news_from_post(object $post): array
{
    $seed = springapex_news_seed((string) $post->post_name) ?? [];
    $post_id = (int) $post->ID;
    $seed_image = metadata_exists('post', $post_id, '_springapex_seed_image')
        ? get_post_meta($post_id, '_springapex_seed_image', true)
        : ($seed['image'] ?? '');
    $news_type = (string) ($seed['news_type'] ?? 'company-news');
    $news_type_label = '';
    if (function_exists('get_the_terms')) {
        $terms = get_the_terms($post_id, 'spring_news_type');
        if (is_array($terms) && isset($terms[0])) {
            $news_type = (string) ($terms[0]->slug ?? $news_type);
            $news_type_label = (string) ($terms[0]->name ?? '');
        }
    }
    $category = function_exists('springapex_news_category_meta')
        ? springapex_news_category_meta($post_id)
        : (string) ($seed['category'] ?? '');
    if ($category === '') {
        $category = $news_type_label;
    }
    // Optional caption replacing the published date, for multi-day events. Read
    // through the meta resolver, not merged from the seed: the seed is matched by
    // slug and a new article would otherwise never be able to have one.
    $date_label = function_exists('springapex_news_date_label_meta')
        ? springapex_news_date_label_meta($post_id)
        : (string) ($seed['date_label'] ?? '');

    return array_merge($seed, [
        'id' => $post_id,
        'slug' => (string) $post->post_name,
        'title' => get_the_title($post),
        'date' => (string) get_the_date('Y-m-d', $post),
        'date_label' => $date_label,
        'category' => $category,
        'news_type' => $news_type,
        'summary' => (string) ($post->post_excerpt !== '' ? $post->post_excerpt : ($seed['summary'] ?? '')),
        'image' => [
            'id' => (int) get_post_thumbnail_id($post),
            'file' => (string) $seed_image,
        ],
        // Body and photos come from the editor, so the operator can add, caption
        // and reorder images the way any other post works. The seed's blocks and
        // gallery are matched by slug and can never exist for a new article, so
        // carrying them here would show pictures nobody can edit or delete.
        // They still feed the database-free preview/ build via
        // springapex_news_seed().
        'content' => [],
        'gallery' => [],
        'gallery_title' => '',
        // Sidebar picks, chosen from the real product entries. Falls back to the
        // seed while an article has never been saved (see news-meta.php).
        'products' => function_exists('springapex_news_products_meta')
            ? springapex_news_products_meta($post_id)
            : (array) ($seed['products'] ?? []),
    ]);
}

function springapex_news(string $slug): ?array
{
    $slug = sanitize_title($slug);
    if ($slug === '') {
        return null;
    }

    if (defined('SPRINGAPEX_PREVIEW')) {
        return springapex_news_seed($slug);
    }

    if (!function_exists('get_posts') || !post_type_exists('spring_news')) {
        return null;
    }

    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'spring_news',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'orderby' => 'ID',
        'order' => 'ASC',
    ]);

    return isset($posts[0]) ? springapex_news_from_post((object) $posts[0]) : null;
}

if (!function_exists('springapex_url')) {
    function springapex_url(string $path = '/'): string
    {
        return function_exists('home_url') ? home_url($path) : $path;
    }
}
