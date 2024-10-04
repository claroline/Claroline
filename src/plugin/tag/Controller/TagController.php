<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Controller;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Manager\TagManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: 'tag', name: 'apiv2_tag_')]
class TagController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TagManager $manager
    ) {
        $this->authorization = $authorization;
    }

    public static function getClass(): string
    {
        return Tag::class;
    }

    public static function getName(): string
    {
        return 'tag';
    }

    /**
     * List all objects linked to a Tag.
     */
    #[Route(path: '/{id}/object', name: 'list_objects', methods: ['GET'])]
    public function listObjectsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Tag $tag,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('OPEN', $tag, [], true);

        $finderQuery->addFilter('tag', $tag->getUuid());

        $tags = $this->crud->search(TaggedObject::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $tags->toResponse();
    }

    #[Route(path: '/{id}/object', name: 'remove_objects', methods: ['DELETE'])]
    public function removeObjectsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Tag $tag,
        Request $request
    ): JsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $this->manager->removeTagFromObjects($tag, $this->decodeRequest($request));

        return new JsonResponse(null, 204);
    }
}
