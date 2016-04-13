<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater060700 extends Updater
{
    private $om;
    private $kernelDir;

    public function __construct(ContainerInterface $container)
    {
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->kernelDir = $container->getParameter('kernel.root_dir');
    }

    public function postUpdate()
    {
        $this->updateThemes();
    }

    private function updateThemes()
    {
        $this->log('Updating platform themes...');

        $removables = [
            'Bootstrap Default',
            'Claroline Orange',
            'Claroline Gold',
            'Claroline Dark',
        ];

        $themes = $this->om->getRepository('ClarolineCoreBundle:Theme\Theme')->findAll();

        foreach ($themes as $theme) {
            if (in_array($theme->getName(), $removables)) {
                $this->log("Found unsupported theme '{$theme->getName()} (to be removed)'...");
                $this->om->remove($theme);
            } elseif (empty($theme->getName())) {
                // this is what happened when a custom theme couldn't be generated due to permission errors...
                $this->log('Found empty theme (to be removed)...');
                $this->om->remove($theme);
            }
        }

        $this->om->flush();
    }
}
