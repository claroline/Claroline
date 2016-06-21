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

namespace Icap\SocialmediaBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Icap\SocialmediaBundle\Entity\ShareAction;
use Icap\SocialmediaBundle\Library\SocialShare\SocialShare;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ShareActionController extends Controller
{
    /**
     * @Route("/share/form/{resourceId}", name="icap_socialmedia_share_form", )
     * @ParamConverter("user", converter="current_user")
     * @ParamConverter("resourceNode", class="ClarolineCoreBundle:Resource\ResourceNode", options={"id" = "resourceId"})
     * @Template()
     *
     * @param ResourceNode $resourceNode
     * @param User         $user
     *
     * @return array
     */
    public function formAction(ResourceNode $resourceNode, User $user)
    {
        $shareManager = $this->getShareActionManager();
        $sharesCount = $shareManager->countShares(null, array('resource' => $resourceNode->getId()));
        $socialShare = new SocialShare();
        $resourceUrl = $this->generateUrl('claro_resource_open_short', array('node' => $resourceNode->getId()), true);

        return array(
            'resourceNode' => $resourceNode,
            'networks' => $socialShare->getNetworks(),
            'shares' => $sharesCount,
            'resourceUrl' => $resourceUrl,
        );
    }

    /**
     * @Route("/share", name="icap_socialmedia_share")
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @Template()
     *
     * @param Request $request
     * @param User    $user
     *
     * @return bool
     */
    public function shareAction(Request $request, User $user = null)
    {
        $share = new ShareAction();
        $share->setUser($user);
        $network = $request->get('network');
        $options = $this->getShareActionManager()->createShare($request, $share);
        $this->dispatchShareEvent($share);

        $response = array();
        if ($network !== null) {
            $socialShare = new SocialShare();
            $shareLink = $socialShare->getNetwork($network)->getShareLink($options['url'], array($options['title']));
            $response = new RedirectResponse($shareLink);
        }

        return $response;
    }
}
