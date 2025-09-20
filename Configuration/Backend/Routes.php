<?php

use In2code\Powermail\Controller\ModuleController;

return [
    'powermail_test' => [
        'path' => '/powermail/test',
        'target' => ModuleController::class . '::testAction',
    ],
];
