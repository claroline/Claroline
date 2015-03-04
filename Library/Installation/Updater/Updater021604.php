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
use Claroline\CoreBundle\Entity\Resource\MenuAction;

class Updater021604 extends Updater
{
    private $container;
    /** @var EntityManager */
    private $em;

    public function __construct($container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    public function postUpdate()
    {
        $this->log('Adding change file action...');

        $fileType = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneByName('file');
        $maskDecoder = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Resource\MaskDecoder')
            ->findOneBy(array('resourceType' => $fileType, 'name' => 'edit'));

        $action = new MenuAction();
        $action->setName('update_file');
        $action->setAsync(true);
        $action->setIsCustom(true);
        $action->setIsForm(false);
        $action->setResourceType($fileType);
        $action->setValue($maskDecoder->getValue());

        $this->em->persist($action);
        $this->em->flush();
    }
}
