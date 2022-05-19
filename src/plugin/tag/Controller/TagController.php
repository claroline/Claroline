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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Manager\TagManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("tag")
 */
class TagController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TagManager */
    private $manager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TagManager $tagManager
    ) {
        $this->authorization = $authorization;
        $this->manager = $tagManager;
    }

    public function getClass()
    {
        return Tag::class;
    }

    public function getName()
    {
        return 'tag';
    }

    /**
     * List all objects linked to a Tag.
     *
     * @Route("/{id}/object", name="apiv2_tag_list_objects", methods={"GET"})
     * @EXT\ParamConverter("tag", class="Claroline\TagBundle\Entity\Tag", options={"mapping": {"id": "uuid"}})
     */
    public function listObjectsAction(Tag $tag, Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->finder->search(TaggedObject::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => [$this->getName() => $tag->getUuid()]]
            ))
        );
    }

    /**
     * Adds a tag to a collection of taggable objects.
     * NB. If the tag does not exist, it will be created if the user has the correct rights.
     *
     * @Route("/{tag}/object", name="apiv2_tag_add_objects", methods={"POST"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function addObjectsAction(string $tag, Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $taggedObjects = $this->manager->tagData([$tag], $this->decodeRequest($request));

        return new JsonResponse(
            !empty($taggedObjects) ? $this->serializer->serialize($taggedObjects[0]->getTag()) : null
        );
    }

    /**
     * @Route("/{id}/object", name="apiv2_tag_remove_objects", methods={"DELETE"})
     * @EXT\ParamConverter("tag", class="Claroline\TagBundle\Entity\Tag", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function removeObjectsAction(Tag $tag, Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $this->manager->removeTagFromObjects($tag, $this->decodeRequest($request));

        return new JsonResponse(null, 204);
    }
}
