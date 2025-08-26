<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/clacoform/entry', name: 'apiv2_clacoformentry_')]
class EntryController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly FinderProvider $finder,
        private readonly ClacoFormManager $manager
    ) {
        $this->authorization = $authorization;
    }

    public static function getClass(): string
    {
        return Entry::class;
    }

    public function getIgnore(): array
    {
        return ['list'];
    }

    public static function getName(): string
    {
        return 'clacoform_entry';
    }

    #[Route(path: '/clacoform/{clacoForm}/entries/list', name: 'claroform_list', methods: ['GET'])]
    public function entriesListAction(
        #[MapEntity(mapping: ['clacoForm' => 'uuid'])]
        ClacoForm $clacoForm,
        Request $request
    ): JsonResponse {
        $this->checkPermission('OPEN', $clacoForm->getResourceNode(), [], true);

        $params = $request->query->all();
        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['clacoForm'] = $clacoForm->getId();

        if (isset($params['filters'])) {
            $filters = $params['filters'];
            $excludedFilters = [
                'clacoForm',
                'type',
                'title',
                'status',
                'locked',
                'user',
                'createdAfter',
                'createdBefore',
                'categories',
            ];

            foreach ($params['filters'] as $key => $value) {
                if (!in_array($key, $excludedFilters)) {
                    $filters[$key] = $value;
                }
            }
            $params['filters'] = $filters;
        }
        $data = $this->finder->search(Entry::class, $params);

        return new JsonResponse($data, 200);
    }

    #[Route(path: '/clacoform/{clacoForm}/file/upload', name: 'file_upload', methods: ['POST'])]
    public function uploadAction(
        #[MapEntity(mapping: ['clacoForm' => 'uuid'])]
        ClacoForm $clacoForm,
        Request $request
    ): JsonResponse {
        $this->checkPermission('OPEN', $clacoForm->getResourceNode(), [], true);

        $files = $request->files->all();
        $data = [];

        foreach ($files as $file) {
            $data[] = $this->manager->registerFile($clacoForm, $file);
        }

        return new JsonResponse($data, 200);
    }

    /**
     * Returns id of a random entry.
     */
    #[Route(path: '/{clacoForm}/random', name: 'random', methods: ['GET'])]
    public function randomAction(
        #[MapEntity(mapping: ['clacoForm' => 'uuid'])]
        ClacoForm $clacoForm
    ): JsonResponse {
        $this->checkPermission('OPEN', $clacoForm->getResourceNode(), [], true);

        $entryId = $this->manager->getRandomEntryId($clacoForm);

        return new JsonResponse($entryId, 200);
    }

    /**
     * Changes status of an entry.
     */
    #[Route(path: '/entry/{entry}/status/change', name: 'change_status', methods: ['PUT'])]
    public function changeStatusAction(#[MapEntity(mapping: ['entry' => 'uuid'])] Entry $entry): JsonResponse
    {
        $this->checkPermission('ADMINISTRATE', $entry, [], true);

        $this->manager->changeEntryStatus($entry);

        return new JsonResponse($this->serializer->serialize($entry), 200);
    }

    /**
     * Changes status of entries.
     */
    #[Route(path: '/entries/status/{status}/change', name: 'change_status_bulk', methods: ['PUT'])]
    public function changeStatusBulkAction(int $status, Request $request): JsonResponse
    {
        $entries = [];
        $serializedEntries = [];

        $ids = $this->decodeRequest($request);
        $entriesParams = $this->om->getRepository(Entry::class)->findBy(['uuid' => $ids]);
        foreach ($entriesParams as $entryParam) {
            if (!$entryParam->isLocked() && $this->checkPermission('ADMINISTRATE', $entryParam)) {
                $entries[] = $entryParam;
            }
        }

        $updatedEntries = $this->manager->changeEntriesStatus($entries, intval($status));
        foreach ($updatedEntries as $entry) {
            $serializedEntries[] = $this->serializer->serialize($entry);
        }

        return new JsonResponse($serializedEntries, 200);
    }
}
