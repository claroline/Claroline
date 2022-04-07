<?php

namespace Claroline\SamlBundle\Listener\Platform;

use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

class SamlSsoListener
{
    /** @var PlatformConfigurationHandler */
    private $config;

    public function __construct(
        PlatformConfigurationHandler $config
    ) {
        $this->config = $config;
    }

    public function onConfig(GenericDataEvent $event)
    {
        if ($this->config->getParameter('saml.active')) {
            $sso = [];

            $idp = $this->config->getParameter('saml.idp');
            foreach ($idp as $entityId => $idpConfig) {
                if (!isset($idpConfig['active']) || $idpConfig['active']) {
                    $sso[] = [
                        'service' => 'saml',
                        'label' => !empty($idpConfig['label']) ? $idpConfig['label'] : $entityId,
                        'confirm' => !empty($idpConfig['confirm']) ? $idpConfig['confirm'] : null,
                        'primary' => false,
                        'idp' => $entityId,
                    ];
                }
            }

            $event->setResponse([
                'authentication' => [
                    'sso' => $sso,
                ],
            ]);
        }
    }
}
