<?php

return [
    'phone' => env('SANTRAINS_PHONE', '+353 89230 3761'),
    'email' => env('SANTRAINS_EMAIL', 'info@santrains.com'),
    'website' => env('SANTRAINS_WEBSITE', 'www.santrains.com'),
    'instructor_name' => env('SANTRAINS_INSTRUCTOR_NAME', 'Santhosh Jacob'),
    'default_course' => env('SANTRAINS_DEFAULT_COURSE', 'Manual Handling and People Handling'),
    'training_standard_url' => env('SANTRAINS_TRAINING_STANDARD_URL', 'hseland.ie'),
    'course_catalog' => [
        'Manual Handling & People Handling',
        'Basic Life Support',
        'Cardiac First Response Community Level',
        'Infection Control',
        'Fire Training',
        'PMAV',
        'Safeguarding Vulnerable Adults',
        'Driving Lessons',
    ],
];
