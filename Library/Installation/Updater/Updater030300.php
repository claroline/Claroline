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
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Widget\Widget;

class Updater030300 extends Updater
{
    private $em;
    private $configHandler;

    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->configHandler = $container->get('claroline.config.platform_config_handler');
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->createBadgeUsageWidget();
        $this->removeUnimplementedTools();
        $this->createWorkspaceLearningOutcomesTool();
        $this->createAuthenticationDirectory();
        $this->usernameRegexUpdate();
        $this->em->flush();
    }

    private function createBadgeUsageWidget()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        try {
            $widgetKey = 'badge_usage';

            $workspaceWidget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')->findOneByName($widgetKey);

            if (is_null($workspaceWidget)) {
                $this->log('Creating workspace badge widget...');
                $widget = new Widget();
                $widget->setName($widgetKey);
                $widget->setConfigurable(true);
                $widget->setIcon('fake/icon/path');
                $widget->setPlugin(null);
                $widget->setExportable(false);
                $widget->setDisplayableInDesktop(false);
                $widget->setDisplayableInWorkspace(true);
                $em->persist($widget);
                $em->flush();
            }
        } catch (MappingException $e) {
            $this->log('A MappingException has been thrown while trying to get Widget repository');
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

    private function createAuthenticationDirectory()
    {
        $authDir = $this->container->getParameter('claroline.param.authentication_directory');

        if (!file_exists($authDir)) {
            $this->log('Creating authentication directory');
            mkdir($authDir);
        }
    }

    private function usernameRegexUpdate()
    {
        $this->log('Updating user name regex...');
        $this->configHandler->setParameter('username_regex', '/^[a-zA-Z0-9@\-_\.]*$/');
    }
}
