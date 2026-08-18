<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/solution-content.php';

function springapex_content_enhancements(): array
{
    return [
        'company' => [
            'profile' => [
                'eyebrow' => 'ABOUT APEXSPRING',
                'home_title' => 'Precision spring manufacturing, built for long-term production.',
                'home_text' => 'ApexSpring is a women-owned precision spring manufacturer based in Xuzhou, China. Since 2001, we have supported projects from application review and prototyping through controlled production, inspection and export delivery.',
                'home_support' => 'We manufacture standard and custom spring components for automotive, industrial equipment, medical, energy and other demanding applications.',
                'title' => 'Precision manufacturing, from review to delivery.',
                'paragraphs' => [
                    'Xuzhou Apex Spring Manufacturing Co., Ltd, established in 2001 and headquartered in Xuzhou, China, is a comprehensive spring manufacturer integrating R&D, engineering design, and precision manufacturing. The group includes Jiangsu APEX Spring Manufacturing Co., Ltd., Jiangsu Chaoxing Spring Technology Co., Ltd., and Jiangsu Chaofan Spring Manufacturing Co., Ltd. Unlike traditional manufacturers, we focus on engineered spring solutions and performance optimization. With strong capabilities in design development and parameter analysis, we provide customized, high-precision spring solutions tailored to real application requirements across automotive, industrial equipment, and precision engineering sectors.',
                    'The company operates multiple production facilities with a combined production area of 12,000 square meters and more than 120 employees, and offers a fully integrated production chain from raw material processing to finished products, covering wire diameters from 0.1 mm to 80 mm, with an annual output value of approximately RMB 150 million.',
                    'Our product range includes precision small springs, special-shaped springs, compression, extension, and torsion springs, automotive suspension and braking springs, hot-coiled large springs, heavy-duty springs, as well as wire forms and various customized spring products. These products are widely used in automotive & mobility, railway & transportation, construction & heavy equipment, agricultural machinery, and medical & precision devices, serving leading companies and supply chain partners across these industries.',
                    'Our products have been exported to dozens of countries and regions, including Switzerland, France, Thailand, Singapore, and Malaysia. In the domestic market, we maintain long-term cooperation with over 2,000 stable customers, demonstrating strong market recognition and reliability. The company is certified to IATF 16949, ISO 13485, ISO 9001, ISO 14001, and ISO 45001, and is supported by a comprehensive quality control system and in-house testing laboratory to ensure consistent performance and reliability under demanding operating conditions. We don’t just manufacture springs — we engineer solutions for performance and reliability.',
                ],
                'image' => 'facility-aerial-original.webp',
                'image_alt' => 'ApexSpring manufacturing facility in Xuzhou, Jiangsu, China',
                'highlights' => [
                    ['value' => '2001', 'label' => 'Founded'],
                    ['value' => 'Xuzhou, China', 'label' => 'Manufacturing base'],
                    ['value' => '120+', 'label' => 'Employees'],
                ],
            ],
            'facts' => [
                ['icon' => 'users', 'value' => '2001', 'label' => 'Founded', 'detail' => 'Spring manufacturing experience built in Xuzhou.'],
                ['icon' => 'factory', 'value' => '3', 'label' => 'Production Facilities', 'detail' => 'Manufacturing capacity organized across three facilities.'],
                ['icon' => 'users', 'value' => '120+', 'label' => 'Employees', 'detail' => 'Engineering, production, quality and export support teams.'],
                ['icon' => 'spring', 'value' => 'USD 30+ million', 'label' => 'Annual Output', 'detail' => 'Precision spring components produced each year.'],
                ['icon' => 'form', 'value' => '0.1–80 mm', 'label' => 'Wire Diameter Range', 'detail' => 'From fine-wire components to heavy-duty spring applications.'],
            ],
            'quality' => [
                'eyebrow' => 'QUALITY SYSTEMS',
                'title' => 'Quality built into production.',
                'text' => 'Controlled processes, inspection and traceable records.',
                'standards' => [
                    ['name' => 'IATF 16949', 'scope' => 'Automotive quality management', 'document' => 'certificates/iatf-16949-certificate.jpg'],
                    ['name' => 'ISO 9001', 'scope' => 'Quality management system', 'document' => 'certificates/iso-9001-certificate.jpg'],
                    ['name' => 'ISO 13485', 'scope' => 'Medical device quality management', 'document' => 'certificates/iso-13485-certificate.jpg'],
                    ['name' => 'ISO 14001', 'scope' => 'Environmental management system', 'document' => 'certificates/iso-14001-certificate.jpg'],
                    ['name' => 'ISO 45001', 'scope' => 'Occupational health and safety', 'document' => 'certificates/iso-45001-certificate.jpg'],
                ],
                'certificates' => [
                    [
                        'name' => 'IATF 16949',
                        'scope' => 'Automotive quality management',
                        'valid_until' => 'Valid until February 4, 2028',
                        'image' => 'certificates/iatf-16949.png',
                        'document' => 'certificates/iatf-16949-certificate.jpg',
                    ],
                    [
                        'name' => 'ISO 9001',
                        'scope' => 'Quality management system',
                        'valid_until' => 'Valid until December 11, 2026',
                        'image' => 'certificates/iso-9001.png',
                        'document' => 'certificates/iso-9001-certificate.jpg',
                    ],
                    [
                        'name' => 'ISO 13485',
                        'scope' => 'Medical device quality management',
                        'valid_until' => 'Valid until March 26, 2028',
                        'image' => 'certificates/iso-13485.png',
                        'document' => 'certificates/iso-13485-certificate.jpg',
                    ],
                    [
                        'name' => 'ISO 14001',
                        'scope' => 'Environmental management system',
                        'valid_until' => 'Valid until April 17, 2028',
                        'image' => 'certificates/iso-14001.png',
                        'document' => 'certificates/iso-14001-certificate.jpg',
                    ],
                    [
                        'name' => 'ISO 45001',
                        'scope' => 'Occupational health and safety',
                        'valid_until' => 'Valid until April 17, 2028',
                        'image' => 'certificates/iso-45001.png',
                        'document' => 'certificates/iso-45001-certificate.jpg',
                    ],
                ],
                'image' => 'generated/springapex-quality-lab-v1.webp',
            ],
            'timeline' => [
                [
                    'year' => '2001',
                    'title' => 'ApexSpring founded',
                    'text' => 'Production began in Xuzhou.',
                    'image' => 'generated/about-timeline-2001-workshop-v1.png',
                    'alt' => 'Representative early precision spring manufacturing workshop',
                ],
                [
                    'year' => 'Growth',
                    'title' => 'Production expanded',
                    'text' => 'Three facilities increased capacity.',
                    'image' => 'generated/about-timeline-growth-cnc-v1.png',
                    'alt' => 'Representative modern CNC spring coiling production line',
                ],
                [
                    'year' => 'Today',
                    'title' => 'Global project support',
                    'text' => 'Engineering and export support worldwide.',
                    'image' => 'generated/about-timeline-today-support-v1.png',
                    'alt' => 'Representative global engineering team reviewing spring drawings and samples',
                ],
            ],
            'gallery' => [
                ['image' => 'generated/springapex-factory-floor-v1.webp', 'alt' => 'Representative precision spring manufacturing workflow', 'caption' => 'Representative manufacturing workflow'],
                ['image' => 'generated/springapex-quality-lab-v1.webp', 'alt' => 'Representative spring inspection setup', 'caption' => 'Representative inspection setup'],
            ],
            'markets' => [
                'title' => 'Global support from Xuzhou.',
                'text' => 'Drawing review, documentation and shipment support.',
                'regions' => ['North America', 'Europe', 'Asia-Pacific'],
            ],
        ],
        'contact_network' => [
            'eyebrow' => 'GLOBAL CONTACT NETWORK',
            'title' => 'Engineering support, wherever your project begins.',
            'facility_image' => 'facility-aerial-original.webp',
            'map_image' => 'contact/contact-world-map-v1.png',
            'headquarters' => [
                'title' => 'Who we are',
                'location' => '',
                'text' => 'ApexSpring is a precision spring manufacturer delivering reliable spring solutions from design and prototyping to mass production. Our Xuzhou facility combines advanced machinery with experienced engineering support for customers across diverse industries.',
            ],
            'facts' => [
                ['icon' => 'factory', 'value' => '12,000 m²', 'label' => 'Production facility'],
                ['icon' => 'cnc', 'value' => '3', 'label' => 'Production lines'],
                ['icon' => 'users', 'value' => '120+', 'label' => 'Employees'],
                ['icon' => 'pen', 'value' => '28', 'label' => 'Engineering team'],
            ],
            'markers' => [
                ['label' => 'North America · Mexico', 'left' => '21.1%', 'top' => '38.5%', 'label_side' => 'right', 'label_y' => '0px'],
                ['label' => 'Europe · 7 partners', 'left' => '52.8%', 'top' => '24.5%', 'label_side' => 'right', 'label_y' => '0px'],
                ['label' => 'Asia Pacific · India', 'left' => '67.8%', 'top' => '46.5%', 'label_side' => 'right', 'label_y' => '0px'],
                ['label' => 'Asia Headquarters · Xuzhou', 'left' => '78.7%', 'top' => '36.6%', 'label_side' => 'left', 'label_y' => '0px', 'headquarters' => true],
            ],
            'regions' => [
                [
                    'slug' => 'headquarters',
                    'label' => 'Headquarters',
                    'locations' => [
                        [
                            'name' => 'China (Xuzhou)',
                            'detail' => 'Asia Headquarters',
                            'company' => 'Xuzhou APEX Spring Manufacturing Co., Ltd.',
                            'phone' => '+86 187 9642 2510',
                            'email' => 'victoria@springapex.cn',
                            'address' => 'No. 15, Zhongnan Gaoke, Luji Town, Tongshan District, Xuzhou City, Jiangsu Province, China 221112',
                        ],
                    ],
                ],
                [
                    'slug' => 'asia-pacific',
                    'label' => 'Asia Pacific',
                    'locations' => [
                        [
                            'name' => 'India',
                            'company' => 'Alcomex Springs Pvt. Ltd.',
                            'phone' => '+91 02137 666 102',
                            'email' => 'info@alcomex.in',
                            'address' => 'Plot No. 3362, Talegaon Dhamdhere, Maharashtra, Pune 412208, India',
                            'website' => 'https://www.alcomex.com/',
                        ],
                    ],
                ],
                [
                    'slug' => 'europe',
                    'label' => 'Europe',
                    'locations' => [
                        [
                            'name' => 'Denmark',
                            'company' => 'Lesjøfors A/S',
                            'phone' => '+45 4695 6100',
                            'email' => 'info.bby@lesjoforsab.com',
                            'address' => 'Ringager 9-11, Brøndby, DK-2605, Denmark',
                            'website' => 'https://shop.lesjofors.com/dk/da-DK/',
                        ],
                        [
                            'name' => 'Finland',
                            'company' => 'Lesjöfors Springs',
                            'phone' => '+358 207 649 340',
                            'email' => 'info.abo@lesjoforsab.com',
                            'address' => 'Hallimestarinkatu 7, Kaarina, SF-20780, Finland',
                            'website' => 'https://shop.lesjofors.com/fi/fi-FI/',
                        ],
                        [
                            'name' => 'France',
                            'company' => 'Ressorts Lacroix',
                            'phone' => '+33 2 38 44 32 03',
                            'email' => 'commercial@ressorts-lacroix.com',
                            'address' => 'Zone d’activité Synergie, 4ième avenue, 45130 Meung-sur-Loire, France',
                            'website' => 'https://ressorts-lacroix.com/',
                        ],
                        [
                            'name' => 'Germany',
                            'company' => 'Lesjöfors Industrial Springs & Pressings GmbH',
                            'phone' => '+49 233 450 17 0',
                            'email' => 'automotive.hag@lesjoforsab.com',
                            'address' => 'Heidestraße 115, Velbert, DE-42549, Germany',
                            'website' => 'https://www.lesjofors.com/en/industries/automotive-aftermarket/',
                        ],
                        [
                            'name' => 'Latvia',
                            'company' => 'LSEZ SIA Lesjofors Gas Springs',
                            'phone' => '+371 2578 4100',
                            'email' => 'info.lep@lesjoforsab.com',
                            'address' => 'Dūņu street 4, Liepāja, LV-3401, Latvia',
                        ],
                        [
                            'name' => 'Netherlands',
                            'company' => 'Alcomex Veren B.V.',
                            'phone' => '+31 226 351122',
                            'email' => 'info@alcomex.nl',
                            'address' => 'De Veken 109, 1716 KG Opmeer, Netherlands',
                            'website' => 'https://www.alcomex.com/',
                        ],
                        [
                            'name' => 'Poland',
                            'company' => 'Alcomex Springs-Pol Sp. z o.o.',
                            'phone' => '+48 22 615 83 67',
                            'email' => 'biuro@alcomex.pl',
                            'address' => 'Ul. Okólna 45 hala D, Marki, 05-270, Poland',
                            'website' => 'https://www.alcomex.com/springs/door-springs/',
                        ],
                    ],
                ],
                [
                    'slug' => 'north-america',
                    'label' => 'North America',
                    'locations' => [
                        [
                            'name' => 'Mexico',
                            'company' => 'Lesjöfors Springs America, Inc.',
                            'phone' => '011-631 104 13 69',
                            'email' => 'info.us@lesjoforsab.com',
                            'address' => '911 North Industrial Park Road, C/O Gamas International, Nogales, MX-85621, Mexico',
                        ],
                    ],
                ],
                [
                    'slug' => 'other-regions',
                    'label' => 'Other Regions',
                    'locations' => [
                        [
                            'name' => 'Global programs',
                            'detail' => 'Coordinated from Xuzhou, China',
                            'company' => 'ApexSpring',
                            'phone' => '+86 187 9642 2510',
                            'email' => 'victoria@springapex.cn',
                            'address' => 'Xuzhou, Jiangsu Province, China',
                        ],
                    ],
                ],
            ],
        ],
        'manufacturing_process' => [
            ['icon' => 'pen', 'step' => '01', 'title' => 'Requirement Review', 'text' => 'Load, movement, space, environment and drawing requirements are checked before production planning.', 'image' => 'manufacturing-videos/application-engineering-v1.webp'],
            ['icon' => 'form', 'step' => '02', 'title' => 'Prototype & Tooling', 'text' => 'Geometry and process settings are prepared for samples or the first controlled production run.', 'image' => 'manufacturing-videos/machine-setup-v1.webp'],
            ['icon' => 'cnc', 'step' => '03', 'title' => 'Forming & Coiling', 'text' => 'CNC coiling and forming processes control dimensions, pitch, hooks and wire geometry.', 'image' => 'manufacturing-videos/featured-cnc-coiling-v1.webp'],
            ['icon' => 'heat', 'step' => '04', 'title' => 'Heat & Surface Process', 'text' => 'Stress relief, heat treatment and surface options are selected for material and service conditions.', 'image' => 'manufacturing-videos/manufacturing-processes-v1.webp'],
            ['icon' => 'search', 'step' => '05', 'title' => 'Inspection & Validation', 'text' => 'Dimensions, load and application-critical characteristics are checked against the agreed control plan.', 'image' => 'manufacturing-videos/quality-inspection-v1.webp'],
            ['icon' => 'delivery', 'step' => '06', 'title' => 'Packing & Delivery', 'text' => 'Parts are protected, identified and prepared with the documentation required for shipment.', 'image' => 'manufacturing-videos/packaging-delivery-v1.webp'],
        ],
        'product_selection' => [
            'title' => 'Choose by load direction.',
            'text' => 'Match force, space and service conditions to the right spring family.',
            'items' => [
                ['icon' => 'spring', 'title' => 'Axial Compression', 'text' => 'Compression, die and disc springs resist force as the available length decreases.'],
                ['icon' => 'extension', 'title' => 'Axial Tension', 'text' => 'Extension springs store energy as hooks or loops are pulled apart.'],
                ['icon' => 'torsion', 'title' => 'Rotational Torque', 'text' => 'Torsion springs act around a centerline and control angular movement.'],
                ['icon' => 'form', 'title' => 'Custom Motion', 'text' => 'Wire forms, pins and specialty geometries transfer force through application-specific shapes.'],
            ],
        ],
        'resources' => [
            'hero' => [
                'title' => 'Download Center',
                'subtitle' => 'Company, product and industry brochures in one place.',
                'mobile_image' => 'resources-hero-mobile-v1.png',
            ],
            'items' => [
                [
                    'type' => 'Specification Guide',
                    'title' => 'What to Include in a Spring RFQ',
                    'summary' => 'The dimensions, loads, materials, environment and documentation that help engineering review move faster.',
                    'points' => ['Part drawing or reference geometry', 'Load at working height or angle', 'Material and operating environment', 'Quantity, cycle life and required documents'],
                ],
                [
                    'type' => 'Material Guide',
                    'title' => 'Selecting Spring Wire and Surface Treatment',
                    'summary' => 'A practical comparison of strength, corrosion exposure, temperature and finishing requirements.',
                    'points' => ['Mechanical load and fatigue demand', 'Corrosion and wash-down exposure', 'Operating temperature', 'Coating, passivation and cleanliness'],
                ],
                [
                    'type' => 'Quality Guide',
                    'title' => 'Planning Inspection and Validation',
                    'summary' => 'Define critical dimensions, force checkpoints, sample plans and traceability before production.',
                    'points' => ['Critical-to-function characteristics', 'Load or torque verification points', 'Sampling and reporting expectations', 'Material and batch traceability'],
                ],
                [
                    'type' => 'Design Guide',
                    'title' => 'From Prototype to Repeat Production',
                    'summary' => 'How design review, samples, approval and controlled production fit together.',
                    'points' => ['Application and tolerance review', 'Prototype or first-article samples', 'Approval criteria and control plan', 'Repeat production and change control'],
                ],
                [
                    'type' => 'Application Guide',
                    'title' => 'Matching Spring Type to Load Direction',
                    'summary' => 'A quick route from the required movement to compression, extension, torsion, disc or wire-form options.',
                    'points' => ['Compression and return force', 'Tension and stored energy', 'Rotation and torque', 'Compact preload and custom formed motion'],
                ],
                [
                    'type' => 'Supplier Guide',
                    'title' => 'Documents to Request for Production Parts',
                    'summary' => 'Choose inspection, material and traceability documents according to the application risk.',
                    'points' => ['Inspection report', 'Material certificate', 'Surface treatment record', 'Traceability and packaging identification'],
                ],
            ],
        ],
        'solution_details' => springapex_solution_content(),
        'contact_workflow' => [
            ['step' => '01', 'title' => 'Send Details', 'text' => 'Share your drawing, quantity and requirements.'],
            ['step' => '02', 'title' => 'Engineering Review', 'text' => 'We assess the design and confirm any missing details.'],
            ['step' => '03', 'title' => 'Receive Next Steps', 'text' => 'Get technical feedback, sample options or a quote.'],
        ],
        'home_faq' => [
            ['question' => 'Can you manufacture from my drawing without a standard part number?', 'answer' => 'Yes. Most of our projects start from a customer drawing or application sketch. We review the geometry, material and performance needs, then confirm manufacturability before quoting.'],
            ['question' => 'What is your minimum order quantity?', 'answer' => 'We support both small-batch prototypes and high-volume production. There is no fixed MOQ — quantities are evaluated based on the spring type, tooling and material requirements.'],
            ['question' => 'How do you handle springs for high-temperature or corrosive environments?', 'answer' => 'We select materials (such as Inconel, stainless steel or special alloys) and surface treatments matched to the operating environment. Our engineers can recommend options based on temperature range, media exposure and cycle life.'],
            ['question' => 'What documentation can you provide with production orders?', 'answer' => 'We supply inspection reports, material certificates, dimensional records and traceability documentation as agreed during project setup. PPAP and FAIR packages are available for automotive and aerospace programs.'],
            ['question' => 'What is the expected lead time?', 'answer' => 'Lead time depends on the spring type, material, tooling, quantity and approval requirements. We confirm the production and sample schedule during quotation.'],
            ['question' => 'How are payment and delivery terms confirmed?', 'answer' => 'Commercial terms, delivery address and documentation requirements are confirmed with the quotation before production starts.'],
            ['question' => 'What support is available after delivery?', 'answer' => 'After-delivery questions can be routed through the project contact for drawing questions, quality documents, repeat orders and application feedback.'],
            ['question' => 'Can you provide samples before mass production?', 'answer' => 'Yes. Prototype and pre-production samples can be arranged for dimensional, load and application approval before the full production order proceeds.'],
        ],
    ];
}
