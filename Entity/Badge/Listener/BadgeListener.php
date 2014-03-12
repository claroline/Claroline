<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Badge\Listener;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @DI\Service("claroline.entity_listener.badge")
 * @DI\Tag("doctrine.entity_listener")
 */
class BadgeListener
{
    /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler */
    private $platformConfigHandler;

    /** @var \Symfony\Component\Security\Core\SecurityContext */
    private $securityContext;

    /**
     * @DI\InjectParams({
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "securityContext"       = @DI\Inject("security.context")
     * })
     */
    public function __construct(PlatformConfigurationHandler $platformConfigHandler, SecurityContext $securityContext)
    {
        $this->platformConfigHandler = $platformConfigHandler;
        $this->securityContext       = $securityContext;
    }

    /**
     * @param Badge              $badge
     * @param LifecycleEventArgs $event
     */
    public function postLoad(Badge $badge, LifecycleEventArgs $event)
    {
        // Set the locale on tha badge
        $platformLocale = $this->platformConfigHandler->getParameter('locale_language');

        /** @var \Claroline\CoreBundle\Entity\User $user */
        $user           = $this->securityContext->getToken()->getUser();
        $userLocale     = null;

        if ('anon.' !== $user) {
            $userLocale = $user->getLocale();
        }

        $locale = $platformLocale;

        if (null !== $userLocale) {
            $locale = $userLocale;
        }

        $badge->setLocale($locale);
    }
}