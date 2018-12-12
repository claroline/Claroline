<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeVinci\CompetencyBundle\Controller\API;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use HeVinci\CompetencyBundle\Entity\Competency;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/competency")
 */
class CompetencyController extends AbstractCrudController
{
    public function getName()
    {
        return 'competency';
    }

    public function getClass()
    {
        return Competency::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list'];
    }

    /**
     * @EXT\Route(
     *     "/root/list",
     *     name="apiv2_competency_root_list"
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function competenciesRootListAction(Request $request)
    {
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['parent'] = null;
        $data = $this->finder->search(Competency::class, $params, [Options::SERIALIZE_MINIMAL]);

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/competency/{id}/list",
     *     name="apiv2_competency_tree_list"
     * )
     * @EXT\ParamConverter(
     *     "competency",
     *     class="HeVinciCompetencyBundle:Competency",
     *     options={"mapping": {"id": "uuid"}}
     * )
     *
     * @param Competency $competency
     * @param Request    $request
     *
     * @return JsonResponse
     */
    public function competenciesTreeListAction(Competency $competency, Request $request)
    {
        $root = $competency;

        while (!is_null($root->getParent())) {
            $root = $root->getParent();
        }
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['uuid'] = $root->getUuid();
        $data = $this->finder->search(Competency::class, $params, [Options::SERIALIZE_MINIMAL, Options::IS_RECURSIVE]);

        return new JsonResponse($data, 200);
    }

    public function getOptions()
    {
        return [
            'get' => [Options::IS_RECURSIVE],
        ];
    }
}
