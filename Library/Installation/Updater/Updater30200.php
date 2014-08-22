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
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater30200 
{
	private $container;
    private $om;
    private $logger

	public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $this->container->get('doctrine.orm.entity_manager');
    }

    public function postUpdate()
    {
        $this->updateCompetenceTools();  
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

        $existingTool = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneBy(array('name' => 'learning_profil'));

        if (!$existingTool) {
            $wsTool = new Tool();
            $wsTool->setName('learning_profil');
            $wsTool->setClass('graduation-cap');
            $wsTool->setIsWorkspaceRequired(true);
            $wsTool->setIsDesktopRequired(false);
            $wsTool->setDisplayableInWorkspace(true);
            $wsTool->setDisplayableInDesktop(false);
            $wsTool->setExportable(false);
            $wsTool->setIsConfigurableInWorkspace(false);
            $wsTool->setIsConfigurableInDesktop(false);

            $this->om->persist($wsTool);
        }
        
        $this->om->flush();
        $this->log('competence tools created ...');
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