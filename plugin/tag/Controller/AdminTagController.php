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

use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Manager\TagManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('claroline_tag_admin_tool')")
 */
class AdminTagController extends Controller
{
    private $tagManager;

    /**
     * @DI\InjectParams({
     *     "tagManager" = @DI\Inject("claroline.manager.tag_manager")
     * })
     */
    public function __construct(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }

    /**
     * @EXT\Route(
     *     "/admin/tags/management",
     *     name="claro_tag_admin_tags_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminTagsManagementAction()
    {
        return array();
    }

    /**
     * @EXT\Route(
     *     "/admin/tags/display/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_tag_admin_tags_display",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="name","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminTagsDisplayAction(
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC',
        $page = 1,
        $max = 50
    ) {
        $datas = array();
        $datas['#'] = array();
        $tags = $this->tagManager->getPlatformTags(
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );
        $tagsList = array();

        foreach ($tags as $tag) {
            $tagsList[] = $tag;
        }
        $taggedObjects = $this->tagManager->getTaggedObjectsByTags($tagsList);

        foreach ($taggedObjects as $taggedObject) {
            $tag = $taggedObject->getTag();
            $tagId = $tag->getId();
            $tagName = $tag->getName();
            $objectId = $taggedObject->getObjectId();
            $objectClass = $taggedObject->getObjectClass();
            $objectName = $taggedObject->getObjectName();

            $firstChar = strtoupper(substr($tagName, 0, 1));
            $isNormalChar = ctype_alpha($firstChar);

            if (!$isNormalChar) {
                $firstChar = '#';
            }

            if (!isset($datas[$firstChar])) {
                $datas[$firstChar] = array();
            }

            if (!isset($datas[$firstChar][$tagName])) {
                $datas[$firstChar][$tagName] = array();
                $datas[$firstChar][$tagName]['tag_id'] = $tagId;
                $datas[$firstChar][$tagName]['objects'] = array();
            }

            if (!isset($datas[$firstChar][$tagName]['objects'][$objectClass])) {
                $datas[$firstChar][$tagName]['objects'][$objectClass] = array();
            }
            $datas[$firstChar][$tagName]['objects'][$objectClass][] = array(
                'id' => $objectId,
                'name' => $objectName,
                'tagged_object_id' => $taggedObject->getId(),
            );
        }

        return array(
            'pager' => $tags,
            'search' => $search,
            'datas' => $datas,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'page' => $page,
            'max' => $max,
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/tag/{tag}/delete",
     *     name="claro_tag_admin_tag_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function adminTagDeleteAction(Tag $tag)
    {
        $this->tagManager->deleteTag($tag);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/tagged/object/{taggedObject}/delete",
     *     name="claro_tag_admin_tagged_object_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function adminTaggedObjectDeleteAction(TaggedObject $taggedObject)
    {
        $this->tagManager->deleteTaggedObject($taggedObject);

        return new JsonResponse('success', 200);
    }
}
