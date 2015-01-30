<?php

namespace HeVinci\CompetencyBundle\Installation;

use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function postInstall()
    {
        $this->log('Adding competency management on activities (custom action)...');

        $em = $this->container->get('doctrine.orm.entity_manager');
        $typeRepo = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $decoderRepo = $em->getRepository('ClarolineCoreBundle:Resource\MaskDecoder');
        $actionRepo = $em->getRepository('ClarolineCoreBundle:Resource\MenuAction');

        $activityType = $typeRepo->findOneByName('activity');
        $decoder = $decoderRepo->findOneByName('manage_competencies');
        $action = $actionRepo->findOneByName('manage_competencies');

        if (!$decoder) {
            $exp = count($decoderRepo->findByResourceType($activityType));
            $decoder = new MaskDecoder();
            $decoder->setName('manage_competencies');
            $decoder->setResourceType($activityType);
            $decoder->setValue(pow(2, $exp));
            $em->persist($decoder);
        }

        if (!$action) {
            $action = new MenuAction();
            $action->setName('manage_competencies');
            $action->setResourceType($activityType);
            $action->setValue($decoder->getValue());
            $em->persist($action);
        }

        $em->flush();
    }
}
