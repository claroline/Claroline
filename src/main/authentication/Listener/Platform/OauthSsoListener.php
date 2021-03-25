<?php

namespace Claroline\AuthenticationBundle\Listener\Platform;

use Claroline\AuthenticationBundle\Manager\OauthManager;
use Claroline\CoreBundle\Event\GenericDataEvent;

class OauthSsoListener
{
    /** @var OauthManager */
    private $oauthManager;

    /**
     * OauthSsoListener constructor.
     */
    public function __construct(OauthManager $oauthManager)
    {
        $this->oauthManager = $oauthManager;
    }

    public function onConfig(GenericDataEvent $event)
    {
        $event->setResponse([
            'sso' => array_map(function (array $sso) {
                return [
                    'service' => $sso['service'],
                    'label' => isset($sso['display_name']) ? $sso['display_name'] : null,
                    'primary' => isset($sso['client_primary']) ? $sso['client_primary'] : false,
                ];
            }, $this->oauthManager->getActiveServices()),
        ]);
    }
}
