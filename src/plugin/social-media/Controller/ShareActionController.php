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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ShareActionController extends Controller
{
    /**
     * @Route("/share/form/{resourceId}", name="icap_socialmedia_share_form", )
     * @ParamConverter("user", converter="current_user")
     * @ParamConverter("resourceNode", class="ClarolineCoreBundle:Resource\ResourceNode", options={"id" = "resourceId"})
     * @Template()
     *
     * @return array
     */
    public function formAction(ResourceNode $resourceNode, User $user)
    {
        $shareManager = $this->getShareActionManager();
        $sharesCount = $shareManager->countShares(null, ['resource' => $resourceNode->getId()]);
        $socialShare = new SocialShare();
        $resourceUrl = $this->generateUrl('claro_index', [], true).
            '#/desktop/workspaces/open/'.$resourceNode->getWorkspace()->getSlug().'/resources/'.$resourceNode->getSlug();

        return [
            'resourceNode' => $resourceNode,
            'networks' => $socialShare->getNetworks(),
            'shares' => $sharesCount,
            'resourceUrl' => $resourceUrl,
        ];
    }

    /**
     * @Route("/share", name="icap_socialmedia_share")
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @Template()
     *
     * @param User $user
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

        $response = [];
        if (null !== $network) {
            $socialShare = new SocialShare();
            $shareLink = $socialShare->getNetwork($network)->getShareLink($options['url'], [$options['title']]);
            $response = new RedirectResponse($shareLink);
        }

        return $response;
    }
}
