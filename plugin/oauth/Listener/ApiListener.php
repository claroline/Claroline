<?php

namespace Icap\OAuthBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Icap\OAuthBundle\Manager\OauthManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
//    /** @var OauthManager */
//    private $oauthManager;
//
//    /**
//     * @DI\InjectParams({
//     *     "$oauthManager" = @DI\Inject("icap.oauth.manager")
//     * })
//     *
//     * @param OauthManager $oauthManager
//     */
//    public function __construct(OauthManager $oauthManager)
//    {
//        $this->oauthManager = $oauthManager;
//    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        //OauthUSer

        $event->addMessage('[IcapOAuthBundle]');
    }
}
