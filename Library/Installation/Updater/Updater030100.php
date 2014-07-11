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

use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Persistence\ObjectManager;

class Updater030100
{
	private $container;
    private $om;

    public function __construct($container)
    {
        $this->container = $container;
        $this->om = $container->get('doctrine.orm.entity_manager');
    }

    public function postUpdate()
    {
        $this->updateCompetenceTools();

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

    private function updateCompetenceTools()
    {
    	$this->log('Creating admin referential competence tools...');
        $existingTool = $this->om->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findByName('competence_referencial');

        if (count($existingTool) === 0) {
            $competenceReferencial = new AdminTool();
            $competenceReferencial->setName('competence_referencial');
            $competenceReferencial->setClass('graduation-cap');
            $this->om->persist($competenceReferencial);
        }

        $existingTool = $this->om->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findByName('competence_subscription');
        if (count($existingTool) === 0) {    
            $competenceSubscription = new AdminTool();
            $competenceSubscription->setName('competence_subscription');
            $competenceSubscription->setClass('code-fork');
            $this->om->persist($competenceSubscription);
        }
        
        $this->om->flush();
        $this->log('competence tools created ...');
    }
}
