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

use Claroline\CoreBundle\Entity\Tool\Tool;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater030201
{
    private $em;
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    public function postUpdate()
    {
        $this->removeUnimplementedTools();
        $this->createWorkspaceLearningOutcomesTool();

        $this->em->flush();
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

    private function removeUnimplementedTools()
    {
        $this->log('Deleting admin competences subscription tool...');
        $adminCompetencesSubscriptionTool = $this->em
            ->getRepository('ClarolineCoreBundle:Tool\AdminTool')
            ->findOneByName('competence_subscription');

        if ($adminCompetencesSubscriptionTool) {
            $this->em->remove($adminCompetencesSubscriptionTool);
        }

        $this->log('Deleting learning profile tool...');
        $learningProfileTool = $this->em
            ->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneByName('learning_profil');

        if ($learningProfileTool) {
            $this->em->remove($learningProfileTool);
        }
    }

    private function createWorkspaceLearningOutcomesTool()
    {
        $this->log('Creating workspace learning outcomes tool...');
        $learningOutcomesTool = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneBy(array('name' => 'learning_outcomes'));

        if (!$learningOutcomesTool) {
            $wsTool = new Tool();
            $wsTool->setName('learning_outcomes');
            $wsTool->setClass('graduation-cap');
            $wsTool->setIsWorkspaceRequired(true);
            $wsTool->setIsDesktopRequired(false);
            $wsTool->setDisplayableInWorkspace(true);
            $wsTool->setDisplayableInDesktop(false);
            $wsTool->setExportable(false);
            $wsTool->setIsConfigurableInWorkspace(false);
            $wsTool->setIsConfigurableInDesktop(false);

            $this->em->persist($wsTool);
        }
    }
}
