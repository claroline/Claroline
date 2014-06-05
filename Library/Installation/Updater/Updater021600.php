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

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;

class Updater021600
{
    private $container;
    private $logger;
    /** @var ObjectManager */
    private $om;

    public function __construct($container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {

        $this->log('Updating tools for anonymous...');

        $tool = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName('home');
        $tool->setIsAnonymousExcluded(false);
        $this->om->persist($tool);
        $this->om->flush();

        $tool = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName('resource_manager');
        $tool->setIsAnonymousExcluded(false);
        $this->om->persist($tool);
        $this->om->flush();

        $tool = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName('agenda');
        $tool->setIsAnonymousExcluded(false);
        $this->om->persist($tool);
        $this->om->flush();
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }
}