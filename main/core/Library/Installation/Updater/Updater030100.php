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

class Updater030100 extends Updater
{
    private $em;

    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    public function postUpdate()
    {
        $this->log('Setting desktop tools visibility...');

        $desktopTools = $this->em->getRepository('ClarolineCoreBundle:Tool\OrderedTool')
            ->findBy(array('workspace' => null));

        for ($i = 0, $count = count($desktopTools); $i < $count; ++$i) {
            $desktopTools[$i]->setVisibleInDesktop(true);

            if ($i % 50 === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();
    }
}
