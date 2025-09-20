<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addTypoScript(
    'powermail_extended',
    'setup',
    '
       module.tx_powermail {
            view {
                templateRootPaths.20 = EXT:powermail_extended/Resources/Private/Templates/
                partialRootPaths.20 = EXT:powermail_extended/Resources/Private/Partials/
                layoutRootPaths.20 = EXT:powermail_extended/Resources/Private/Layouts/
            }
       }
    ',
);
