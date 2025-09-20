<?php

declare(strict_types=1);

/*
 * This file is part of the "powermail_extended" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace SyntaxOOps\PowermailExtended\Utility;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LocalizationUtility
 *
 * @author Haythem Daoud <haythemdaoud.x@gmail.com>
 */
class DatabaseQueryUtility
{
    /**
     * @param string $table
     * @param array $fields
     * @param array $conditions
     * @param ArrayParameterType|ParameterType|null $parameterType
     * @return array
     * @throws Exception
     */
    public static function fetchRowsByTable(
        string $table,
        array $fields,
        array $conditions = [],
        ArrayParameterType|ParameterType|null $parameterType = null)
    : array {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);

        $queryBuilder
            ->select(...$fields)
            ->from($table);

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->in($field, $queryBuilder->createNamedParameter($value, $parameterType))
                );
            } else {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->eq($field, $queryBuilder->createNamedParameter($value))
                );
            }
        }

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }
}
