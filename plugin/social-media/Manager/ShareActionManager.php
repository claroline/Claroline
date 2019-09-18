<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 4/22/15
 */

namespace Icap\SocialmediaBundle\Manager;

use Doctrine\ORM\EntityManager;
use Icap\SocialmediaBundle\Entity\ShareAction;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Router;

/**
 * Class LikeActionManager.
 *
 * @DI\Service("icap_socialmedia.manager.share_action")
 */
class ShareActionManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Icap\SocialmediaBundle\Repository\ShareActionRepository
     */
    protected $shareActionRepository;

    /**
     * @var \Claroline\CoreBundle\Repository\ResourceNodeRepository
     */
    protected $resourceNodeRepository;

    /**
     * @var WallItemManager
     */
    protected $wallItemManager;

    protected $router;

    /**
     * @DI\InjectParams({
     *      "em"                = @DI\Inject("doctrine.orm.entity_manager"),
     *      "wallItemManager"   = @DI\Inject("icap_socialmedia.manager.wall_item"),
     *      "router"            = @DI\Inject("router")
     * })
     *
     * @param EntityManager                     $em
     * @param WallItemManager                   $wallItemManager
     * @param \Symfony\Component\Routing\Router $router
     */
    public function __construct(EntityManager $em, WallItemManager $wallItemManager, Router $router)
    {
        $this->em = $em;
        $this->wallItemManager = $wallItemManager;
        $this->router = $router;
        $this->shareActionRepository = $em->getRepository('IcapSocialmediaBundle:ShareAction');
        $this->resourceNodeRepository = $em->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
    }

    public function createShare(Request $request, ShareAction $share)
    {
        $return = [];
        $resourceId = $request->get('resourceId');
        if (null === $resourceId) {
            $url = $request->get('url');
            if (null === $url) {
                throw new BadRequestHttpException();
            }
            $share->setUrl($url);
            $return['url'] = $url;
            $title = $request->get('title');
            $share->setTitle($title);
            if (null !== $title) {
                $return['title'] = $title;
            }
        } else {
            $resourceNode = $this->resourceNodeRepository->find($resourceId);
            $share->setResource($resourceNode);
            $return['title'] = $resourceNode->getName();
            $return['url'] = $this->router->generate('claro_index', [], true).
                '#/desktop/workspaces/open/'.$resourceNode->getWorkspace()->getSlug().'/resources/'.$resourceNode->getSlug();
        }
        $network = $request->get('network');
        $share->setNetwork($network);

        $this->em->persist($share);
        $this->wallItemManager->createWallItem($share);
        $this->em->flush();

        return $return;
    }

    public function countShares(Request $request = null, $criteria = [])
    {
        if (null !== $request) {
            $criteria = $this->getCriteriaFromRequest($request, null, $criteria);
        }

        return $this->shareActionRepository->countShares($criteria);
    }

    private function getCriteriaFromRequest(Request $request = null, User $user = null, $criteria = [])
    {
        if (null !== $user) {
            $criteria['user'] = $user;
        }

        if (null !== $request) {
            $resourceId = $request->get('resourceId');
            if (null === $resourceId) {
                $resourceId = $request->get('resource');
            }
            if (null !== $resourceId) {
                $criteria['resource'] = $resourceId;
            } else {
                $url = $request->get('url');
                $criteria['url'] = $url;
            }
        }

        return $criteria;
    }
}
