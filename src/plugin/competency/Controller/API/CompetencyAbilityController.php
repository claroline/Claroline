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
use HeVinci\CompetencyBundle\Entity\CompetencyAbility;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/competency_ability")
 */
class CompetencyAbilityController extends AbstractCrudController
{
    public function getName()
    {
        return 'competency_ability';
    }

    public function getClass()
    {
        return CompetencyAbility::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find'];
    }

    /**
     * @Route(
     *     "/competency/{competency}/list",
     *     name="apiv2_competency_ability_competency_list"
     * )
     * @EXT\ParamConverter(
     *     "competency",
     *     class="HeVinci\CompetencyBundle\Entity\Competency",
     *     options={"mapping": {"competency": "uuid"}}
     * )
     *
     * @return JsonResponse
     */
    public function competencyListAction(Competency $competency, Request $request)
    {
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['competencies'] = [$competency->getUuid()];
        $data = $this->finder->search(CompetencyAbility::class, $params, [Options::SERIALIZE_MINIMAL]);

        return new JsonResponse($data, 200);
    }
}
