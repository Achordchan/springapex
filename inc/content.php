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
            'name' => 'APEX SPRING',
            'tagline' => 'SPRING MANUFACTURING EXPERT',
            'company' => 'Xuzhou APEX Spring Manufacturing Co., Ltd.',
            'email' => 'victoria@springapex.cn',
            'phone' => '+86 187 9642 2510',
            'whatsapp' => '+86 187 9642 2510',
            'address' => "Xuzhou, Jiangsu, China",
            'hours' => 'Monday – Friday, China Standard Time',
            'linkedin' => '',
        ],
        'nav' => [
            ['label' => 'About Us', 'slug' => 'about-us', 'href' => '/about/'],
            ['label' => 'Products', 'slug' => 'products', 'href' => '/products/'],
            ['label' => 'Industries', 'slug' => 'solutions', 'href' => '/solutions/'],
            ['label' => 'Custom Springs', 'slug' => 'capabilities', 'href' => '/capabilities/'],
            ['label' => 'News', 'slug' => 'news', 'href' => '/news/'],
            ['label' => 'Contact', 'slug' => 'contact', 'href' => '/contact/'],
        ],
        'home' => [
            'hero' => [
                'title' => "Precision Springs. Designed for Your Applications.",
                'subtitle' => 'From engineering design to mass production, we deliver reliable spring solutions for global industries.',
                'primary_cta' => ['label' => 'Get a Quote', 'href' => '/contact/?intent=quote', 'icon' => 'arrow-right'],
                'secondary_cta' => ['label' => 'Find Products', 'href' => '/products/', 'icon' => 'arrow-right'],
                'image' => 'hero-spring-v2.png',
            ],
            'industries' => ['AUTOMOTIVE', 'INDUSTRIAL', 'MEDICAL', 'AEROSPACE', 'RAIL', 'ENERGY'],
            'pillars' => [
                ['icon' => 'pen', 'title' => 'Your Design, Optimized', 'text' => 'We review your drawings and application needs to develop a spring that performs reliably and is ready for production.'],
                ['icon' => 'cubes', 'title' => 'Any Quantity, Consistent Quality', 'text' => 'From a single prototype to millions of pieces — advanced CNC forming delivers the same precision every time.'],
                ['icon' => 'check-shield', 'title' => 'Verified Before Shipment', 'text' => 'Every batch is dimensionally inspected and load-tested, with documentation available for your quality records.'],
                ['icon' => 'headset', 'title' => 'Fast, Clear Communication', 'text' => 'Your inquiries are answered within 24 hours. Engineering feedback and quotes follow a predictable timeline.'],
            ],
            'applications' => [
                ['slug' => 'automotive', 'title' => 'Automotive', 'image' => 'home-automotive-v2.png'],
                ['slug' => 'industrial-equipment', 'title' => 'Industrial Equipment', 'image' => 'home-industrial-v2.png'],
                ['slug' => 'medical', 'title' => 'Medical Devices', 'image' => 'home-medical-v2.png'],
                ['slug' => 'energy', 'title' => 'Energy', 'image' => 'home-energy-v2.png'],
            ],
            'process' => [
                ['icon' => 'spring', 'label' => 'Engineering'],
                ['icon' => 'cubes', 'label' => 'Prototyping'],
                ['icon' => 'gear', 'label' => 'Manufacturing'],
                ['icon' => 'check-shield', 'label' => 'Testing'],
                ['icon' => 'delivery', 'label' => 'Delivery'],
            ],
        ],
        'products' => [
            'hero' => [
                'title' => 'Products',
                'subtitle' => 'Precision springs for every need. Built to perform, built to last.',
                'primary_cta' => ['label' => 'Explore Product Range', 'href' => '#product-categories', 'icon' => 'arrow-right'],
                'image' => 'products-hero-v3.png',
            ],
            'categories' => [
                ['slug' => 'compression-springs', 'title' => 'Compression Springs', 'icon' => 'spring', 'image' => 'product-compression-detail-v4.png', 'category_image' => 'product-compression-detail-v4.png', 'featured_image' => 'product-compression-card-v3.png', 'desc' => 'Designed for dependable axial load and return force.'],
                ['slug' => 'extension-springs', 'title' => 'Extension Springs', 'icon' => 'extension', 'image' => 'product-extension-v2.png', 'category_image' => 'product-extension-category-v3.png', 'desc' => 'Built to deliver reliable tension and controlled extension.'],
                ['slug' => 'torsion-springs', 'title' => 'Torsion Springs', 'icon' => 'torsion', 'image' => 'product-torsion-v2.png', 'category_image' => 'product-torsion-category-v3.png', 'desc' => 'Engineered for repeatable rotational force and torque.'],
                ['slug' => 'disc-springs', 'title' => 'Disc Springs', 'icon' => 'disc', 'image' => 'product-disc-v2.png', 'desc' => 'High load capacity in compact assemblies.'],
                ['slug' => 'wire-forms', 'title' => 'Wire Forms', 'icon' => 'form', 'image' => 'product-wire-form-v2.png', 'desc' => 'Complex formed-wire parts manufactured to specification.'],
                ['slug' => 'die-springs', 'title' => 'Die Springs', 'icon' => 'disc', 'image' => 'product-disc-v2.png', 'desc' => 'Heavy-duty springs for stamping dies and industrial tooling.'],
                ['slug' => 'other-customized-springs', 'title' => 'Other Customized Springs', 'icon' => 'spring', 'image' => 'product-wire-v2.png', 'desc' => 'Custom spring solutions for specialized applications and requirements.'],
            ],
            'featured' => ['compression-springs', 'extension-springs', 'torsion-springs', 'disc-springs'],
        ],
        'product_details' => [
            'compression-springs' => [
                'title' => 'Compression Springs',
                'subtitle' => 'Designed to resist axial compression and deliver reliable performance.',
                'overview' => 'Compression springs are open-coil helical springs that store energy under axial load and return toward their original length when that load is removed. Diameters, wire sizes, materials, ends and surface treatments can be engineered around the application.',
                'image' => 'product-compression-detail-v4.png',
                'diagram' => 'compression-diagram-v2.png',
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
        'news' => [
            'hero' => [
                'title' => 'News',
                'subtitle' => 'Company updates, engineering insights and manufacturing news from SpringApex.',
                'image' => 'generated/springapex-news-hero-v3.webp',
            ],
            'items' => [
                [
                    'slug' => 'new-cnc-coiling-line',
                    'title' => 'New CNC Coiling Line Expands Custom Spring Capacity',
                    'date' => '2026-07-18',
                    'category' => 'Manufacturing',
                    'summary' => 'A fully automatic CNC coiling line is now in operation, supporting tighter dimensional tolerances and shorter lead times for custom compression and torsion springs.',
                    'image' => 'generated/springapex-news-cnc-coiling-v1.webp',
                    'products' => ['compression-springs', 'torsion-springs'],
                    'content' => [
                        ['type' => 'p', 'text' => 'A new fully automatic CNC coiling line has been commissioned at the SpringApex plant. The line combines servo-driven wire feeding, high-speed coiling and in-process length measurement so dimensional results stay consistent across the full production batch.'],
                        ['type' => 'p', 'text' => 'The equipment extends our manufacturing window for compression and torsion springs, covering wire diameters from 0.1 mm to 60 mm with closed, closed-and-ground and plain end options.'],
                        ['type' => 'h2', 'text' => 'What this means for customers'],
                        ['type' => 'list', 'items' => [
                            'Tighter dimensional control on batch production for the same design.',
                            'Shorter changeover time between custom jobs, which supports smaller minimum quantities.',
                            'More capacity for prototype loops before a design is released to mass production.',
                        ]],
                        ['type' => 'p', 'text' => 'If you are reviewing a custom spring design, send the drawing and operating conditions to our engineering team. We will confirm whether the required wire diameter, tolerances and end configuration fit the new line.'],
                    ],
                ],
                [
                    'slug' => 'spring-material-selection-guide',
                    'title' => 'How to Choose the Right Spring Material Before Production',
                    'date' => '2026-06-25',
                    'category' => 'Engineering Guide',
                    'summary' => 'Music wire, stainless steel, carbon steel or alloy material: a practical checklist to shortlist the right spring material from the application requirements.',
                    'image' => 'generated/springapex-news-material-selection-v1.webp',
                    'products' => ['compression-springs', 'extension-springs', 'torsion-springs'],
                    'content' => [
                        ['type' => 'p', 'text' => 'Material selection decides how a spring behaves in service: how much force it can deliver, how it responds to temperature and corrosion, and how long it keeps its original dimensions.'],
                        ['type' => 'h2', 'text' => 'Start from four questions'],
                        ['type' => 'list', 'items' => [
                            'What loads does the spring see, and how many cycles must it survive?',
                            'Is the spring exposed to moisture, chemicals or high temperature?',
                            'Are the dimensions tight enough that plating or coating thickness matters?',
                            'Does the end product require magnetic, electrical or medical-grade properties?',
                        ]],
                        ['type' => 'p', 'text' => 'Music wire offers good strength and surface quality for general industrial use. Stainless steel is chosen when corrosion resistance matters, carbon steel when cost and availability lead, and alloy materials when the application runs hot or demands fatigue resistance.'],
                        ['type' => 'p', 'text' => 'Our engineering team reviews the material proposal together with the drawing, and flags alternatives when a different wire grade would improve service life without changing the envelope.'],
                    ],
                ],
                [
                    'slug' => 'quality-system-audit-completed',
                    'title' => 'Scheduled Quality System Audit Completed Across Production',
                    'date' => '2026-05-30',
                    'category' => 'Quality',
                    'summary' => 'The annual quality management system audit covered incoming material control, in-process inspection, final load testing and batch documentation.',
                    'image' => 'generated/springapex-news-quality-audit-v1.webp',
                    'products' => ['disc-springs', 'die-springs'],
                    'content' => [
                        ['type' => 'p', 'text' => 'SpringApex completed a scheduled quality management system audit covering incoming material control, in-process inspection, final load testing and batch documentation.'],
                        ['type' => 'h2', 'text' => 'Audit scope'],
                        ['type' => 'list', 'items' => [
                            'Incoming wire verification against material certificates and diameter checks.',
                            'In-process dimensional and force checks at defined control points.',
                            'Final inspection including free length, outer diameter and load testing per drawing.',
                            'Batch traceability from wire lot to shipped carton.'],
                        ],
                        ['type' => 'p', 'text' => 'Inspection records are kept per batch and made available to customers on request, so the documentation trail matches the parts that were actually shipped.'],
                    ],
                ],
                [
                    'slug' => 'prototype-to-production-process',
                    'title' => 'From Drawing to Production: The SpringApex Prototype Process',
                    'date' => '2026-05-08',
                    'category' => 'Engineering',
                    'summary' => 'What happens after you send a spring drawing: design review, prototype confirmation and controlled release to production.',
                    'image' => 'generated/springapex-news-prototype-v1.webp',
                    'products' => ['compression-springs', 'wire-forms'],
                    'content' => [
                        ['type' => 'p', 'text' => 'Every custom spring starts with the same exchange: the customer sends a drawing or sample, and engineering confirms the design before any wire is cut.'],
                        ['type' => 'h2', 'text' => 'The four steps'],
                        ['type' => 'list', 'items' => [
                            'Design review: dimensions, loads, material and tolerances are checked against manufacturing capability.',
                            'Process confirmation: coiling method, end configuration, heat treatment and surface finish are fixed.',
                            'Prototype: a small run is produced and measured against the drawing.',
                            'Production release: after the prototype is confirmed, the job moves to controlled batch production.'],
                        ],
                        ['type' => 'p', 'text' => 'Questions during review are answered within one business day, so the clarification loop does not stall the project.'],
                    ],
                ],
                [
                    'slug' => 'export-packaging-and-traceability',
                    'title' => 'Export Packaging and Batch Traceability for Every Shipment',
                    'date' => '2026-04-16',
                    'category' => 'Logistics',
                    'summary' => 'Corrosion protection, sturdy packing and carton-level traceability are applied to every export shipment leaving the SpringApex plant.',
                    'image' => 'generated/springapex-news-export-packaging-v1.webp',
                    'products' => ['disc-springs', 'die-springs'],
                    'content' => [
                        ['type' => 'p', 'text' => 'Springs can arrive at their destination in perfect technical condition yet be rejected because of rust, damaged packaging or unclear labeling. Export packing at SpringApex is treated as part of the quality process, not an afterthought.'],
                        ['type' => 'h2', 'text' => 'Standard export packing'],
                        ['type' => 'list', 'items' => [
                            'Rust protection applied according to the agreed surface treatment and transit time.',
                            'Layer separation and cushioning so parts do not rub or deform in transit.',
                            'Cartons or wooden cases matched to weight and route, with handling labels.',
                            'Carton labels carrying the batch number, so records can be traced back to the wire lot.'],
                        ],
                        ['type' => 'p', 'text' => 'For programs with a specific packaging specification, we confirm the packing method and inspection level before production so the shipment matches the customer quality agreement.'],
                    ],
                ],
                [
                    'slug' => 'engineering-support-response-time',
                    'title' => 'Engineering Support: Initial Response Within 24 Hours',
                    'date' => '2026-03-27',
                    'category' => 'Service',
                    'summary' => 'Every engineering inquiry receives an initial response within one business day, with the information needed to move the project forward.',
                    'image' => 'generated/springapex-news-engineering-support-v1.webp',
                    'products' => ['compression-springs', 'extension-springs'],
                    'content' => [
                        ['type' => 'p', 'text' => 'A slow first reply is often what stalls a spring project. SpringApex commits to an initial engineering response within 24 hours on business days, even when the final quotation needs more review time.'],
                        ['type' => 'h2', 'text' => 'What to include in your inquiry'],
                        ['type' => 'list', 'items' => [
                            'A drawing or sample with key dimensions and tolerances.',
                            'Operating conditions: load, travel, cycles and environment.',
                            'Material or surface treatment requirements, if already specified.',
                            'Target quantity and delivery date.'],
                        ],
                        ['type' => 'p', 'text' => 'You can reach engineering by email or WhatsApp. Inquiries that already include operating conditions usually receive a more precise response in the first round.'],
                    ],
                ],
            ],
        ],
        'about' => [
            'hero' => [
                'title' => 'About SpringApex',
                'subtitle' => 'Precision springs. Purpose-built performance. Trusted worldwide.',
                'cta' => ['label' => 'Get to Know Us', 'href' => '#story', 'icon' => 'arrow-right'],
                'image' => 'about-building-v3.png',
            ],
            'stats' => [
                ['icon' => 'users', 'value' => '2001', 'label' => 'Founded'],
                ['icon' => 'factory', 'value' => '3', 'label' => 'Production Facilities'],
                ['icon' => 'spring', 'value' => '120+', 'label' => 'Employees'],
                ['icon' => 'globe', 'value' => '2,000+', 'label' => 'Customers'],
            ],
            'story' => [
                'eyebrow' => 'OUR STORY',
                'title' => "Built on Precision.\nDriven by Purpose.",
                'text' => 'SpringApex was founded on a simple belief: precision components drive extraordinary outcomes. From our early days to global partnerships today, we remain committed to delivering springs that perform—every time.',
                'image' => 'about-story-springs-v5.png',
                'values' => [
                    ['icon' => 'target', 'title' => 'Customer Focused', 'text' => 'We listen, collaborate and engineer around the real application.'],
                    ['icon' => 'award', 'title' => 'Quality First', 'text' => 'Every part, every batch and every shipment is handled with care.'],
                    ['icon' => 'leaf', 'title' => 'Continuous Improvement', 'text' => 'We invest in equipment, people and better production methods.'],
                ],
            ],
        ],
        'capabilities' => [
            'hero' => [
                'title' => 'Capabilities',
                'subtitle' => 'Engineering and manufacturing built around repeatable performance.',
                'cta' => ['label' => 'Upload Your Drawing', 'href' => '/contact/?intent=drawing', 'icon' => 'upload'],
                'image' => 'generated/springapex-capabilities-hero-v2.webp',
            ],
            'intro' => [
                'title' => 'Our Capabilities',
                'text' => 'Engineered to meet demanding applications with consistency, care and traceable quality.',
            ],
            'items' => [
                ['icon' => 'cnc', 'title' => 'Precision Engineering', 'text' => 'Parameter analysis and manufacturable designs built to application requirements.'],
                ['icon' => 'qc', 'title' => 'CNC Coiling', 'text' => 'Controlled forming processes for repeatable dimensions and force.'],
                ['icon' => 'heat', 'title' => 'Heat Treatment & Surface', 'text' => 'Material and finishing options selected for durability and corrosion resistance.'],
                ['icon' => 'search', 'title' => 'Testing & Quality', 'text' => 'Inspection and validation throughout production and shipment.'],
            ],
        ],
        'contact' => [
            'hero' => [
                'title' => 'Contact Us',
                'subtitle' => "We're here to support your projects.",
                'points' => [
                    ['icon' => 'headset', 'title' => 'Fast, expert support', 'text' => 'Get answers from our engineering team.'],
                    ['icon' => 'shield', 'title' => 'Confidential & secure', 'text' => 'Your drawings and project details are handled with care.'],
                ],
                'image' => 'contact-springs-v2.png',
            ],
            'inquiry_types' => ['Request a Quote', 'Upload a Drawing', 'Technical Support', 'Custom Design', 'Catalog / Technical Documents', 'Supplier Qualification', 'Feedback / Suggestions', 'Partnership', 'Other'],
            'map_image' => 'map-xuzhou-v2.png',
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

    return array_merge($seed, [
        'id' => $post_id,
        'slug' => $slug,
        'title' => get_the_title($post),
        'desc' => (string) ($post->post_excerpt ?? ''),
        'subtitle' => (string) $subtitle,
        'overview' => (string) ($post->post_content ?? ''),
        'image' => $database_image,
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
        return array_merge($category, $details[$slug] ?? [
            'subtitle' => $category['desc'] ?? '',
            'overview' => $category['desc'] ?? '',
            'specs' => [],
            'materials' => [],
            'applications' => [],
            'catalog_url' => '',
        ]);
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

    return array_merge($seed, [
        'id' => $post_id,
        'slug' => (string) $post->post_name,
        'title' => get_the_title($post),
        'date' => (string) get_the_date('Y-m-d', $post),
        'category' => metadata_exists('post', $post_id, '_springapex_news_category')
            ? (string) get_post_meta($post_id, '_springapex_news_category', true)
            : (string) ($seed['category'] ?? ''),
        'summary' => (string) ($post->post_excerpt !== '' ? $post->post_excerpt : ($seed['summary'] ?? '')),
        'image' => [
            'id' => (int) get_post_thumbnail_id($post),
            'file' => (string) $seed_image,
        ],
        'content' => [],
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
