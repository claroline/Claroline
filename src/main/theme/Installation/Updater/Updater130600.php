<?php

namespace Claroline\ThemeBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;
use Claroline\ThemeBundle\Entity\Theme;

class Updater130600 extends Updater
{
    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $config;

    public function __construct(
        ObjectManager $om,
        PlatformConfigurationHandler $config
    ) {
        $this->om = $om;
        $this->config = $config;
    }

    public function preUpdate()
    {
        $toRemove = [
            'Claroline Black',
            'Claroline Mint',
            'Claroline Ruby',
        ];

        foreach ($toRemove as $theme) {
            $this->removeTheme($theme);
        }

        $this->om->flush();
    }

    private function removeTheme(string $themeName)
    {
        /** @var Theme $theme */
        $theme = $this->om->getRepository(Theme::class)->findOneBy([
            'name' => $themeName,
        ]);

        if ($theme) {
            $this->log(sprintf('Remove %s theme...', $themeName));

            // update current platform theme if needed
            if ($theme->getNormalizedName() === $this->config->getParameter('theme')) {
                $this->config->setParameter('theme', 'claroline');
            }

            $this->om->remove($theme);
        }
    }
}
