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
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiMeta(
 *     class="Claroline\ClacoFormBundle\Entity\Keyword",
 *     ignore={"exist", "copyBulk", "schema", "find", "list"}
 * )
 * @EXT\Route("/clacoformkeyword")
 */
class KeywordController extends AbstractCrudController
{
    /* var FinderProvider */
    protected $finder;

    /**
     * KeywordController constructor.
     *
     * @DI\InjectParams({
     *     "finder" = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param FinderProvider $finder
     */
    public function __construct(FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    public function getName()
    {
        return 'clacoformkeyword';
    }

    /**
     * @EXT\Route(
     *     "/clacoform/{clacoForm}/keywords/list",
     *     name="apiv2_clacoformkeyword_list"
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
    public function categoriesListAction(ClacoForm $clacoForm, Request $request)
    {
        $params = $request->query->all();
        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['clacoForm'] = $clacoForm->getId();
        $data = $this->finder->search('Claroline\ClacoFormBundle\Entity\Keyword', $params);

        return new JsonResponse($data, 200);
    }
}
