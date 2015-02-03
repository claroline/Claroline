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
        $this->log('Adding competency custom action to activities...');

        $em = $this->container->get('doctrine.orm.entity_manager');
        $typeRepo = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $decoderRepo = $em->getRepository('ClarolineCoreBundle:Resource\MaskDecoder');
        $actionRepo = $em->getRepository('ClarolineCoreBundle:Resource\MenuAction');

        $activityType = $typeRepo->findOneByName('activity');
        $decoder = $decoderRepo->findOneByName('manage-competencies');
        $action = $actionRepo->findOneByName('manage-competencies');

        if (!$decoder) {
            $exp = count($decoderRepo->findByResourceType($activityType));
            $decoder = new MaskDecoder();
            $decoder->setName('manage-competencies');
            $decoder->setResourceType($activityType);
            $decoder->setValue(pow(2, $exp));
            $em->persist($decoder);
        }

        if (!$action) {
            $action = new MenuAction();
            $action->setName('manage-competencies');
            $action->setResourceType($activityType);
            $action->setValue($decoder->getValue());
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
