<?php

declare(strict_types=1);

/*
 * This file is part of the "powermail_extended" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace SyntaxOOps\PowermailExtended\Utility;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility as BaseLocalizationUtility;

/**
 * Class LocalizationUtility
 *
 * @author Haythem Daoud <haythemdaoud.x@gmail.com>
 */
class LocalizationUtility
{
    private const EXTENSION_NAME = 'powermail_extended';

    /**
     * @param string $key
     * @return string
     */
    public static function translate(string $key): string
    {
        return (string)BaseLocalizationUtility::translate($key, self::EXTENSION_NAME);
    }
}
