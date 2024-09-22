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

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Manager\TagManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     *
     */
    #[Route(path: '/{id}/object', name: 'list_objects', methods: ['GET'])]
    public function listObjectsAction(#[MapEntity(class: 'Claroline\TagBundle\Entity\Tag', mapping: ['id' => 'uuid'])]
    Tag $tag, Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->crud->list(TaggedObject::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['tag' => $tag->getUuid()]]
            ))
        );
    }

    /**
     * Adds a tag to a collection of taggable objects.
     * NB. If the tag does not exist, it will be created if the user has the correct rights.
     *
     */
    #[Route(path: '/{tag}/object', name: 'add_objects', methods: ['POST'])]
    public function addObjectsAction(string $tag, Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $taggedObjects = $this->manager->tagData([$tag], $this->decodeRequest($request));

        return new JsonResponse(
            !empty($taggedObjects) ? $this->serializer->serialize($taggedObjects[0]->getTag()) : null
        );
    }

    
    #[Route(path: '/{id}/object', name: 'remove_objects', methods: ['DELETE'])]
    public function removeObjectsAction(#[MapEntity(class: 'Claroline\TagBundle\Entity\Tag', mapping: ['id' => 'uuid'])]
    Tag $tag, Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $this->manager->removeTagFromObjects($tag, $this->decodeRequest($request));

        return new JsonResponse(null, 204);
    }
}
