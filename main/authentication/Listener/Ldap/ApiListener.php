<?php

namespace Claroline\AuthenticationBundle\Listener\Ldap;

use Claroline\AuthenticationBundle\Manager\Ldap\LdapManager;
use  Claroline\CoreBundle\Event\User\MergeUsersEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var LdapManager */
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.ldap_manager")
     * })
     *
     * @param LdapManager $manager
     */
    public function __construct(LdapManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of LdapUser nodes
        $ldapUserCount = $this->manager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineAuthenticationBundle] updated LdapUser count: $ldapUserCount");
    }
}
