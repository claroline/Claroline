<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Controller\Icon;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\ThemeBundle\Entity\Icon\IconItem;
use Claroline\ThemeBundle\Entity\Icon\IconSet;
use Claroline\ThemeBundle\Manager\IconSetManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/icon_item")
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
     * @Route(
     *     "/{iconSet}/items/list",
     *     name="apiv2_icon_set_items_list",
     *     methods={"GET"}
     * )
     * @EXT\ParamConverter(
     *     "iconSet",
     *     class="Claroline\CoreBundle\Entity\Icon\IconSet",
     *     options={"mapping": {"iconSet": "uuid"}}
     * )
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
     * @Route(
     *     "/{iconSet}/item/update",
     *     name="apiv2_icon_set_item_update",
     *     methods={"POST", "PUT"}
     * )
     * @EXT\ParamConverter(
     *     "iconSet",
     *     class="Claroline\CoreBundle\Entity\Icon\IconSet",
     *     options={"mapping": {"iconSet": "uuid"}}
     * )
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
