<?php

$EM_CONF['powermail_extended'] = [
    'title' => 'Powermail Extended',
    'description' => 'Extends base powermail extension.',
    'category' => 'module',
    'author' => 'haythem daoud',
    'author_email' => 'haythemdaoud.x@gmail.com',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '13.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.9.99',
            'powermail' => '13.0.0-13.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
