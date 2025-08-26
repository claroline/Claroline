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
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\ClacoFormBundle\Manager\ExportManager;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/clacoform', options: ['expose' => true])]
class ClacoFormController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly FinderProvider $finder,
        private readonly ClacoFormManager $clacoFormManager,
        private readonly string $filesDir,
        private readonly SerializerProvider $serializer,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ExportManager $exportManager
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/{id}/stats', name: 'apiv2_clacoform_stats', methods: ['GET'])]
    public function getStatsAction(ClacoForm $clacoForm): JsonResponse
    {
        $this->checkPermission('EDIT', $clacoForm, [], true);

        $stats = $this->om->getRepository(ClacoForm::class)->getEntryStats($clacoForm);

        return new JsonResponse([
            'total' => $stats['total'],
            'users' => $stats['users'],
            'fields' => array_map(function (array $fieldStats) {
                return [
                    'field' => $this->serializer->serialize($fieldStats['field']),
                    'values' => $fieldStats['values'],
                ];
            }, $stats['fields']),
        ]);
    }

    /**
     * Downloads PDF version of entry.
     */
    #[Route(path: '/entry/{entry}/pdf/download', name: 'claro_claco_form_entry_pdf_download', methods: ['GET'])]
    public function entryPdfDownloadAction(
        #[MapEntity(mapping: ['entry' => 'uuid'])]
        Entry $entry,
        #[CurrentUser]
        ?User $user
    ): StreamedResponse {
        $this->checkPermission('OPEN', $entry, [], true);

        $fileName = TextNormalizer::toKey($entry->getTitle());

        return new StreamedResponse(function () use ($entry, $user): void {
            echo $this->exportManager->generatePdfForEntry($entry, $user);
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.$fileName.'.pdf',
        ]);
    }

    /**
     * Exports entries.
     */
    #[Route(path: '/{id}/entries/export', name: 'claro_claco_form_entries_export', methods: ['GET'])]
    public function clacoFormEntriesExportAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        ResourceNode $resourceNode
    ): BinaryFileResponse {
        $this->checkPermission('FOLLOW', $resourceNode, [], true);

        $clacoForm = $this->om->getRepository(ClacoForm::class)->findOneBy(['resourceNode' => $resourceNode]);
        $export = $this->exportManager->exportEntries($clacoForm);

        return new BinaryFileResponse($export[0], 200, [
            'Content-Disposition' => 'attachment; filename='.$export[1],
        ]);
    }

    /**
     * Changes owner of an entry.
     */
    #[Route(path: '/entry/{entry}/user/{user}/change', name: 'claro_claco_form_entry_user_change', methods: ['PUT'])]
    public function entryOwnerChangeAction(
        #[MapEntity(mapping: ['entry' => 'uuid'])]
        Entry $entry,
        #[MapEntity(mapping: ['user' => 'uuid'])]
        User $user
    ): JsonResponse {
        $this->checkPermission('ADMINISTRATE', $entry, [], true);

        $updatedEntry = $this->clacoFormManager->changeEntryOwner($entry, $user);
        $serializedEntry = $this->serializer->serialize($updatedEntry);

        return new JsonResponse($serializedEntry, 200);
    }

    /**
     * Switches lock of an entry.
     */
    #[Route(path: '/entry/{entry}/lock/switch', name: 'claro_claco_form_entry_lock_switch', methods: ['PUT'])]
    public function entryLockSwitchAction(
        #[MapEntity(mapping: ['entry' => 'uuid'])]
        Entry $entry
    ): JsonResponse {
        $this->checkPermission('ADMINISTRATE', $entry, [], true);

        $updatedEntry = $this->clacoFormManager->switchEntryLock($entry);
        $serializedEntry = $this->serializer->serialize($updatedEntry);

        return new JsonResponse($serializedEntry, 200);
    }

    #[Route(path: '/entries/lock/{locked}/switch', name: 'claro_claco_form_entries_lock_switch', methods: ['PUT'])]
    public function entriesLockSwitchAction(int $locked, Request $request): JsonResponse
    {
        $ids = $this->decodeRequest($request);
        $entries = $this->om->getRepository(Entry::class)->findBy(['uuid' => $ids]);

        $clacoForms = [];

        foreach ($entries as $entry) {
            $clacoForm = $entry->getClacoForm();
            $clacoFormId = $clacoForm->getId();

            if (!isset($clacoForms[$clacoFormId])) {
                $clacoForms[$clacoFormId] = $clacoForm;
            }
        }
        foreach ($clacoForms as $clacoForm) {
            $this->checkPermission('ADMINISTRATE', $clacoForm->getResourceNode(), [], true);
        }

        $updatedEntries = $this->clacoFormManager->switchEntriesLock($entries, 1 === intval($locked));
        $serializedEntries = [];

        foreach ($updatedEntries as $entry) {
            $serializedEntries[] = $this->serializer->serialize($entry);
        }

        return new JsonResponse($serializedEntries, 200);
    }

    #[Route(path: '/entry/{entry}/field/{field}/file/download', name: 'claro_claco_form_field_value_file_download', methods: ['GET'])]
    public function downloadAction(
        #[MapEntity(mapping: ['entry' => 'uuid'])]
        Entry $entry,
        string $field
    ): StreamedResponse|JsonResponse {
        $formField = $this->om->getRepository(Field::class)->findByFieldFacetUuid($field);
        if (empty($formField) || FieldFacet::FILE_TYPE !== $formField->getType()) {
            return new JsonResponse(null, 404);
        }
        $fieldValue = $this->clacoFormManager->getFieldValueByEntryAndField($entry, $formField);
        $data = $fieldValue->getFieldFacetValue()->getValue();

        if (empty($data)) {
            return new JsonResponse(null, 404);
        }
        $response = new StreamedResponse();
        $path = $this->filesDir.DIRECTORY_SEPARATOR.preg_replace('#^\.\.\/files\/#', '', $data['url']); // TODO : files part should not be stored in the DB

        $response->setCallBack(
            function () use ($path): void {
                readfile($path);
            }
        );
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$data['name']);
        $response->headers->set('Content-Type', $data['mimeType']);
        $response->headers->set('Connection', 'close');

        return $response->send();
    }

    /**
     * Returns list of codes of all countries present in all entries.
     */
    #[Route(path: '/{clacoForm}/entries/used/countries', name: 'claro_claco_form_used_countries_load', methods: ['GET'])]
    public function entriesUsedCountriesLoadAction(
        #[MapEntity(mapping: ['clacoForm' => 'uuid'])]
        ClacoForm $clacoForm
    ): JsonResponse {
        $this->checkPermission('OPEN', $clacoForm->getResourceNode(), [], true);

        $countries = $this->clacoFormManager->getAllUsedCountriesCodes($clacoForm);

        return new JsonResponse($countries, 200);
    }
}
