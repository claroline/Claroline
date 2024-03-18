<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Subscriber\Administration;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Tool\AbstractToolSubscriber;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\ThemeBundle\Entity\ColorCollection;
use Claroline\ThemeBundle\Manager\IconSetManager;
use Claroline\ThemeBundle\Manager\ThemeManager;

/**
 * @deprecated
 */
class ParametersSubscriber extends AbstractToolSubscriber
{
    public function __construct(
        private readonly ThemeManager $themeManager,
        private readonly IconSetManager $iconSetManager,
        private readonly SerializerProvider $serializer,
        private readonly ObjectManager $objectManager
    ) {
    }

    protected static function supportsTool(string $toolName): bool
    {
        return 'parameters' === $toolName;
    }

    /**
     * Displays parameters administration tool.
     */
    protected function onOpen(OpenToolEvent $event): void
    {
        $colorCharts = $this->objectManager->getRepository(ColorCollection::class)->findAll();

        $event->addResponse([
            'availableThemes' => $this->themeManager->getAvailableThemes(),
            'availableIconSets' => $this->iconSetManager->getAvailableSets(),
            'availableColorCharts' => array_map(function (ColorCollection $colorCollection) {
                return $this->serializer->serialize($colorCollection);
            }, $colorCharts),
        ]);
    }
}
