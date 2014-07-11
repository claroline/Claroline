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
use Symfony\Component\DependencyInjection\ContainerInterface;


class Updater030100
{
	private $container;
    private $om;
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $this->container->get('doctrine.orm.entity_manager');
    }

    public function postUpdate()
    {
        $this->updateCompetenceTools();
        $this->log('Setting desktop tools visibility...');
        $desktopTools = $this->om->getRepository('ClarolineCoreBundle:Tool\OrderedTool')
            ->findBy(array('workspace' => null));

        for ($i = 0, $count = count($desktopTools); $i < $count; ++$i) {
            $desktopTools[$i]->setVisibleInDesktop(true);

            if ($i % 50 === 0) {
                $this->om->flush();
            }
        }

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
