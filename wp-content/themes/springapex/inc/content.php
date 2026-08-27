<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/content-enhancements.php';

function springapex_product_detail_media_defaults(): array
{
    return [
        'quality' => [
            'load_test' => [
                'image' => 'product-detail/compression-quality-load-test.jpg',
                'alt' => 'Spring load testing equipment operated by a NorenSpring technician',
            ],
            'dimensional_inspection' => [
                'image' => 'quality-inspection-original.jpg',
                'alt' => 'Dimensional inspection with a digital caliper',
            ],
            'material_analysis' => [
                'image' => 'product-detail/compression-quality-material-lab.jpg',
                'alt' => 'Material analysis in the NorenSpring laboratory',
            ],
        ],
        'delivery' => [
            'protected_packaging' => 'product-detail/compression-packed-springs.jpg',
            'custom_crates' => 'product-detail/compression-custom-crates.jpg',
            'palletized_labelled' => 'product-detail/compression-parts-racks.jpg',
            'global_delivery' => 'product-detail/compression-palletized.jpg',
        ],
    ];
}

function springapex_content(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }

    $data = [
        'brand' => [
            'name' => 'NorenSpring',
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
                'mobile_image' => 'hero-spring-mobile-v1.png',
            ],
            'video_dialog_title' => 'NorenSpring Manufacturing',
            'sections' => [
                'products' => [
                    'eyebrow' => 'PRODUCT RANGE',
                    'title' => 'Featured spring products for demanding applications.',
                    'text' => 'Whether you need a standard compression spring or a complex wire form, start here.',
                    'action_label' => 'Explore all products',
                    'action_href' => '/products/',
                ],
                'why' => [
                    'eyebrow' => 'WHY NORENSPRING',
                    'title' => 'What You Get When You Work With Us',
                    'text' => 'Choosing a spring supplier is choosing a manufacturing partner. Here is what that partnership delivers.',
                    'action_label' => 'Our Capabilities',
                    'action_href' => '/capabilities/',
                ],
                'process' => [
                    'eyebrow' => 'HOW WE WORK',
                    'title' => 'From Wire to Performance',
                    'text' => 'Every order follows the same disciplined sequence — so quality is built in, not inspected in.',
                    'note' => 'A proven process. Precision quality. Reliable delivery.',
                ],
                'industries' => [
                    'eyebrow' => 'INDUSTRIES WE SERVE',
                    'title' => 'Springs built for the demands of your industry.',
                    'text' => 'Each sector has unique load, environment and compliance requirements. We engineer around them.',
                    'action_label' => 'View All Applications',
                    'action_href' => '/solutions/',
                ],
            ],
            'industries' => ['AUTOMOTIVE', 'INDUSTRIAL', 'MEDICAL', 'AEROSPACE', 'RAIL', 'ENERGY'],
            'pillars' => [
                ['icon' => 'pen', 'title' => 'Production-Ready Design', 'text' => 'Drawing review for reliable, manufacturable spring designs.'],
                ['icon' => 'cubes', 'title' => 'Prototype to Production', 'text' => 'Repeatable CNC precision from samples to volume orders.'],
                ['icon' => 'check-shield', 'title' => 'Verified Before Shipment', 'text' => 'Inspection, load testing and documentation for every batch.'],
                ['icon' => 'headset', 'title' => 'Fast Engineering Support', 'text' => 'Clear feedback and quotations within 24 hours.'],
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
                'drawing_cta' => ['label' => 'Send a Drawing', 'href' => '/contact/?intent=drawing', 'icon' => 'upload'],
                'image' => 'products-hero-v3.png',
                'mobile_image' => 'products-hero-mobile-v1.png',
            ],
            'mega_menu' => [
                'feature_image' => 'product-compression-menu-v2.png',
            ],
            'detail_media' => springapex_product_detail_media_defaults(),
            'entry' => [
                'eyebrow' => 'START HERE',
                'title' => 'Choose How to Start',
                'text' => 'Describe your application or send a drawing for review.',
                'items' => [
                    ['icon' => 'gear', 'title' => 'Describe Your Application', 'text' => 'Share the load, space and motion requirements for engineering guidance.', 'href' => '/contact/?intent=solution'],
                    ['icon' => 'upload', 'title' => 'Upload Drawing for Quote', 'text' => 'Send a drawing or specification for review and quotation.', 'href' => '/contact/?intent=drawing'],
                    ['icon' => 'spring', 'title' => 'Find by Product Type', 'text' => 'Browse spring families by load direction and component type.', 'href' => '/products/#product-families'],
                ],
            ],
            'range' => [
                'eyebrow' => 'PRODUCT RANGE',
                'title' => 'Spring families for every load and motion.',
                'text' => 'Compare force direction, space, material and operating conditions.',
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
            'cta' => [
                'title' => 'Have a specific application in mind?',
                'text' => "We'll help you find the right spring solution for your needs.",
                'action_label' => 'Contact Our Engineers',
                'action_href' => '/contact/?intent=engineer',
                'image' => 'solutions-cta-springs-v5.png',
                'image_alt' => 'Assorted precision springs',
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
            'follow' => [
                'eyebrow' => 'FOLLOW NORENSPRING',
                'title' => 'Keep up with NorenSpring.',
                'text' => 'Follow exhibition news, manufacturing updates and company developments through our official channels.',
                'action_label' => 'View Official Channels',
                'action_href' => '/about/#official-channels',
            ],
            'items' => [
                [
                    'slug' => 'manufacturing-expo-bangkok-2024',
                    'title' => 'NorenSpring at Manufacturing Expo 2024 in Bangkok',
                    'date' => '2024-06-20',
                    'date_label' => 'June 17–20, 2024',
                    'category' => 'Exhibition',
                    'news_type' => 'exhibitions',
                    'summary' => 'NorenSpring presented precision spring products and met visitors at Manufacturing Expo 2024 in Bangkok, Thailand.',
                    'image' => 'news/manufacturing-expo-bangkok-2024/hero.jpg',
                    'products' => ['compression-springs', 'torsion-springs', 'wire-forms'],
                    'content' => [
                        ['type' => 'p', 'text' => 'From June 17 to 20, 2024, NorenSpring exhibited at Manufacturing Expo in Bangkok, Thailand. The event brought manufacturing teams and suppliers together at BITEC to review production technologies and component solutions.'],
                        ['type' => 'p', 'text' => 'At the NorenSpring booth, visitors reviewed spring samples and discussed custom applications with our team. The conversations focused on turning drawings and operating requirements into practical production plans.'],
                        ['type' => 'h2', 'text' => 'Discussions at the booth'],
                        ['type' => 'list', 'items' => [
                            'Custom spring geometry, load and installation requirements.',
                            'Material and surface-treatment options for different operating environments.',
                            'Prototype, production and quality-documentation requirements.',
                        ]],
                        ['type' => 'h2', 'text' => 'Thank you for visiting'],
                        ['type' => 'p', 'text' => 'Thank you to everyone who visited the NorenSpring booth and shared their application requirements. Our team continues to support follow-up discussions with drawing review, product selection and quotation preparation.'],
                    ],
                    'gallery_title' => 'Manufacturing Expo 2024 in Bangkok',
                    'gallery' => [
                        ['image' => 'news/manufacturing-expo-bangkok-2024/venue.jpg', 'alt' => 'Manufacturing Expo 2024 entrance at BITEC in Bangkok', 'caption' => 'Manufacturing Expo 2024 at BITEC, Bangkok'],
                        ['image' => 'news/manufacturing-expo-bangkok-2024/visitor-discussion.jpg', 'alt' => 'NorenSpring team discussing spring samples with a visitor', 'caption' => 'Spring samples and application discussion'],
                        ['image' => 'news/manufacturing-expo-bangkok-2024/project-meeting.jpg', 'alt' => 'Visitors meeting with the NorenSpring team at the exhibition booth', 'caption' => 'Project requirements discussed at the booth'],
                        ['image' => 'news/manufacturing-expo-bangkok-2024/product-consultation.jpg', 'alt' => 'NorenSpring representative presenting products at Manufacturing Expo', 'caption' => 'Product consultation during the exhibition'],
                        ['image' => 'news/manufacturing-expo-bangkok-2024/technical-review.jpg', 'alt' => 'Technical discussion at the NorenSpring exhibition counter', 'caption' => 'Technical requirements review'],
                        ['image' => 'news/manufacturing-expo-bangkok-2024/exhibition-team.jpg', 'alt' => 'NorenSpring exhibition team at Manufacturing Expo 2024', 'caption' => 'The NorenSpring exhibition team'],
                    ],
                ],
            ],
        ],
        'about' => [
            'hero' => [
                'title' => 'About NorenSpring',
                'subtitle' => 'Precision spring manufacturing since 2001.',
                'image' => 'about-building-v3.png',
                'mobile_image' => 'about-hero-mobile-v1.png',
            ],
            'company_video' => [
                'title' => 'Inside NorenSpring',
                'youtube_id' => '5LUKHmIHPDY',
            ],
            'brand_window' => [
                'image' => 'generated/springapex-factory-floor-v1.webp',
                'aria_label' => 'NorenSpring precision manufacturing',
            ],
            'stats' => [
                ['icon' => 'users', 'value' => '2001', 'label' => 'Founded'],
                ['icon' => 'factory', 'value' => '3', 'label' => 'Production Facilities'],
                ['icon' => 'spring', 'value' => '120+', 'label' => 'Employees'],
                ['icon' => 'globe', 'value' => '2,000+', 'label' => 'Customers'],
            ],
            'why_choose' => [
                'eyebrow' => 'WHY NORENSPRING',
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
            'global_support' => [
                'wordmark' => 'GLOBAL',
                'eyebrow' => 'GLOBAL SUPPORT',
                'title' => 'One manufacturing base. Connected project support.',
                'text' => 'International projects are coordinated from Xuzhou through direct engineering review, production communication and delivery documentation.',
                'image' => 'about-global-support-map-v1.png',
                'image_alt' => 'International project support coordinated from Xuzhou, China',
                'location' => 'Xuzhou, China',
                'action_label' => 'View Contact Network',
                'action_href' => '/contact/#contact-network',
            ],
            'official_channels' => [
                'eyebrow' => 'OFFICIAL CHANNELS',
                'title' => 'Follow NorenSpring.',
                'text' => 'Only confirmed public profile links are shown here.',
                'rail_label' => 'FOLLOW',
                'facebook_text' => 'Company and manufacturing updates.',
                'instagram_text' => 'Products, facilities and events.',
                'youtube_text' => 'Watch the NorenSpring company film.',
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
                'image_alt' => 'Spring engineering review workspace',
                'action_label' => 'Send Your Project Details',
                'action_href' => '/contact/?intent=drawing',
                'items' => [
                    ['icon' => 'pen', 'title' => 'Geometry or Drawing', 'text' => 'Dimensions, available space, end details and installation constraints.'],
                    ['icon' => 'spring', 'title' => 'Load & Movement', 'text' => 'Required force or torque at the working position and expected travel.'],
                    ['icon' => 'heat', 'title' => 'Material & Environment', 'text' => 'Temperature, corrosion exposure, cleanliness and service-life expectations.'],
                    ['icon' => 'form', 'title' => 'Quantity & Records', 'text' => 'Prototype and production volume, inspection reports and traceability needs.'],
                ],
            ],
            'verification' => [
                'title' => 'Verification matched to your drawing and application.',
                'image' => 'product-detail/compression-dimension-guide-v2.png',
                'image_alt' => 'Spring dimension reference diagram with wire diameter, outer diameter and free length callouts',
            ],
        ],
        'manufacturing_videos' => [
            'eyebrow' => 'MANUFACTURING VIDEOS',
            'title' => 'See how precision is built.',
            'intro' => 'Explore the processes, inspection and testing behind repeatable spring production.',
            'hero_image' => 'manufacturing-videos/hero-engineering-studio-v2.webp',
            'hero_mobile_image' => '',
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
            'inquiry_types' => ['Request a Quote', 'Upload a Drawing', 'Technical Support', 'Custom Design', 'Catalog / Technical Documents', 'Supplier Qualification', 'Feedback / Suggestions', 'Partnership', 'Other'],
            'form' => [
                'title' => 'Send an Inquiry',
                'text' => 'Tell us about your project. Our team will get back to you promptly.',
                'submit_label' => 'Send Inquiry',
                'direct_label' => 'Or contact us directly',
            ],
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
    $mega_menu = $meta_or_seed('_springapex_mega_menu', '1');
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

    $card_description = trim((string) $subtitle) !== ''
        ? (string) $subtitle
        : (string) ($post->post_excerpt ?? '');

    return array_merge($seed, [
        'id' => $post_id,
        'slug' => $slug,
        'title' => get_the_title($post),
        'desc' => $card_description,
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
        'mega_menu' => (bool) $mega_menu,
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
        // Upper bound keeps the header/footer queries sane if the catalog ever
        // grows pathologically; a spring catalog stays far below this.
        'posts_per_page' => 200,
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
    return array_values(array_filter($products, static fn(array $product): bool => !empty($product['featured'])));
}

function springapex_mega_menu_products(?array $products = null): array
{
    if (defined('SPRINGAPEX_PREVIEW')) {
        return array_slice($products ?? springapex_products(), 0, 12);
    }

    $products ??= springapex_products();
    $eligible = array_values(array_filter(
        $products,
        static fn(array $product): bool => !empty($product['mega_menu'])
    ));

    // The header panel is a fixed-height two-column list; cap the menu to what
    // stays browsable and let /products/ carry the full range.
    return array_slice($eligible, 0, 12);
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
