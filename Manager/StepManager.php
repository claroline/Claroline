<?php

namespace Innova\PathBundle\Manager;

use Innova\PathBundle\Entity\Step;
use Doctrine\Common\Persistence\ObjectManager;

class StepManager
{
    /**
     * 
     * @var \Doctrine\Common\Persistence\ObjectManager $om
     */
    protected $om;

    /**
     * Class constructor
     * @param \Doctrine\Common\Persistence\ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Get all resource nodes linked to the step
     * @param Step $step
     * @return array
     */
    public function getStepResourceNodes(Step $step)
    {
        $resourceNodes = array();
        $step2ResourceNodes = $this->om->getRepository('InnovaPathBundle:Step2ResourceNode')->findBy(array('step' => $step, 'excluded' => false));

        $nonDigitalRepo = $this->om->getRepository('InnovaPathBundle:NonDigitalResource');
        foreach ($step2ResourceNodes as $step2ResourceNode) {
            if ($step2ResourceNode->getResourceNode()->getClass() == "Innova\PathBundle\Entity\NonDigitalResource") {
                $resourceNodes["nonDigital"][] = $nonDigitalRepo->findOneByResourceNode($step2ResourceNode->getResourceNode());
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
        $step2ResourceNodes = $this->om->getRepository('InnovaPathBundle:Step2ResourceNode')->findBy(array('step' => $step, 'propagated' => true));

        $nonDigitalRepo = $this->om->getRepository('InnovaPathBundle:NonDigitalResource');
        foreach ($step2ResourceNodes as $step2ResourceNode) {
            if ($step2ResourceNode->getResourceNode()->getClass() == "Innova\PathBundle\Entity\NonDigitalResource") {
                $resourceNodes["nonDigital"][] = $nonDigitalRepo->getRepository('InnovaPathBundle:NonDigitalResource')->findOneByResourceNode($step2ResourceNode->getResourceNode());
            }
            else {
                $resourceNodes["digital"][] = $step2ResourceNode->getResourceNode();
            }
        }

        return $resourceNodes;
    }

    public function editResourceNodeRelation(Step $step, $resourceNodeId, $excluded, $propagated, $order)
    {
        $step2ressourceNode = $this->em->getRepository('InnovaPathBundle:Step2ResourceNode')->findOneBy(array (
            'step' => $step,
            'resourceNode' => $resourceNodeId,
            'excluded' => $excluded,
        ));
    
        if (!$step2ressourceNode) {
            $step2ressourceNode = new Step2ResourceNode();
        }
    
        $step2ressourceNode->setResourceNode($this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($resourceNodeId));
        $step2ressourceNode->setStep($step);
        $step2ressourceNode->setExcluded($excluded);
        $step2ressourceNode->setPropagated($propagated);
        $step2ressourceNode->setResourceOrder($order);
    
        $this->om->persist($step2ressourceNode);
        $this->om->flush();
    
        return $step2ressourceNode;
    }
    
    public function edit($id, $jsonStep, $path, $parent, $lvl, $order)
    {
        if ($id == null) {
            $step = new Step();
        } else {
            $step = $this->om->getRepository('InnovaPathBundle:Step')->findOneById($id);
        }

        $step->setPath($path);
        $step->setName($jsonStep->name);
        $step->setStepOrder($order);
        $stepType = $this->om->getRepository('InnovaPathBundle:StepType')->findOneById($jsonStep->type);
        $step->setStepType($stepType);
        $stepWho = $this->om->getRepository('InnovaPathBundle:StepWho')->findOneById($jsonStep->who);
        $step->setStepWho($stepWho);
        $stepWhere = $this->om->getRepository('InnovaPathBundle:StepWhere')->findOneById($jsonStep->where);
        $parent = $this->om->getRepository('InnovaPathBundle:Step')->findOneById($parent);
        $step->setParent($parent);
        $step->setLvl($lvl);
        $step->setStepWhere($stepWhere);
        $step->setDuration(new \DateTime("00-00-00 ".intval($jsonStep->durationHours).":".intval($jsonStep->durationMinutes).":00"));
        $step->setExpanded($jsonStep->expanded);
        $step->setWithTutor($jsonStep->withTutor);
        $step->setWithComputer($jsonStep->withComputer);
        $step->setInstructions($jsonStep->instructions);
        $step->setImage($jsonStep->image);

        $this->om->persist($step);
        $this->om->flush();

        return $step;
    }
}
