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

/**
 * @EXT\Route("/clacoformentry")
 */
class EntryController extends AbstractCrudController
{
    /* @var ClacoFormManager */
    protected $manager;

    /**
     * EntryController constructor.
     *
     * @param ClacoFormManager $manager
     */
    public function __construct(ClacoFormManager $manager)
    {
        $this->manager = $manager;
    }

    public function getClass()
    {
        return 'Claroline\ClacoFormBundle\Entity\Entry';
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
     * @EXT\Route(
     *     "/clacoform/{clacoForm}/entries/list",
     *     name="apiv2_clacoformentry_list"
     * )
     * @EXT\ParamConverter(
     *     "clacoForm",
     *     class="ClarolineClacoFormBundle:ClacoForm",
     *     options={"mapping": {"clacoForm": "uuid"}}
     * )
     *
     * @param ClacoForm $clacoForm
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function entriesListAction(ClacoForm $clacoForm, Request $request)
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
                    $filters['field_'.$key] = $value;
                }
            }
            $params['filters'] = $filters;
        }
        $data = $this->finder->search('Claroline\ClacoFormBundle\Entity\Entry', $params);

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *    "/clacoform/{clacoForm}/file/upload",
     *    name="apiv2_clacoformentry_file_upload"
     * )
     * @EXT\ParamConverter(
     *     "clacoForm",
     *     class="ClarolineClacoFormBundle:ClacoForm",
     *     options={"mapping": {"clacoForm": "uuid"}}
     * )
     *
     * @param ClacoForm $clacoForm
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function uploadAction(ClacoForm $clacoForm, Request $request)
    {
        $files = $request->files->all();
        $data = [];

        foreach ($files as $file) {
            $data[] = $this->manager->registerFile($clacoForm, $file);
        }

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/clacoform/{clacoForm}/{entry}/next",
     *     name="apiv2_clacoformentry_next"
     * )
     * @EXT\ParamConverter(
     *     "clacoForm",
     *     class="ClarolineClacoFormBundle:ClacoForm",
     *     options={"mapping": {"clacoForm": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "entry",
     *     class="ClarolineClacoFormBundle:Entry",
     *     options={"mapping": {"entry": "uuid"}}
     * )
     *
     * @param ClacoForm $clacoForm
     * @param Entry     $entry
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function nextAction(ClacoForm $clacoForm, Entry $entry, Request $request)
    {
        $params = $request->query->all();
        $filters = array_key_exists('filters', $params) ? $params['filters'] : [];
        $filters['clacoForm'] = $clacoForm->getId();
        $sortBy = array_key_exists('sortBy', $params) ? $params['sortBy'] : null;

        //array map is not even needed; objects are fine here
        /** @var Entry[] $data */
        $data = $this->finder->get(Entry::class)->find($filters, $sortBy, 0, -1, false/*, [Options::SQL_ARRAY_MAP]*/);
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
     * @EXT\Route(
     *     "/clacoform/{clacoForm}/{entry}/previous",
     *     name="apiv2_clacoformentry_previous"
     * )
     * @EXT\ParamConverter(
     *     "clacoForm",
     *     class="ClarolineClacoFormBundle:ClacoForm",
     *     options={"mapping": {"clacoForm": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "entry",
     *     class="ClarolineClacoFormBundle:Entry",
     *     options={"mapping": {"entry": "uuid"}}
     * )
     *
     * @param ClacoForm $clacoForm
     * @param Entry     $entry
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function previousAction(ClacoForm $clacoForm, Entry $entry, Request $request)
    {
        $params = $request->query->all();
        $filters = array_key_exists('filters', $params) ? $params['filters'] : [];
        $filters['clacoForm'] = $clacoForm->getId();
        $sortBy = array_key_exists('sortBy', $params) ? $params['sortBy'] : null;

        //array map is not even needed; objects are fine here
        /** @var Entry[] $data */
        $data = $this->finder->get(Entry::class)->find($filters, $sortBy, 0, -1, false/*, [Options::SQL_ARRAY_MAP]*/);
        $prev = null;

        foreach ($data as $position => $value) {
            if ($value->getId() === $entry->getId()) {
                $prev = $position - 1;
            }
        }

        $previousEntry = array_key_exists($prev, $data) ? $data[$prev] : end($data);

        return new JsonResponse($this->serializer->serialize($previousEntry), 200);
    }
}
