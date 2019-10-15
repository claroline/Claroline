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
use Claroline\CoreBundle\Entity\User;
use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Manager\TagManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("tag")
 */
class TagController extends AbstractCrudController
{
    /** @var TagManager */
    private $manager;

    /**
     * TagController constructor.
     *
     * @param TagManager $tagManager
     */
    public function __construct(
        TagManager $tagManager
    ) {
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
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function createAction(Request $request, $class)
    {
        $query = $request->query->all();
        $data = $this->decodeRequest($request);

        // Skips creation if a tag already exists with the same name
        $tag = isset($data['meta']['creator']) ?
            $this->manager->getUserTagByNameAndUserId($data['name'], $data['meta']['creator']) :
            $this->manager->getOnePlatformTagByName($data['name']);

        if ($tag) {
            return new JsonResponse($this->serializer->serialize($tag));
        } else {
            $options = $this->options['create'];

            if (isset($query['options'])) {
                $options = $query['options'];
            }

            $object = $this->crud->create(
                $class,
                $this->decodeRequest($request),
                $options
            );

            if (is_array($object)) {
                return new JsonResponse($object, 400);
            }

            return new JsonResponse(
                $this->serializer->serialize($object, $options),
                201
            );
        }
    }

    /**
     * List all objects linked to a Tag.
     *
     * @EXT\Route("/{id}/object", name="apiv2_tag_list_objects")
     * @EXT\ParamConverter("tag", class="ClarolineTagBundle:Tag", options={"mapping": {"id": "uuid"}})
     * @EXT\Method("GET")
     *
     * @param Tag     $tag
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listObjectsAction(Tag $tag, Request $request)
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
        $taggedObjects = $this->manager->tagData([$tag], $this->decodeRequest($request), $user);

        return new JsonResponse(
            !empty($taggedObjects) ? $this->serializer->serialize($taggedObjects[0]->getTag()) : null
        );
    }

    /**
     * @EXT\Route("/{id}/object", name="apiv2_tag_remove_objects")
     * @EXT\ParamConverter("tag", class="ClarolineTagBundle:Tag", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\Method("DELETE")
     *
     * @param Tag     $tag
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeObjectsAction(Tag $tag, Request $request)
    {
        $this->manager->removeTagFromObjects($tag, $this->decodeRequest($request));

        return new JsonResponse(null, 204);
    }
}
