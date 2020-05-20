<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Icon;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Icon\IconItem;
use Claroline\CoreBundle\Entity\Icon\IconSet;
use Claroline\CoreBundle\Manager\IconSetManager;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/icon_item")
 */
class IconItemController extends AbstractCrudController
{
    /** @var AuthorizationCheckerInterface */
    protected $authorization;

    /** @var IconSetManager */
    private $iconSetManager;

    /** @var ToolManager */
    private $toolManager;

    /**
     * IconItemController constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param IconSetManager                $iconSetManager
     * @param ToolManager                   $toolManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        IconSetManager $iconSetManager,
        ToolManager $toolManager
    ) {
        $this->authorization = $authorization;
        $this->iconSetManager = $iconSetManager;
        $this->toolManager = $toolManager;
    }

    public function getName()
    {
        return 'icon_item';
    }

    public function getClass()
    {
        return IconItem::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'doc', 'find', 'list', 'create', 'update'];
    }

    /**
     * @EXT\Route(
     *     "/{iconSet}/items/list",
     *     name="apiv2_icon_set_items_list"
     * )
     * @EXT\ParamConverter(
     *     "iconSet",
     *     class="ClarolineCoreBundle:Icon\IconSet",
     *     options={"mapping": {"iconSet": "uuid"}}
     * )
     * @EXT\Method("GET")
     *
     * @param IconSet $iconSet
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function iconSetItemListAction(IconSet $iconSet, Request $request)
    {
        return new JsonResponse($this->finder->search(
            IconItem::class,
            array_merge($request->query->all(), ['hiddenFilters' => ['iconSet' => $iconSet->getUuid()]]),
            [Options::SERIALIZE_MINIMAL]
        ));
    }

    /**
     * @EXT\Route(
     *     "/{iconSet}/item/update",
     *     name="apiv2_icon_set_item_update"
     * )
     * @EXT\ParamConverter(
     *     "iconSet",
     *     class="ClarolineCoreBundle:Icon\IconSet",
     *     options={"mapping": {"iconSet": "uuid"}}
     * )
     * @EXT\Method({"POST", "PUT"})
     *
     * @param IconSet $iconSet
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function iconSetItemUpdateAction(IconSet $iconSet, Request $request)
    {
        if (!$iconSet->isEditable() || !$this->hasToolAccess()) {
            throw new AccessDeniedException();
        }
        $data = json_decode($request->request->all()['iconItem'], true);
        $mimeTypes = [];

        $file = $request->files->all()['file'];
        $relativeUrl = $file ? $this->iconSetManager->uploadIcon($iconSet, $file) : null;

        if (isset($data['mimeTypes'])) {
            $mimeTypes = $data['mimeTypes'];
        } elseif (isset($data['mimeType'])) {
            $mimeTypes = [$data['mimeType']];
        }
        $iconItems = $relativeUrl ? $this->iconSetManager->updateIconItems($iconSet, $mimeTypes, $relativeUrl) : [];

        return new JsonResponse(array_map(function (IconItem $iconItem) {
            return $this->serializer->serialize($iconItem);
        }, $iconItems));
    }

    /**
     * @param string $rights
     *
     * @return bool
     */
    private function hasToolAccess($rights = 'OPEN')
    {
        $tool = $this->toolManager->getAdminToolByName('main_settings');

        return !is_null($tool) && $this->authorization->isGranted($rights, $tool);
    }
}
