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

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiMeta(
 *     class="Claroline\ClacoFormBundle\Entity\Entry",
 *     ignore={"exist", "copyBulk", "schema", "find", "list"}
 * )
 * @EXT\Route("/clacoformentry")
 */
class EntryController extends AbstractCrudController
{
    /* @var FinderProvider */
    protected $finder;

    /* @var ClacoFormManager */
    protected $manager;

    /**
     * EntryController constructor.
     *
     * @DI\InjectParams({
     *     "finder"  = @DI\Inject("claroline.api.finder"),
     *     "manager" = @DI\Inject("claroline.manager.claco_form_manager")
     * })
     *
     * @param FinderProvider   $finder
     * @param ClacoFormManager $manager
     */
    public function __construct(FinderProvider $finder, ClacoFormManager $manager)
    {
        $this->finder = $finder;
        $this->manager = $manager;
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
}
