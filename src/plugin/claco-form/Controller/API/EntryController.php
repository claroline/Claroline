<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/clacoformentry")
 */
class EntryController extends AbstractCrudController
{
    /* @var ClacoFormManager */
    protected $manager;

    public function __construct(ClacoFormManager $manager)
    {
        $this->manager = $manager;
    }

    public function getClass()
    {
        return Entry::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list'];
    }

    public function getName()
    {
        return 'clacoformentry';
    }

    /**
     * @Route("/clacoform/{clacoForm}/entries/list", name="apiv2_clacoformentry_list")
     * @EXT\ParamConverter("clacoForm", class="Claroline\ClacoFormBundle\Entity\ClacoForm", options={"mapping": {"clacoForm": "uuid"}})
     */
    public function entriesListAction(ClacoForm $clacoForm, Request $request): JsonResponse
    {
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
                'keywords',
            ];

            foreach ($params['filters'] as $key => $value) {
                if (!in_array($key, $excludedFilters)) {
                    $filters[$key] = $value;
                }
            }
            $params['filters'] = $filters;
        }
        $data = $this->finder->search('Claroline\ClacoFormBundle\Entity\Entry', $params);

        return new JsonResponse($data, 200);
    }

    /**
     * @Route("/clacoform/{clacoForm}/file/upload", name="apiv2_clacoformentry_file_upload")
     * @EXT\ParamConverter("clacoForm", class="Claroline\ClacoFormBundle\Entity\ClacoForm", options={"mapping": {"clacoForm": "uuid"}})
     */
    public function uploadAction(ClacoForm $clacoForm, Request $request): JsonResponse
    {
        $files = $request->files->all();
        $data = [];

        foreach ($files as $file) {
            $data[] = $this->manager->registerFile($clacoForm, $file);
        }

        return new JsonResponse($data, 200);
    }

    /**
     * @Route("/clacoform/{clacoForm}/{entry}/next", name="apiv2_clacoformentry_next")
     * @EXT\ParamConverter("clacoForm", class="Claroline\ClacoFormBundle\Entity\ClacoForm", options={"mapping": {"clacoForm": "uuid"}})
     * @EXT\ParamConverter("entry", class="Claroline\ClacoFormBundle\Entity\Entry", options={"mapping": {"entry": "uuid"}})
     */
    public function nextAction(ClacoForm $clacoForm, Entry $entry, Request $request): JsonResponse
    {
        $params = $request->query->all();
        $filters = array_key_exists('filters', $params) ? $params['filters'] : [];
        $filters['clacoForm'] = $clacoForm->getId();
        $sortBy = array_key_exists('sortBy', $params) ? $params['sortBy'] : null;

        //array map is not even needed; objects are fine here
        /** @var Entry[] $data */
        $data = $this->finder->get(Entry::class)->find($filters, $sortBy, 0, -1, false);
        $next = null;

        foreach ($data as $position => $value) {
            if ($value->getId() === $entry->getId()) {
                $next = $position + 1;
            }
        }

        $nextEntry = array_key_exists($next, $data) ? $data[$next] : reset($data);

        return new JsonResponse($this->serializer->serialize($nextEntry), 200);
    }

    /**
     * @Route("/clacoform/{clacoForm}/{entry}/previous", name="apiv2_clacoformentry_previous")
     * @EXT\ParamConverter("clacoForm", class="Claroline\ClacoFormBundle\Entity\ClacoForm", options={"mapping": {"clacoForm": "uuid"}})
     * @EXT\ParamConverter("entry", class="Claroline\ClacoFormBundle\Entity\Entry", options={"mapping": {"entry": "uuid"}})
     */
    public function previousAction(ClacoForm $clacoForm, Entry $entry, Request $request): JsonResponse
    {
        $params = $request->query->all();
        $filters = array_key_exists('filters', $params) ? $params['filters'] : [];
        $filters['clacoForm'] = $clacoForm->getId();
        $sortBy = array_key_exists('sortBy', $params) ? $params['sortBy'] : null;

        //array map is not even needed; objects are fine here
        /** @var Entry[] $data */
        $data = $this->finder->get(Entry::class)->find($filters, $sortBy, 0, -1, false);
        $prev = null;

        foreach ($data as $position => $value) {
            if ($value->getId() === $entry->getId()) {
                $prev = $position - 1;
            }
        }

        $previousEntry = array_key_exists($prev, $data) ? $data[$prev] : end($data);

        return new JsonResponse($this->serializer->serialize($previousEntry), 200);
    }

    /**
     * Changes status of an entry.
     *
     * @Route("/entry/{entry}/status/change", name="claro_claco_form_entry_status_change")
     * @EXT\ParamConverter("entry", class="Claroline\ClacoFormBundle\Entity\Entry", options={"mapping": {"entry": "uuid"}})
     */
    public function entryStatusChangeAction(Entry $entry): JsonResponse
    {
        if ($entry->isLocked()) {
            $serializedEntry = $this->serializer->serialize($entry);
        } else {
            $this->manager->checkEntryModeration($entry);
            $updatedEntry = $this->manager->changeEntryStatus($entry);
            $serializedEntry = $this->serializer->serialize($updatedEntry);
        }

        return new JsonResponse($serializedEntry, 200);
    }

    /**
     * Changes status of entries.
     *
     * @Route("/entries/status/{status}/change", name="claro_claco_form_entries_status_change")
     *
     * @param int $status
     */
    public function entriesStatusChangeAction($status, Request $request): JsonResponse
    {
        $entries = [];
        $serializedEntries = [];

        /** @var Entry[] $entriesParams */
        $entriesParams = $this->decodeIdsString($request, Entry::class);
        foreach ($entriesParams as $entryParam) {
            if (!$entryParam->isLocked()) {
                $entries[] = $entryParam;
            }
        }

        foreach ($entries as $entry) {
            $this->manager->checkEntryModeration($entry);
        }

        $updatedEntries = $this->manager->changeEntriesStatus($entries, intval($status));
        foreach ($updatedEntries as $entry) {
            $serializedEntries[] = $this->serializer->serialize($entry);
        }

        return new JsonResponse($serializedEntries, 200);
    }
}
