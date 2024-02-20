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
use Claroline\ClacoFormBundle\Entity\Keyword;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/clacoformkeyword")
 */
class KeywordController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    public function getClass(): string
    {
        return Keyword::class;
    }

    public function getIgnore(): array
    {
        return ['list'];
    }

    public function getName(): string
    {
        return 'clacoformkeyword';
    }

    /**
     * @Route(
     *     "/clacoform/{clacoForm}/keywords/list",
     *     name="apiv2_clacoformkeyword_list"
     * )
     *
     * @EXT\ParamConverter(
     *     "clacoForm",
     *     class="Claroline\ClacoFormBundle\Entity\ClacoForm",
     *     options={"mapping": {"clacoForm": "uuid"}}
     * )
     */
    public function keywordsListAction(ClacoForm $clacoForm, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $clacoForm->getResourceNode(), [], true);

        $params = $request->query->all();
        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['clacoForm'] = $clacoForm->getId();

        return new JsonResponse(
            $this->crud->list(Keyword::class, $params)
        );
    }

    /**
     * Returns the keyword.
     *
     * @Route("/{clacoForm}/keyword/{value}/excluding/uuid/{uuid}", name="claro_claco_form_get_keyword_by_name_excluding_uuid", defaults={"uuid"=null})
     *
     * @EXT\ParamConverter( "clacoForm", class="Claroline\ClacoFormBundle\Entity\ClacoForm", options={"mapping": {"clacoForm": "uuid"}})
     */
    public function getKeywordByNameExcludingUuidAction(ClacoForm $clacoForm, $value, string $uuid = null): JsonResponse
    {
        $this->checkPermission('EDIT', $clacoForm->getResourceNode(), [], true);

        $keyword = $this->om->getRepository(Keyword::class)->findKeywordByNameExcludingUuid($clacoForm, $value, $uuid);

        if (!empty($keyword)) {
            return new JsonResponse(true);
        }

        return new JsonResponse(false, 204);
    }
}
