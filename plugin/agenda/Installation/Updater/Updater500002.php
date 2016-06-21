<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Installation\Updater;

use Claroline\CoreBundle\Entity\Plugin;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater500002 extends Updater
{
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $plugin = $this->om
            ->getRepository('Claroline\CoreBundle\Entity\Plugin')
            ->findOneBy([
                'vendorName' => 'Claroline',
                'bundleName' => 'AgendaBundle',
            ]);

        $this->updateExtension('tool', 'agenda', $plugin);
        $this->updateExtension('widget', 'agenda', $plugin);

        $this->om->flush();
    }

    private function updateExtension($type, $name, Plugin $plugin)
    {
        $class = $type === 'tool' ?
            'Claroline\CoreBundle\Entity\Tool\Tool' :
            'Claroline\CoreBundle\Entity\Widget\Widget';

        $target = $this->om
            ->getRepository($class)
            ->findOneBy(['name' => $name]);

        if ($target) {
            $this->log("Updating {$name} {$type}...");
            $target->setName("{$name}_");
            $target->setPlugin($plugin);
        }
    }
}
