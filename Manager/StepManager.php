<?php

namespace Innova\PathBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\Path;
use Innova\PathBundle\Entity\Step2ResourceNode;

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
     * @param \Innova\PathBundle\Entity\Step $step
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
        $step2ressourceNode = $this->om->getRepository('InnovaPathBundle:Step2ResourceNode')->findOneBy(array (
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

    public function edit($id, \stdClass $jsonStep, Path $path, $parent = null, $lvl, $order)
    {
        if ($id == null) {
            $step = new Step();
        } else {
            $step = $this->om->getRepository('InnovaPathBundle:Step')->findOneById($id);
        }

        $step->setPath($path);
        
        $step->setName($jsonStep->name);
        $step->setParent($parent);
        
        $step->setStepOrder($order);
        $step->setLvl($lvl);
        
        $durationHours = !empty($jsonStep->durationHours) ? intval($jsonStep->durationHours) : 0;
        $durationMinutes = !empty($jsonStep->durationMinutes) ? intval($jsonStep->durationMinutes) : 0;
        $step->setDuration(new \DateTime('00-00-00 ' . $durationHours . ':' . $durationMinutes . ':00'));
        
        if (!empty($jsonStep->withTutor)) {
            $step->setWithTutor($jsonStep->withTutor);
        }
        
        if (!empty($jsonStep->withComputer)) {
            $step->setWithComputer($jsonStep->withComputer);
        }
        
        if (!empty($jsonStep->who)) {
            $stepWho = $this->om->getRepository('InnovaPathBundle:StepWho')->findOneById($jsonStep->who);
            $step->setStepWho($stepWho);
        }
        
        if (!empty($jsonStep->where)) {
            $stepWhere = $this->om->getRepository('InnovaPathBundle:StepWhere')->findOneById($jsonStep->where);
            $step->setStepWhere($stepWhere);
        }
        
        if (!empty($jsonStep->description)) {
            $step->setDescription($jsonStep->description);
        }
        
        if (!empty($jsonStep->image)) {
            $step->setImage($jsonStep->image);
        }

        $this->om->persist($step);
        $this->om->flush();

        return $step;
    }

    public function contextualUpdate($step)
    {
        $path = $step->getPath();
        $json = json_decode($path->getStructure());
        $json_root_steps = $json->steps;

        $this->findAndUpdateJsonStep($json_root_steps, $step);

        $json = json_encode($json);
        $path->setStructure($json);
       
        $this->om->persist($path);
        $this->om->persist($step);
        $this->om->flush();
    }

    public function findAndUpdateJsonStep($jsonSteps, $step){
        foreach($jsonSteps as $jsonStep){
            echo $jsonStep->resourceId;
            if($jsonStep->resourceId == $step->getId()){
                $jsonStep->description = $step->getDescription();
            }
            elseif(!empty($jsonStep->children)) {
                $this->findAndUpdateJsonStep($jsonStep->children, $step);
            }
        }
    }
}
