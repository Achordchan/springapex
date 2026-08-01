<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function springapex_content_enhancements(): array
{
    return [
        'company' => [
            'facts' => [
                ['icon' => 'users', 'value' => '2001', 'label' => 'Founded', 'detail' => 'Spring manufacturing experience built in Xuzhou.'],
                ['icon' => 'factory', 'value' => '3', 'label' => 'Production Facilities', 'detail' => 'Manufacturing capacity organized across three facilities.'],
                ['icon' => 'users', 'value' => '120+', 'label' => 'Employees', 'detail' => 'Engineering, production, quality and export support teams.'],
                ['icon' => 'spring', 'value' => 'USD 30+ million', 'label' => 'Annual Output', 'detail' => 'Precision spring components produced each year.'],
                ['icon' => 'form', 'value' => '0.1–80 mm', 'label' => 'Wire Diameter Range', 'detail' => 'From fine-wire components to heavy-duty spring applications.'],
            ],
            'manufacturing' => [
                'eyebrow' => 'MANUFACTURING SCALE',
                'title' => 'Built for reliable production.',
                'text' => 'From engineering review and process validation to precision forming and final inspection, SpringApex provides consistent spring solutions for both prototype development and high-volume production.',
                'image' => 'generated/springapex-factory-floor-v1.webp',
            ],
            'quality' => [
                'eyebrow' => 'QUALITY SYSTEMS',
                'title' => 'Quality Requirements Translated into Production Excellence',
                'text' => 'Quality is built into every step of our manufacturing process. Through strict process control, advanced inspection equipment, and continuous improvement, we ensure consistent spring performance and reliability.',
                'detail' => 'Our quality management systems are certified to international standards, including IATF 16949, ISO 9001, ISO 13485, ISO 14001, and ISO 45001. Certification documents are available for customer review during supplier qualification.',
                'standards' => [
                    ['name' => 'IATF 16949', 'scope' => 'Automotive quality management'],
                    ['name' => 'ISO 9001', 'scope' => 'Quality management system'],
                    ['name' => 'ISO 13485', 'scope' => 'Medical device quality management'],
                    ['name' => 'ISO 14001', 'scope' => 'Environmental management system'],
                    ['name' => 'ISO 45001', 'scope' => 'Occupational health and safety'],
                ],
                'image' => 'generated/springapex-quality-lab-v1.webp',
            ],
            'timeline' => [
                ['year' => '2001', 'title' => 'APEX Spring founded', 'text' => 'The company began precision spring manufacturing in Xuzhou, Jiangsu.'],
                ['year' => 'Growth', 'title' => 'Expanded production capacity', 'text' => 'Production developed across three facilities with broader wire-diameter and industry coverage.'],
                ['year' => 'Today', 'title' => 'International program support', 'text' => 'Engineering, quality and export teams support customers from drawing review through repeat delivery.'],
            ],
            'gallery' => [
                ['image' => 'generated/springapex-factory-floor-v1.webp', 'alt' => 'Representative precision spring manufacturing workflow', 'caption' => 'Representative manufacturing workflow'],
                ['image' => 'generated/springapex-quality-lab-v1.webp', 'alt' => 'Representative spring inspection setup', 'caption' => 'Representative inspection setup'],
            ],
            'markets' => [
                'title' => 'Built in Xuzhou. Supporting international programs.',
                'text' => 'The export team coordinates drawing review, technical communication, documentation and shipment planning for customers across global industrial markets.',
                'regions' => ['North America', 'Europe', 'Asia-Pacific', 'International industrial markets'],
            ],
        ],
        'manufacturing_process' => [
            ['icon' => 'pen', 'step' => '01', 'title' => 'Requirement Review', 'text' => 'Load, movement, space, environment and drawing requirements are checked before production planning.'],
            ['icon' => 'form', 'step' => '02', 'title' => 'Prototype & Tooling', 'text' => 'Geometry and process settings are prepared for samples or the first controlled production run.'],
            ['icon' => 'cnc', 'step' => '03', 'title' => 'Forming & Coiling', 'text' => 'CNC coiling and forming processes control dimensions, pitch, hooks and wire geometry.'],
            ['icon' => 'heat', 'step' => '04', 'title' => 'Heat & Surface Process', 'text' => 'Stress relief, heat treatment and surface options are selected for material and service conditions.'],
            ['icon' => 'search', 'step' => '05', 'title' => 'Inspection & Validation', 'text' => 'Dimensions, load and application-critical characteristics are checked against the agreed control plan.'],
            ['icon' => 'delivery', 'step' => '06', 'title' => 'Packing & Delivery', 'text' => 'Parts are protected, identified and prepared with the documentation required for shipment.'],
        ],
        'quality_evidence' => [
            ['icon' => 'form', 'title' => 'Dimensional Inspection', 'text' => 'Wire diameter, free length, diameters, pitch, angles and formed features.'],
            ['icon' => 'spring', 'title' => 'Load Verification', 'text' => 'Load at height, torque or extension behavior checked to the product requirement.'],
            ['icon' => 'search', 'title' => 'Performance Validation', 'text' => 'Fatigue, material and environmental tests can be planned where the application requires them.'],
            ['icon' => 'check-shield', 'title' => 'Batch Documentation', 'text' => 'Inspection records, material documentation and traceability can be supplied by project agreement.'],
        ],
        'product_selection' => [
            'title' => 'Choose by how the part carries load.',
            'text' => 'Start with the load direction, available space, service environment and expected cycle life. Our engineers can then narrow the spring family, material and manufacturing route.',
            'items' => [
                ['icon' => 'spring', 'title' => 'Axial Compression', 'text' => 'Compression, die and disc springs resist force as the available length decreases.'],
                ['icon' => 'extension', 'title' => 'Axial Tension', 'text' => 'Extension springs store energy as hooks or loops are pulled apart.'],
                ['icon' => 'torsion', 'title' => 'Rotational Torque', 'text' => 'Torsion springs act around a centerline and control angular movement.'],
                ['icon' => 'form', 'title' => 'Custom Motion', 'text' => 'Wire forms, pins and specialty geometries transfer force through application-specific shapes.'],
            ],
        ],
        'specialty_products' => [
            ['title' => 'Conical Springs', 'text' => 'Nested compression and reduced solid-height designs.', 'icon' => 'spring'],
            ['title' => 'Continuous-Length Springs', 'text' => 'Long coils prepared for cutting or downstream assembly.', 'icon' => 'spring'],
            ['title' => 'Die Springs', 'text' => 'High-force springs for tooling and repetitive industrial duty.', 'icon' => 'factory'],
            ['title' => 'Vibration & Isolation Springs', 'text' => 'Spring elements selected for shock and vibration control.', 'icon' => 'gear'],
            ['title' => 'Wave Springs', 'text' => 'Compact axial preload where installation space is limited.', 'icon' => 'disc'],
            ['title' => 'Pins & Retaining Parts', 'text' => 'Formed retaining components and application-specific wire parts.', 'icon' => 'extension'],
        ],
        'resources' => [
            'hero' => [
                'title' => 'Engineering Resources',
                'subtitle' => 'Practical guidance for specifying, reviewing and sourcing precision springs.',
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
        'solution_details' => [
            'automotive' => [
                'challenge_intro' => 'Automotive spring programs balance packaging, fatigue life, corrosion exposure and repeatable force across high-volume production.',
                'challenges' => ['Consistent load and geometry across repeat batches', 'Fatigue performance under vibration and repeated motion', 'Corrosion protection and material traceability'],
                'products' => ['compression-springs', 'extension-springs', 'torsion-springs', 'wire-forms'],
                'processes' => ['Material and surface-treatment review', 'Prototype and first-article verification', 'Load, dimension and traceability controls'],
                'validation' => ['Dimensional inspection', 'Load or torque checks', 'Fatigue and corrosion testing when specified'],
                'applications' => ['Seat and latch mechanisms', 'Controls and return mechanisms', 'Powertrain and thermal-management assemblies'],
            ],
            'industrial-equipment' => [
                'challenge_intro' => 'Industrial equipment requires durable spring components that remain serviceable under load, contamination and repeated operation.',
                'challenges' => ['Heavy or repeated loading', 'Dust, moisture and temperature exposure', 'Maintenance access and replacement consistency'],
                'products' => ['compression-springs', 'torsion-springs', 'disc-springs', 'wire-springs'],
                'processes' => ['Load and installation-space review', 'Material and finish selection', 'Controlled forming and final inspection'],
                'validation' => ['Load-at-height verification', 'Dimensional and assembly checks', 'Application-specific cycle testing'],
                'applications' => ['Valves and actuators', 'Machine guards and access systems', 'Fixtures, tooling and automation'],
            ],
            'medical' => [
                'challenge_intro' => 'Medical spring components often require fine geometry, clean material choices and controlled documentation.',
                'challenges' => ['Small dimensions and sensitive force windows', 'Material, cleanliness and corrosion requirements', 'Documented change and batch control'],
                'products' => ['compression-springs', 'extension-springs', 'torsion-springs', 'wire-forms'],
                'processes' => ['Drawing and critical-characteristic review', 'Material and surface-condition selection', 'Controlled inspection and documentation'],
                'validation' => ['Fine-dimensional measurement', 'Low-force verification', 'Material and traceability documentation'],
                'applications' => ['Delivery and diagnostic devices', 'Handheld instruments', 'Positioning and return mechanisms'],
            ],
            'aerospace' => [
                'challenge_intro' => 'Aerospace applications demand disciplined requirements review, material control and verification of application-critical characteristics.',
                'challenges' => ['High consequence of dimensional or load variation', 'Temperature, vibration and corrosion exposure', 'Documentation and configuration control'],
                'products' => ['compression-springs', 'torsion-springs', 'disc-springs', 'wire-forms'],
                'processes' => ['Requirement and material review', 'Prototype and first-article planning', 'Inspection and documentation controls'],
                'validation' => ['Critical dimension and force checks', 'Material documentation', 'Application-specific validation by agreement'],
                'applications' => ['Actuation and control mechanisms', 'Latches and retention systems', 'Ground-support and cabin equipment'],
            ],
            'energy' => [
                'challenge_intro' => 'Energy applications require stable mechanical performance across environmental exposure and long service intervals.',
                'challenges' => ['Outdoor corrosion and temperature variation', 'Long-term preload or repeated actuation', 'Traceable maintenance and replacement supply'],
                'products' => ['compression-springs', 'disc-springs', 'torsion-springs', 'wire-forms'],
                'processes' => ['Environment and material review', 'Surface-treatment planning', 'Load and dimension verification'],
                'validation' => ['Load retention checks', 'Coating or corrosion validation when specified', 'Batch documentation'],
                'applications' => ['Switchgear and electrical mechanisms', 'Valves and control equipment', 'Renewable-energy assemblies'],
            ],
            'rail-transit' => [
                'challenge_intro' => 'Rail spring components must withstand vibration, repeated use and demanding maintenance cycles.',
                'challenges' => ['Continuous vibration and shock', 'Corrosion and outdoor exposure', 'Long service intervals and repeatability'],
                'products' => ['compression-springs', 'extension-springs', 'disc-springs', 'wire-forms'],
                'processes' => ['Duty-cycle and load review', 'Material and finish selection', 'Production and traceability controls'],
                'validation' => ['Load and dimensional checks', 'Cycle testing by application requirement', 'Material and batch documentation'],
                'applications' => ['Door and access mechanisms', 'Coupling and braking assemblies', 'Cabin and electrical equipment'],
            ],
        ],
        'contact_workflow' => [
            ['step' => '01', 'title' => 'Submit Requirements', 'text' => 'Share the drawing, application, quantity and known performance requirements.'],
            ['step' => '02', 'title' => 'Information Check', 'text' => 'The team confirms the project context and identifies any missing engineering inputs.'],
            ['step' => '03', 'title' => 'Engineering Review', 'text' => 'Material, geometry, process and validation needs are reviewed for manufacturability.'],
            ['step' => '04', 'title' => 'Quote or Technical Feedback', 'text' => 'You receive the next technical questions, sample plan or quotation path.'],
        ],
        'home_faq' => [
            ['question' => 'Can you manufacture from my drawing without a standard part number?', 'answer' => 'Yes. Most of our projects start from a customer drawing or application sketch. We review the geometry, material and performance needs, then confirm manufacturability before quoting.'],
            ['question' => 'What is your minimum order quantity?', 'answer' => 'We support both small-batch prototypes and high-volume production. There is no fixed MOQ — quantities are evaluated based on the spring type, tooling and material requirements.'],
            ['question' => 'How do you handle springs for high-temperature or corrosive environments?', 'answer' => 'We select materials (such as Inconel, stainless steel or special alloys) and surface treatments matched to the operating environment. Our engineers can recommend options based on temperature range, media exposure and cycle life.'],
            ['question' => 'What documentation can you provide with production orders?', 'answer' => 'We supply inspection reports, material certificates, dimensional records and traceability documentation as agreed during project setup. PPAP and FAIR packages are available for automotive and aerospace programs.'],
        ],
    ];
}
