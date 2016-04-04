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
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\Tool;

class Updater030200 extends Updater
{
    private $configHandler;

    public function __construct(ContainerInterface $container)
    {
        $this->configHandler = $container->get('claroline.config.platform_config_handler');
        $this->om = $container->get('doctrine.orm.entity_manager');
    }

    public function postUpdate()
    {
        $this->usernameRegexUpdate();
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

    private function usernameRegexUpdate()
    {
        $this->log('Updating user name regex...');
        $this->configHandler->setParameter('username_regex', '/^[a-zA-Z0-9@_\.]*$/');
    }
}
