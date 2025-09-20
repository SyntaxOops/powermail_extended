<?php

declare(strict_types=1);

/*
 * This file is part of the "powermail_extended" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace SyntaxOOps\PowermailExtended\Controller;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception;
use In2code\Powermail\Controller\ModuleController as BaseModuleController;
use In2code\Powermail\Domain\Repository\FieldRepository;
use In2code\Powermail\Domain\Repository\PageRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SyntaxOOps\PowermailExtended\Utility\DatabaseQueryUtility;
use SyntaxOOps\PowermailExtended\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class ModuleController
 *
 * @author Haythem Daoud <haythemdaoud.x@gmail.com>
 */
class ModuleController extends BaseModuleController
{
    protected PersistenceManager $persistenceManager;

    /**
     * @param PersistenceManager $persistenceManager
     * @return void
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @return void
     */
    public function initializeAction(): void
    {
        $this->pageRenderer->addInlineLanguageLabelFile(
            GeneralUtility::getFileAbsFileName(
                'EXT:powermail_extended/Resources/Private/Language/locallang.xlf'
            )
        );
        parent::initializeAction();
    }

    /**
     * @param ServerRequestInterface $request
     * @return JsonResponse
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function movePagesAndFieldsAction(ServerRequestInterface $request): JsonResponse
    {
        $data = $request->getBody()->getContents() ?? '';
        $data = json_decode($data, true, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            return $this->json(LocalizationUtility::translate('sorting.error'), 400);
        }

        /** @var PageRepository $pageRepository */
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);

        /** @var FieldRepository $fieldRepository */
        $fieldRepository = GeneralUtility::makeInstance(FieldRepository::class);

        // Move Pages
        if (!empty($data['form-uid'])) {
            $this->updateSorting($pageRepository, $data['sortedIds']);
            $this->persistenceManager->persistAll();

            return $this->json(LocalizationUtility::translate('sorting.success'));
        }

        // Move Fields
        if (!empty($data['page-uid'])) {
            $targetPageUid = $data['page-uid'];
            $movedFieldUid = $data['movedFieldUid'] ?? null;

            $this->updateSorting($fieldRepository, $data['sortedIds']);

            if ($movedFieldUid) {
                $field = $fieldRepository->findByUid((int)$movedFieldUid);
                $page = $pageRepository->findByUid((int)$targetPageUid);
                $field->setPage($page);
                $fieldRepository->update($field);
            }

            $this->persistenceManager->persistAll();

            return $this->json(LocalizationUtility::translate('sorting.success'));
        }

        return $this->json(LocalizationUtility::translate('sorting.error'), 400);
    }

    /**
     * @throws Exception
     */
    public function getPagesByFormAction(ServerRequestInterface $request): JsonResponse
    {
        $params = $request->getQueryParams();
        $formUid = (int)($params['formUid'] ?? 0);

        if ($formUid <= 0) {
            return $this->json(LocalizationUtility::translate('view.invalid.formUid'), 400);
        }

        $ttContentRows = DatabaseQueryUtility::fetchRowsByTable(
            'tt_content',
            ['uid', 'pid', 'header', 'pi_flexform'],
            ['CType' => 'powermail_pi1']
        );

        $TtContentData = [];
        foreach ($ttContentRows as $row) {
            $flexData = GeneralUtility::xml2array($row['pi_flexform'] ?? '');
            $selectedFormUid = (int)($flexData['data']['main']['lDEF']['settings.flexform.main.form']['vDEF'] ?? 0);

            if ($selectedFormUid === $formUid) {
                $TtContentData[$row['uid']] = $row['pid'];
            }
        }

        $TtContentData = array_unique($TtContentData);

        $pages = DatabaseQueryUtility::fetchRowsByTable(
            'pages',
            ['uid', 'title', 'slug'],
            ['uid' => $TtContentData],
            ArrayParameterType::INTEGER
        );

        $pageData = [];
        foreach ($pages as $row) {
            $contentUid = array_search($row['uid'], $TtContentData, true);

            $row['contentUid'] = $contentUid;
            $row['url'] = rtrim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), '/')
                . ($row['slug'] ?? '')
                . '#c' . $contentUid;

            $pageData[] = $row;
        }

        return new JsonResponse(['pages' => $pageData]);
    }

    /**
     * @return ResponseInterface
     */
    protected function testAction(): ResponseInterface
    {
        return $this->moduleTemplate->renderResponse('Module/Test');
    }

    /**
     * @param object $repository
     * @param array $uids
     * @return void
     */
    private function updateSorting(object $repository, array $uids): void
    {
        foreach ($uids as $sorting => $uid) {
            $entity = $repository->findByUid((int)$uid);
            $entity->setSorting($sorting + 1);
            $repository->update($entity);
        }
    }

    /**
     * @param string $message
     * @param int $status
     * @return JsonResponse
     */
    private function json(string $message, int $status = 200): JsonResponse
    {
        return new JsonResponse([
            'success' => $status === 200,
            'message' => $message
        ], $status);
    }
}
