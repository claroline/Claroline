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

use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\InstallationBundle\Updater\Updater;

class Updater020102 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $typeText = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneByName('text');

        $decoder = $em->getRepository('ClarolineCoreBundle:Resource\MaskDecoder')
            ->findOneBy(array('resourceType' => $typeText, 'name' => 'write'));

        if (!$decoder) {
            $updateTextDecoder = new MaskDecoder();
            $updateTextDecoder->setValue(pow(2, 6));
            $updateTextDecoder->setName('write');
            $updateTextDecoder->setResourceType($typeText);
            $em->persist($updateTextDecoder);
            $this->log("Adding 'write' permissions for resource 'text'");
            $em->flush();
        } else {
            $this->log("The 'write' permissions for resource 'text' already exists");
        }
    }
}
