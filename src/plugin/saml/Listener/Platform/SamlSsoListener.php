<?php

namespace Claroline\SamlBundle\Listener\Platform;

use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\SymfonyBridgeBundle\Bridge\Container\BuildContainer;

class SamlSsoListener
{
    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var BuildContainer */
    private $idpContainer;

    /**
     * OauthSsoListener constructor.
     */
    public function __construct(
        PlatformConfigurationHandler $config,
        BuildContainer $idpContainer
    ) {
        $this->config = $config;
        $this->idpContainer = $idpContainer;
    }

    public function onConfig(GenericDataEvent $event)
    {
        if ($this->config->getParameter('saml.active')) {
            $parties = $this->idpContainer->getPartyContainer()->getIdpEntityDescriptorStore()->all();
            $buttons = $this->config->getParameter('saml.buttons');

            $event->setResponse([
                'sso' => array_map(function (EntityDescriptor $descriptor) use ($buttons) {
                    $buttonName = $descriptor->getEntityID();
                    if (!empty($buttons[$descriptor->getEntityID()])) {
                        $buttonName = $buttons[$descriptor->getEntityID()];
                    }

                    return [
                        'service' => 'saml',
                        'label' => $buttonName,
                        'primary' => false,
                        'idp' => $descriptor->getEntityID(),
                    ];
                }, $parties),
            ]);
        }
    }
}
