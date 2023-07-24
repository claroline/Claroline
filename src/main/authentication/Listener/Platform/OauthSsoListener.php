<?php

namespace Claroline\AuthenticationBundle\Listener\Platform;

use Claroline\AuthenticationBundle\Manager\AuthenticationManager;
use Claroline\AuthenticationBundle\Manager\OauthManager;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

class OauthSsoListener
{
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var OauthManager */
    private $oauthManager;
    /** @var AuthenticationManager */
    private $authenticationManager;

    public function __construct(
        PlatformConfigurationHandler $config,
        OauthManager $oauthManager,
        AuthenticationManager $authenticationManager
    ) {
        $this->config = $config;
        $this->oauthManager = $oauthManager;
        $this->authenticationManager = $authenticationManager;
    }

    public function onConfig(GenericDataEvent $event)
    {
        $parameters = $this->authenticationManager->getParameters();

        $event->setResponse([
            'authentication' => [
                'help' => $parameters->getHelpMessage(),
                'changePassword' => $parameters->getChangePassword(),
                'internalAccount' => $parameters->getInternalAccount(),
                'showClientIp' => $parameters->getShowClientIp(),
                'sso' => array_map(function (array $sso) {
                    return [
                        'service' => $sso['service'],
                        'label' => isset($sso['display_name']) ? $sso['display_name'] : null,
                        'primary' => isset($sso['client_primary']) ? $sso['client_primary'] : false,
                    ];
                }, $this->oauthManager->getActiveServices()),
            ],
        ]);
    }
}
