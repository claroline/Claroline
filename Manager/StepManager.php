<?php

namespace Innova\PathBundle\Manager;

use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\Service;

use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\Step2ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

/**
 * @Service("innova.manager.step_manager")
 */
class StepManager
{
    private $step;
    private $step2resource;
    private $resource;
    private $manager;

    /**
     * @InjectParams({
     *     "manager" = @Inject("doctrine"),
     * })
     */
    public function __construct($manager)
    {
        $this->manager = $manager;
        $this->step = $manager->getRepository('InnovaPathBundle:Step');
        $this->resource = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $this->step2resource = $manager->getRepository('InnovaPathBundle:Step2ResourceNode');
    }

    public function getStepResourceNodes(Step $step)
    {
        $resourceNodes = array();
        $step2ResourceNodes = $this->manager->getRepository('InnovaPathBundle:Step2ResourceNode')->findByStep($step);

        foreach ($step2ResourceNodes as $step2ResourceNode) {
           $resourceNodes[] = $step2ResourceNode->getResourceNode();
        }

        return $resourceNodes;
    }

    public function getStepPropagatedResourceNodes(Step $step)
    {
        $resourceNodes = array();

        $step2ResourceNodes = $this->manager->getRepository('InnovaPathBundle:Step2ResourceNode')->findBy(array('step' => $step, 'propagated' => true));

        foreach ($step2ResourceNodes as $step2ResourceNode) {
           $resourceNodes[] = $step2ResourceNode->getResourceNode();
        }

        return $resourceNodes;
    }

}
