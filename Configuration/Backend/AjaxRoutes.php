<?php

use In2code\Powermail\Controller\ModuleController;

return [
    'powermail_move' => [
        'path' => '/powermail/move',
        'target' => ModuleController::class . '::movePagesAndFieldsAction',
    ],

    'powermail_pages' => [
        'path' => '/powermail/pages',
        'target' => ModuleController::class . '::getPagesByFormAction',
    ],
];
