<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AudioPlayerBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AudioPlayerBundle\Entity\Resource\SectionComment;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/audio/comment', name: 'apiv2_resource_audio_comment_')]
class SectionCommentController extends AbstractCrudController
{
    public static function getName(): string
    {
        return 'resource_audio_comment';
    }

    public static function getClass(): string
    {
        return SectionComment::class;
    }

    public function getIgnore(): array
    {
        return ['list', 'get'];
    }

    /**
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    #[Route(path: '/{resourceNode}/list/{type}', name: 'list')]
    public function listByResourceAction(ResourceNode $resourceNode, string $type, Request $request): JsonResponse
    {
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['resourceNode'] = $resourceNode->getUuid();
        $params['hiddenFilters']['type'] = $type;

        return new JsonResponse(
            $this->crud->list(SectionComment::class, $params)
        );
    }
}
