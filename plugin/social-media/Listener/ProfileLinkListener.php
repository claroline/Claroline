<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 5/5/15
 */

namespace Icap\SocialmediaBundle\Listener;

use Claroline\CoreBundle\Event\Profile\ProfileLink;
use Claroline\CoreBundle\Event\Profile\ProfileLinksEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ResourceActionsListener.
 *
 * @DI\Service
 */
class ProfileLinkListener
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @DI\InjectParams({
     *  "router"    = @DI\Inject("router"),
     *  "tokenStorage"  = @DI\Inject("security.token_storage"),
     * })
     *
     * @param $tokenStorage
     * @param $router
     */
    public function __construct(Router $router, TokenStorageInterface $tokenStorage)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @DI\Observe("profile_link_event")
     *
     * @param ProfileLinksEvent $event
     */
    public function onProfileLinkEvent(ProfileLinksEvent $event)
    {
        $loggedUser = $this->tokenStorage->getToken()->getUser();
        if ($loggedUser !== null && $loggedUser !== 'anon') {
            $profileUser = $event->getUser();
            $profileUrl = $this->router->generate('icap_socialmedia_wall_view',
                array('publicUrl' => $profileUser->getPublicUrl())
            );
            $profileLink = new ProfileLink('socialmedia_wall', $profileUrl);

            $event->addTab($profileLink);
        }
    }
}
