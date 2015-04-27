<?php
/**
 * This file is part of the Claroline Connect package
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

/**
 * Class LikeActionManager
 * @package Icap\SocialmediaBundle\Manager
 *
 * @DI\Service("icap.social_media.manager.share_action")
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
     * @DI\InjectParams({
     *      "em"    = @DI\Inject("doctrine.orm.entity_manager")
     * })
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->shareActionRepository = $em->getRepository("IcapSocialmediaBundle:ShareAction");
    }

    public function createShare(Request $request, ShareAction $share)
    {
        $resourceId = $request->get("resourceId");
        $share->setResource($resourceId);
        if ($resourceId === null) {
            $url = $request->get("url");
            $title = $request->get("title");
            $share->setUrl($url);
            $share->setTitle($title);
        }
        $network = $request->get("network");
        $share->setNetwork($network);

        $this->em->persist($share);
        $this->em->flush();
    }
} 