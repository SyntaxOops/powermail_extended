<?php

use In2code\Powermail\Controller\ModuleController;

return [
    'powermail_test' => [
        'parent' => 'web_powermail',
        'position' => [],
        'access' => 'user',
        'iconIdentifier' => 'extension-powermail-main',
        'labels' => [
            'title' => 'LLL:EXT:powermail_extended/Resources/Private/Language/locallang_mod.xlf:title',
        ],
        'extensionName' => 'Powermail',
        'path' => '/module/powermail/test',
        'controllerActions' => [
            ModuleController::class => 'test'
        ],
    ],
];
