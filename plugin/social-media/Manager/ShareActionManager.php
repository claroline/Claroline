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
        $return = array();
        $resourceId = $request->get('resourceId');
        if ($resourceId === null) {
            $url = $request->get('url');
            if ($url === null) {
                throw new BadRequestHttpException();
            }
            $share->setUrl($url);
            $return['url'] = $url;
            $title = $request->get('title');
            $share->setTitle($title);
            if ($title !== null) {
                $return['title'] = $title;
            }
        } else {
            $resourceNode = $this->resourceNodeRepository->find($resourceId);
            $share->setResource($resourceNode);
            $return['title'] = $resourceNode->getName();
            $return['url'] = $this->router->generate('claro_resource_open_short', array('node' => $resourceNode->getId()), true);
        }
        $network = $request->get('network');
        $share->setNetwork($network);

        $this->em->persist($share);
        $this->wallItemManager->createWallItem($share);
        $this->em->flush();

        return $return;
    }

    public function countShares(Request $request = null, $criteria = array())
    {
        if ($request !== null) {
            $criteria = $this->getCriteriaFromRequest($request, null, $criteria);
        }

        return $this->shareActionRepository->countShares($criteria);
    }

    private function getCriteriaFromRequest(Request $request = null, User $user = null, $criteria = array())
    {
        if ($user !== null) {
            $criteria['user'] = $user;
        }

        if ($request !== null) {
            $resourceId = $request->get('resourceId');
            if ($resourceId == null) {
                $resourceId = $request->get('resource');
            }
            if ($resourceId !== null) {
                $criteria['resource'] = $resourceId;
            } else {
                $url = $request->get('url');
                $criteria['url'] = $url;
            }
        }

        return $criteria;
    }
}
