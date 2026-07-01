<?php

return [
    'disable' => env('CAPTCHA_DISABLE', false),

    'characters' => [
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'J',
        'K',
        'L',
        'M',
        'N',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
    ],

    'default' => [
        'length' => 5,
        'width' => 150,
        'height' => 45,
        'quality' => 90,

        'math' => false,
        'expire' => 120,
        'encrypt' => false,
        'sensitive' => false,

        'bgImage' => false,
        'bgColor' => '#f8fafc',

        'fontColors' => [
            '#1e293b',
            '#0f172a',
            '#334155',
        ],

        'angle' => 8,
        'lines' => 2,
        'contrast' => 2,
        'sharpen' => 3,
        'blur' => 1,
        'invert' => false,

        'textLeftPadding' => 14,
        'marginTop' => 6,
    ],
];
