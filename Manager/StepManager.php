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

    public function edit(Step $step, $jsonStep, $path, $parent, $lvl, $order){
        $step->setPath($path);
        $step->setName($jsonStep->name);
        $step->setStepOrder($order);
        $stepType = $this->manager->getRepository('InnovaPathBundle:StepType')->findOneById($jsonStep->type);
        $step->setStepType($stepType);
        $stepWho = $this->manager->getRepository('InnovaPathBundle:StepWho')->findOneById($jsonStep->who);
        $step->setStepWho($stepWho);
        $stepWhere = $this->manager->getRepository('InnovaPathBundle:StepWhere')->findOneById($jsonStep->where);
        $parent = $this->manager->getRepository('InnovaPathBundle:Step')->findOneById($parent);
        $step->setParent($parent);
        $step->setLvl($lvl);
        $step->setStepWhere($stepWhere);
        $step->setDuration(new \DateTime("00-00-00 ".intval($jsonStep->durationHours).":".intval($jsonStep->durationMinutes).":00"));
        $step->setExpanded($jsonStep->expanded);
        $step->setWithTutor($jsonStep->withTutor);
        $step->setWithComputer($jsonStep->withComputer);
        $step->setInstructions($jsonStep->instructions);
        $step->setImage($jsonStep->image);

        $this->manager->persist($step);
        $this->manager->flush();
    }
}
