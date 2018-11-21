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

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Manager\TagManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use User;

/**
 * @ApiMeta(
 *     class="Claroline\TagBundle\Entity\Tag"
 * )
 * @EXT\Route("tag")
 */
class TagController extends AbstractCrudController
{
    /** @var TagManager */
    private $manager;

    /**
     * TagController constructor.
     *
     * @DI\InjectParams({
     *     "tagManager" = @DI\Inject("claroline.manager.tag_manager")
     * })
     *
     * @param TagManager $tagManager
     */
    public function __construct(
        TagManager $tagManager
    ) {
        $this->manager = $tagManager;
    }

    public function getName()
    {
        return 'tag';
    }

    /**
     * List all objects linked to a Tag.
     *
     * @EXT\Route("/{id}/object", name="apiv2_tag_list_objects")
     * @EXT\Method("GET")
     *
     * @param string  $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listObjectsAction($id, Request $request)
    {
        return new JsonResponse(
            $this->finder->search(TaggedObject::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => [$this->getName() => $id]]
            ))
        );
    }

    /**
     * Adds a taf to a collection of taggable objects.
     * NB. If the tag does not exist, it will be created.
     *
     * @EXT\Route("/{tag}/object", name="apiv2_tag_add_objects")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\Method("POST")
     *
     * @param string  $tag
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addObjectsAction($tag, User $user, Request $request)
    {
        $taggedObjects = $this->manager->tagData([$tag], $this->decodeRequest($request), $user, true);

        return new JsonResponse(
            !empty($taggedObjects) ? $this->serializer->serialize($taggedObjects[0]->getTag()) : null
        );
    }

    /**
     * @EXT\Route("/{id}/object", name="apiv2_tag_remove_objects")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\Method("DELETE")
     *
     * @param string  $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeObjectsAction($id, Request $request)
    {
        // TODO : implement
        return new JsonResponse(null, 204);
    }
}
