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
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiMeta(
 *     class="Claroline\TagBundle\Entity\Tag"
 * )
 * @EXT\Route("tag")
 */
class TagController extends AbstractCrudController
{
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
     * @EXT\Route("/{id}/object", name="apiv2_tag_remove_objects")
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
    }
}
