<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function springapex_solution_content(): array
{
    $shared_steps = [
        [
            'title' => 'Application Review',
            'text' => 'We review the mechanism, loads, movement, environment and production requirements.',
            'image' => 'manufacturing-videos/application-engineering-v1.webp',
        ],
        [
            'title' => 'Spring, Material & Prototype',
            'text' => 'Spring geometry, material and finish are defined before prototype and first-article review.',
            'image' => 'manufacturing-videos/featured-cnc-coiling-v1.webp',
        ],
        [
            'title' => 'Controlled Production',
            'text' => 'Approved tooling, process settings and in-process checks support repeatable output.',
            'image' => 'manufacturing-videos/machine-setup-v1.webp',
        ],
        [
            'title' => 'Validation & Records',
            'text' => 'Inspection, performance checks and agreed records are completed before release.',
            'image' => 'manufacturing-videos/quality-inspection-v1.webp',
        ],
    ];

    return [
        'automotive' => [
            'hero_title' => 'Automotive spring programs built from requirements to repeat production.',
            'challenge_intro' => 'We balance packaging, fatigue life, corrosion exposure and repeatable force for automotive production.',
            'requirements_title' => 'We design around the conditions that matter.',
            'requirements_text' => 'Every spring program is shaped by the operating environment, assembly constraints and validation expectations.',
            'challenges' => [
                ['title' => 'Repeatable performance under load', 'text' => 'Maintain consistent force, travel and torque across production batches.', 'icon' => 'target'],
                ['title' => 'Durability in the operating environment', 'text' => 'Account for vibration, temperature, corrosion and repeated motion.', 'icon' => 'gear'],
                ['title' => 'Traceability from lot to shipment', 'text' => 'Keep material, process and inspection records aligned with each batch.', 'icon' => 'check-shield'],
            ],
            'products' => ['compression-springs', 'extension-springs', 'torsion-springs', 'wire-forms'],
            'application_items' => [
                ['title' => 'Seat & Latch Mechanisms', 'text' => 'Seats, headrests, latches, hinges and locking mechanisms.', 'icon' => 'car', 'products' => ['compression-springs', 'extension-springs', 'torsion-springs'], 'image' => 'solutions/automotive-seat-latch-v1.png'],
                ['title' => 'Controls & Return Mechanisms', 'text' => 'Pedals, throttles, shifters, valves and actuation systems.', 'icon' => 'gear', 'products' => ['compression-springs', 'extension-springs', 'wire-forms'], 'image' => 'solutions/automotive-controls-return-v1.png'],
                ['title' => 'Powertrain & Thermal Management', 'text' => 'Engine components, sensors, dampers and thermal systems.', 'icon' => 'gear', 'products' => ['compression-springs', 'torsion-springs', 'wire-forms'], 'image' => 'solutions/automotive-powertrain-thermal-v1.png'],
            ],
            'program_steps' => $shared_steps,
            'quality_image' => 'generated/springapex-quality-lab-v1.webp',
            'quality_items' => [
                ['title' => 'Dimensional Inspection', 'text' => 'Critical dimensions are verified against drawings and agreed specifications.', 'icon' => 'form'],
                ['title' => 'Load or Torque Verification', 'text' => 'Spring rate, preload and torque are checked at defined positions.', 'icon' => 'target'],
                ['title' => 'Material & Batch Records', 'text' => 'Material, processing and inspection information follows each production lot.', 'icon' => 'check-shield'],
            ],
        ],
        'industrial-equipment' => [
            'hero_title' => 'Industrial spring programs engineered for reliable service.',
            'challenge_intro' => 'We design for repeated loads, contamination, maintenance access and dependable replacement supply.',
            'requirements_title' => 'Durability starts with the real duty cycle.',
            'requirements_text' => 'Load, installation space, service conditions and maintenance expectations guide every design decision.',
            'challenges' => [
                ['title' => 'Heavy or repeated loading', 'text' => 'Define load, travel and cycle requirements before selecting geometry.', 'icon' => 'target'],
                ['title' => 'Dust, moisture and temperature exposure', 'text' => 'Select materials and finishes around the operating environment.', 'icon' => 'shield'],
                ['title' => 'Serviceable replacement supply', 'text' => 'Hold dimensions and performance stable across repeat orders.', 'icon' => 'gear'],
            ],
            'products' => ['compression-springs', 'torsion-springs', 'disc-springs', 'wire-forms'],
            'application_items' => [
                ['title' => 'Valves & Actuators', 'text' => 'Return, regulation and preload functions in fluid and pneumatic systems.', 'icon' => 'gear', 'products' => ['compression-springs', 'disc-springs']],
                ['title' => 'Guards & Access Systems', 'text' => 'Hinges, covers, doors and controlled opening mechanisms.', 'icon' => 'factory', 'products' => ['torsion-springs', 'extension-springs', 'wire-forms']],
                ['title' => 'Fixtures, Tooling & Automation', 'text' => 'Clamping, positioning and return functions in production equipment.', 'icon' => 'gear', 'products' => ['compression-springs', 'die-springs', 'wire-forms']],
            ],
            'program_steps' => $shared_steps,
            'quality_image' => 'quality-inspection-wide-original.webp',
            'quality_items' => [
                ['title' => 'Load-at-Height Verification', 'text' => 'Working loads are measured at the application heights defined by the drawing.', 'icon' => 'target'],
                ['title' => 'Dimensional & Assembly Checks', 'text' => 'Geometry and interface dimensions are confirmed before release.', 'icon' => 'form'],
                ['title' => 'Cycle Testing When Required', 'text' => 'Application-specific cycling can be agreed during engineering review.', 'icon' => 'gear'],
            ],
        ],
        'medical' => [
            'hero_title' => 'Medical spring programs built around precision and documentation.',
            'challenge_intro' => 'We support fine geometry, sensitive force windows, clean material choices and controlled records.',
            'requirements_title' => 'Small components still demand a complete requirements review.',
            'requirements_text' => 'Critical characteristics, material condition and documentation are defined before prototype release.',
            'challenges' => [
                ['title' => 'Fine dimensions and low forces', 'text' => 'Control geometry and force within narrow application windows.', 'icon' => 'target'],
                ['title' => 'Material and surface condition', 'text' => 'Match corrosion, cleanliness and contact requirements.', 'icon' => 'shield'],
                ['title' => 'Documented batch control', 'text' => 'Maintain agreed inspection and material records for repeat production.', 'icon' => 'check-shield'],
            ],
            'products' => ['compression-springs', 'extension-springs', 'torsion-springs', 'wire-forms'],
            'application_items' => [
                ['title' => 'Delivery & Diagnostic Devices', 'text' => 'Compact spring functions in controlled delivery and diagnostic assemblies.', 'icon' => 'target', 'products' => ['compression-springs', 'extension-springs']],
                ['title' => 'Handheld Instruments', 'text' => 'Return, retention and tactile mechanisms for precision instruments.', 'icon' => 'form', 'products' => ['torsion-springs', 'wire-forms']],
                ['title' => 'Positioning & Return Mechanisms', 'text' => 'Consistent movement and reset functions in small assemblies.', 'icon' => 'gear', 'products' => ['compression-springs', 'torsion-springs']],
            ],
            'program_steps' => $shared_steps,
            'quality_image' => 'generated/springapex-quality-lab-v1.webp',
            'quality_items' => [
                ['title' => 'Fine-Dimensional Measurement', 'text' => 'Critical dimensions are checked with methods suited to small components.', 'icon' => 'form'],
                ['title' => 'Low-Force Verification', 'text' => 'Sensitive force windows are measured at the specified working positions.', 'icon' => 'target'],
                ['title' => 'Material & Traceability Records', 'text' => 'Agreed material and batch information is retained with production records.', 'icon' => 'check-shield'],
            ],
        ],
        'aerospace' => [
            'hero_title' => 'Aerospace spring programs defined by requirements and evidence.',
            'challenge_intro' => 'We apply disciplined material control and verification to application-critical spring characteristics.',
            'requirements_title' => 'Critical characteristics are defined before production.',
            'requirements_text' => 'Loads, interfaces, environment and documentation expectations are reviewed as one connected program.',
            'challenges' => [
                ['title' => 'Controlled load and geometry', 'text' => 'Identify the dimensions and performance points that are critical to function.', 'icon' => 'target'],
                ['title' => 'Temperature, vibration and corrosion', 'text' => 'Select material and finish around the operating environment.', 'icon' => 'shield'],
                ['title' => 'Configuration and document control', 'text' => 'Align revisions, material records and inspection evidence.', 'icon' => 'check-shield'],
            ],
            'products' => ['compression-springs', 'torsion-springs', 'disc-springs', 'wire-forms'],
            'application_items' => [
                ['title' => 'Actuation & Control Mechanisms', 'text' => 'Return, preload and controlled movement in mechanical assemblies.', 'icon' => 'rocket', 'products' => ['compression-springs', 'torsion-springs']],
                ['title' => 'Latches & Retention Systems', 'text' => 'Compact force and holding functions for access and retention.', 'icon' => 'shield', 'products' => ['extension-springs', 'torsion-springs', 'wire-forms']],
                ['title' => 'Ground-Support & Cabin Equipment', 'text' => 'Serviceable spring components for support and interior mechanisms.', 'icon' => 'gear', 'products' => ['compression-springs', 'disc-springs', 'wire-forms']],
            ],
            'program_steps' => $shared_steps,
            'quality_image' => 'quality-inspection-wide-original.webp',
            'quality_items' => [
                ['title' => 'Critical Dimension & Force Checks', 'text' => 'Agreed critical characteristics are verified before release.', 'icon' => 'target'],
                ['title' => 'Material Documentation', 'text' => 'Material identity and available supporting records remain linked to the batch.', 'icon' => 'check-shield'],
                ['title' => 'Application-Specific Validation', 'text' => 'Additional validation is defined with the customer when the application requires it.', 'icon' => 'gear'],
            ],
        ],
        'energy' => [
            'hero_title' => 'Energy spring programs built for environmental exposure and long service.',
            'challenge_intro' => 'We design for stable mechanical performance, corrosion exposure and dependable long-term supply.',
            'requirements_title' => 'Environment and service interval shape the spring program.',
            'requirements_text' => 'Material, finish, preload and validation are selected around the installed operating conditions.',
            'challenges' => [
                ['title' => 'Outdoor and corrosive exposure', 'text' => 'Select materials and finishes for moisture, temperature and corrosion risk.', 'icon' => 'shield'],
                ['title' => 'Long-term preload or repeated actuation', 'text' => 'Define load retention, travel and cycling expectations.', 'icon' => 'target'],
                ['title' => 'Maintenance and replacement continuity', 'text' => 'Keep records and repeat-order performance stable over long programs.', 'icon' => 'check-shield'],
            ],
            'products' => ['compression-springs', 'disc-springs', 'torsion-springs', 'wire-forms'],
            'application_items' => [
                ['title' => 'Switchgear & Electrical Mechanisms', 'text' => 'Preload, return and contact functions in electrical equipment.', 'icon' => 'leaf', 'products' => ['compression-springs', 'torsion-springs', 'wire-forms']],
                ['title' => 'Valves & Control Equipment', 'text' => 'Stable mechanical response in flow and control assemblies.', 'icon' => 'gear', 'products' => ['compression-springs', 'disc-springs']],
                ['title' => 'Renewable-Energy Assemblies', 'text' => 'Spring components for access, positioning and operating mechanisms.', 'icon' => 'leaf', 'products' => ['torsion-springs', 'disc-springs', 'wire-forms']],
            ],
            'program_steps' => $shared_steps,
            'quality_image' => 'quality-inspection-wide-original.webp',
            'quality_items' => [
                ['title' => 'Load-Retention Checks', 'text' => 'Performance is checked at the defined working condition.', 'icon' => 'target'],
                ['title' => 'Coating or Corrosion Validation', 'text' => 'Finish-related validation can be included when specified.', 'icon' => 'shield'],
                ['title' => 'Batch Documentation', 'text' => 'Production and inspection records remain connected to the released lot.', 'icon' => 'check-shield'],
            ],
        ],
        'rail-transit' => [
            'hero_title' => 'Rail spring programs designed for vibration, duty cycle and service life.',
            'challenge_intro' => 'We engineer repeatable components for vibration, repeated use, environmental exposure and maintenance cycles.',
            'requirements_title' => 'Duty cycle and maintenance conditions come first.',
            'requirements_text' => 'The design review connects shock, vibration, load, environment and replacement expectations.',
            'challenges' => [
                ['title' => 'Continuous vibration and shock', 'text' => 'Review dynamic loads, travel and retention requirements.', 'icon' => 'target'],
                ['title' => 'Corrosion and outdoor exposure', 'text' => 'Match material and finish to weather and service conditions.', 'icon' => 'shield'],
                ['title' => 'Long service intervals', 'text' => 'Control repeat-order geometry, force and supporting records.', 'icon' => 'check-shield'],
            ],
            'products' => ['compression-springs', 'extension-springs', 'disc-springs', 'wire-forms'],
            'application_items' => [
                ['title' => 'Door & Access Mechanisms', 'text' => 'Return, retention and controlled movement in passenger access systems.', 'icon' => 'factory', 'products' => ['extension-springs', 'torsion-springs', 'wire-forms']],
                ['title' => 'Coupling & Braking Assemblies', 'text' => 'High-load and preload functions in mechanical operating assemblies.', 'icon' => 'gear', 'products' => ['compression-springs', 'disc-springs']],
                ['title' => 'Cabin & Electrical Equipment', 'text' => 'Compact return and positioning functions in interior equipment.', 'icon' => 'gear', 'products' => ['compression-springs', 'torsion-springs', 'wire-forms']],
            ],
            'program_steps' => $shared_steps,
            'quality_image' => 'quality-inspection-wide-original.webp',
            'quality_items' => [
                ['title' => 'Load & Dimensional Checks', 'text' => 'Working loads and critical geometry are verified to the released drawing.', 'icon' => 'target'],
                ['title' => 'Cycle Testing by Requirement', 'text' => 'Duty-cycle testing can be defined around the operating mechanism.', 'icon' => 'gear'],
                ['title' => 'Material & Batch Documentation', 'text' => 'Agreed material and inspection records follow each production lot.', 'icon' => 'check-shield'],
            ],
        ],
    ];
}
