<?php

namespace Icap\BadgeBundle\Controller;

use Claroline\CoreBundle\Entity\Badge\BadgeCollection;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/badge/collection")
 */
class CollectionController extends Controller
{
    /**
     * @Route("/{slug}/{locale}", name="claro_badge_collection_share_view", defaults={"locale"= "fr"})
     * @Template
     */
    public function shareViewAction(Request $request, BadgeCollection $collection, $locale)
    {
        $request->setLocale($locale);

        if (!$collection->isIsShared()) {
            throw $this->createNotFoundException("Collection not shared.");
        }

        if (!$this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $showBanner = false;
        }
        else {
            /** @var User|string $user */
            $user = $this->getUser();

            $showBanner = ($user === $collection->getUser());
        }

        return array(
            'collection' => $collection,
            'user'       => $collection->getUser(),
            'showBanner' => $showBanner
        );
    }
}
