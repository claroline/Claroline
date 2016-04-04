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
use Doctrine\ORM\EntityManager;

class Updater021602 extends Updater
{
    /** @var EntityManager */
    private $em;

    public function __construct($container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    public function postUpdate()
    {
        $this->repairPublicProfileUrls();
    }

    private function repairPublicProfileUrls()
    {
        $this->log('Repairing public profile urls...');

        $users = $this->em->getRepository('ClarolineCoreBundle:User')->findAll();

        for ($i = 0, $count = count($users); $i < $count; ++$i) {
            if (false !== strpos($users[$i]->getPublicUrl(), ' ')) {
                $repairedUrl = str_replace(' ', '-', $users[$i]->getPublicUrl());
                $users[$i]->setPublicUrl($repairedUrl);
            }

            if ($i % 100 === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();
    }
}
