<?php

namespace Claroline\ThemeBundle\Listener\Platform;

use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\ThemeBundle\Library\Icon\ResourceIconItemFilename;
use Claroline\ThemeBundle\Manager\IconSetManager;

class ClientListener
{
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var IconSetManager */
    private $iconManager;

    /**
     * ClientListener constructor.
     *
     * @param PlatformConfigurationHandler $config
     * @param IconSetManager               $iconManager
     */
    public function __construct(
        PlatformConfigurationHandler $config,
        IconSetManager $iconManager
    ) {
        $this->config = $config;
        $this->iconManager = $iconManager;
    }

    /**
     * @param GenericDataEvent $event
     */
    public function onConfig(GenericDataEvent $event)
    {
        $icons = $this->iconManager->getIconSetIconsByType(
            $this->iconManager->getActiveResourceIconSet()
        );

        $event->setResponse([
            'theme' => [
                'name' => strtolower($this->config->getParameter('theme')),
                'icons' => array_map(function (ResourceIconItemFilename $icon) {
                    return [
                        'mimeTypes' => $icon->getMimeTypes(),
                        'url' => $icon->getRelativeUrl(),
                    ];
                }, array_values(array_merge(
                    $icons->getDefaultIcons()->getAllIcons(),
                    $icons->getSetIcons()->getAllIcons()
                ))),
            ],
        ]);
    }
}
