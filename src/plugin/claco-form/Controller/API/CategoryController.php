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
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/clacoformcategory")
 */
class CategoryController extends AbstractCrudController
{
    public function getClass()
    {
        return 'Claroline\ClacoFormBundle\Entity\Category';
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list'];
    }

    public function getName()
    {
        return 'clacoformcategory';
    }

    /**
     * @Route(
     *     "/clacoform/{clacoForm}/categories/list",
     *     name="apiv2_clacoformcategory_list"
     * )
     * @EXT\ParamConverter(
     *     "clacoForm",
     *     class="ClarolineClacoFormBundle:ClacoForm",
     *     options={"mapping": {"clacoForm": "uuid"}}
     * )
     *
     * @return JsonResponse
     */
    public function categoriesListAction(ClacoForm $clacoForm, Request $request)
    {
        $params = $request->query->all();
        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['clacoForm'] = $clacoForm->getId();
        $data = $this->finder->search('Claroline\ClacoFormBundle\Entity\Category', $params);

        return new JsonResponse($data, 200);
    }
}
