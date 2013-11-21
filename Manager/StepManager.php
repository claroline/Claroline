<?php

namespace Innova\PathBundle\Manager;

use Innova\PathBundle\Entity\Step;
use Doctrine\ORM\EntityManager;

class StepManager
{
    private $step;
    private $step2resource;
    private $resource;
    private $manager;
    private $nonDigitalResource;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
        $this->step = $manager->getRepository('InnovaPathBundle:Step');
        $this->resource = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $this->step2resource = $manager->getRepository('InnovaPathBundle:Step2ResourceNode');
        $this->nonDigitalResource = $manager->getRepository('InnovaPathBundle:NonDigitalResource');
    }

    public function getStepResourceNodes(Step $step)
    {
        $resourceNodes = array();
        $step2ResourceNodes = $this->manager->getRepository('InnovaPathBundle:Step2ResourceNode')->findBy(array('step' => $step, 'excluded' => false));

        foreach ($step2ResourceNodes as $step2ResourceNode) {
            if ($step2ResourceNode->getResourceNode()->getClass() == "Innova\PathBundle\Entity\NonDigitalResource") {
                $resourceNodes["nonDigital"][] =  $this->nonDigitalResource->findOneByResourceNode($step2ResourceNode->getResourceNode());
            }
            else {
                $resourceNodes["digital"][] = $step2ResourceNode->getResourceNode();
            }
        }
        return $resourceNodes;
    }

    public function getStepPropagatedResourceNodes(Step $step)
    {
        $resourceNodes = array();
        $step2ResourceNodes = $this->manager->getRepository('InnovaPathBundle:Step2ResourceNode')->findBy(array('step' => $step, 'propagated' => true));

        foreach ($step2ResourceNodes as $step2ResourceNode) {
            if ($step2ResourceNode->getResourceNode()->getClass() == "Innova\PathBundle\Entity\NonDigitalResource") {
                $resourceNodes["nonDigital"][] = $this->nonDigitalResource->findOneByResourceNode($step2ResourceNode->getResourceNode());
            }
            else {
                $resourceNodes["digital"][] = $step2ResourceNode->getResourceNode();
            }
        }

        return $resourceNodes;
    }
}
