<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AudioPlayerBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AudioPlayerBundle\Entity\Resource\AudioParams;
use Claroline\AudioPlayerBundle\Entity\Resource\SectionComment;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/audioresourcesectioncomment")
 */
class SectionCommentController extends AbstractCrudController
{
    public function getName()
    {
        return 'audioresourcesectioncomment';
    }

    public function getClass()
    {
        return SectionComment::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list'];
    }

    /**
     * @EXT\Route(
     *     "/{resourceNode}/list/user",
     *     name="apiv2_audioresourcesectioncomment_list_user"
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param ResourceNode $resourceNode
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function userCommentsListAction(ResourceNode $resourceNode, Request $request)
    {
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['resourceNode'] = $resourceNode->getUuid();
        $params['hiddenFilters']['type'] = AudioParams::USER_TYPE;

        return new JsonResponse(
            $this->finder->search(SectionComment::class, $params)
        );
    }
}
