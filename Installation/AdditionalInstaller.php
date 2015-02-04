<?php

namespace HeVinci\CompetencyBundle\Installation;

use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function postInstall()
    {
        $this->addActivityCustomAction();
        $this->addCompetencyManagerRole();
    }

    private function addActivityCustomAction()
    {
        $this->log('Adding custom action to activities...');

        $em = $this->container->get('doctrine.orm.entity_manager');
        $typeRepo = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $actionRepo = $em->getRepository('ClarolineCoreBundle:Resource\MenuAction');

        $activityType = $typeRepo->findOneByName('activity');
        $action = $actionRepo->findOneByName('manage-competencies');

        if (!$action) {
            $action = new MenuAction();
            $action->setName('manage-competencies');
            $action->setResourceType($activityType);
            // the new action will be bound to the 'open' permission
            $action->setValue(MaskDecoder::OPEN);
            $em->persist($action);
        }

        $em->flush();
    }

    private function addCompetencyManagerRole()
    {
        $this->log('Adding competency manager role...');

        $role = $this->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Role')
            ->findOneByName('ROLE_COMPETENCY_MANAGER');

        if (!$role) {
            $this->container
                ->get('claroline.manager.role_manager')
                ->createBaseRole('ROLE_COMPETENCY_MANAGER', 'competency_manager');
        }
    }
}
