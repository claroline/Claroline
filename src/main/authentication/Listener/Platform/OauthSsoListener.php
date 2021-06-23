<?php

namespace Claroline\AuthenticationBundle\Listener\Platform;

use Claroline\AuthenticationBundle\Manager\OauthManager;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

class OauthSsoListener
{
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var OauthManager */
    private $oauthManager;

    public function __construct(
        PlatformConfigurationHandler $config,
        OauthManager $oauthManager
    ) {
        $this->config = $config;
        $this->oauthManager = $oauthManager;
    }

    public function onConfig(GenericDataEvent $event)
    {
        $event->setResponse([
            'authentication' => [
                'help' => $this->config->getParameter('authentication.help'),
                'changePassword' => $this->config->getParameter('authentication.changePassword'),
                'internalAccount' => $this->config->getParameter('authentication.internalAccount'),
                'showClientIp' => $this->config->getParameter('authentication.showClientIp'),
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
