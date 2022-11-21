<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 1/17/17
 */

namespace Claroline\ThemeBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\ThemeBundle\Entity\Icon\IconSet;

class IconSetManager
{
    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var IconSetBuilderManager */
    private $builder;

    public function __construct(
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        IconSetBuilderManager $builder
    ) {
        $this->om = $om;
        $this->config = $config;
        $this->builder = $builder;
    }

    public function createSet(string $setName, \SplFileInfo $archive): array
    {
        $this->builder->generateFromArchive($archive, $setName);

        return [
            'name' => $setName,
            'default' => false,
            'icons' => $this->getSetIcons($setName),
        ];
    }

    public function downloadSet(string $setName): string
    {
        return $this->builder->zip($setName);
    }

    public function deleteSet(string $setName): void
    {
        $existingSets = $this->om->getRepository(IconSet::class)->findBy(['name' => $setName]);
        foreach ($existingSets as $existingSet) {
            $this->om->remove($existingSet);
        }

        $this->builder->removeSetDir($setName);

        $this->om->flush();
    }

    public function getCurrentSet(): array
    {
        $setName = $this->config->getParameter('display.resource_icon_set');

        return $this->getSetIcons($setName);
    }

    public function getAvailableSets(): array
    {
        /** @var IconSet[] $sets */
        $sets = $this->om->getRepository(IconSet::class)->findAll();

        $available = [];
        foreach ($sets as $set) {
            if (empty($available[$set->getName()])) {
                $available[$set->getName()] = [
                    'name' => $set->getName(),
                    'default' => $set->isDefault(),
                    'icons' => $this->getSetIcons($set->getName()),
                ];
            }
        }

        return array_values($available);
    }

    private function getSetIcons(string $setName): array
    {
        $setTypes = [IconSet::RESOURCE_ICON_SET, IconSet::WIDGET_ICON_SET, IconSet::DATA_ICON_SET];

        $icons = [];
        foreach ($setTypes as $setType) {
            /** @var IconSet $set */
            $set = $this->om->getRepository(IconSet::class)->findOneBy([
                'name' => $setName,
                'type' => $setType,
            ]);

            if (empty($set)) {
                // no set defined for the type, we will load the default one
                /** @var IconSet $set */
                $set = $this->om->getRepository(IconSet::class)->findOneBy([
                    'default' => true,
                    'type' => $setType,
                ]);
            }

            $setIcons = [];
            foreach ($set->getIcons() as $icon) {
                if (empty($setIcons[$icon->getName()])) {
                    $setIcons[$icon->getName()] = [
                        'mimeTypes' => [$icon->getMimeType()],
                        'url' => $icon->getRelativeUrl(),
                        'svg' => $icon->isSvg(),
                    ];
                } else {
                    $setIcons[$icon->getName()]['mimeTypes'] = array_merge($setIcons[$icon->getName()]['mimeTypes'], [$icon->getMimeType()]);
                }
            }

            $icons[$setType] = array_values($setIcons);
        }

        return $icons;
    }
}
