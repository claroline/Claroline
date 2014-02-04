<?php

namespace Innova\PathBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\Path\Path;
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

    public function editResourceNodeRelation(Step $step, $resourceNodeId, $excluded, $propagated, $order = null)
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
        
        if (!empty($order)) {
            $step2ressourceNode->setResourceOrder($order);
        }
    
        $this->om->persist($step2ressourceNode);
        $this->om->flush();
    
        return $step2ressourceNode;
    }
    
    /**
     * Create a new step from JSON structure
     * @param  \Innova\PathBundle\Entity\Path\Path $path          Parent path of the step
     * @param  integer                             $level         Depth of the step in the path
     * @param  \Innova\PathBundle\Entity\Step      $parent        Parent step of the step
     * @param  integer                             $order         Order of the step relative to its siblings
     * @param  \stdClass                           $stepStructure Data about the step
     * @return \Innova\PathBundle\Entity\Step                     Edited step
     */
    public function create(Path $path, $level = 0, Step $parent = null, $order = 0, \stdClass $stepStructure)
    {
        $step = new Step();
        
        return $this->edit($path, $level, $parent, $order, $stepStructure, $step);
    }
    
    /**
     * Update an existing step from JSON structure
     * @param  \Innova\PathBundle\Entity\Path\Path $path          Parent path of the step
     * @param  integer                             $level         Depth of the step in the path
     * @param  \Innova\PathBundle\Entity\Step      $parent        Parent step of the step
     * @param  integer                             $order         Order of the step relative to its siblings
     * @param  \stdClass                           $stepStructure Data about the step
     * @param  \Innova\PathBundle\Entity\Step      $step          Current step to edit
     * @return \Innova\PathBundle\Entity\Step                     Edited step
     */
    public function edit(Path $path, $level = 0, Step $parent = null, $order = 0, \stdClass $stepStructure, Step $step)
    {
        // Update step properties
        $step->setPath($path);
        $step->setParent($parent);
        $step->setLvl($level);
        $step->setOrder($order);
        
        // Grab data from structure
        $name = !empty($stepStructure->name) ? $stepStructure->name : Step::DEFAULT_NAME;
        $step->setName($name);
        
        $image = !empty($stepStructure->image) ? $stepStructure->image : null;
        $step->setImage($image);
        
        $description = !empty($stepStructure->description) ? $stepStructure->description : null;
        $step->setDescription($description);
        
        $withTutor = !empty($stepStructure->withTutor) ? $stepStructure->withTutor : false;
        $step->setWithTutor($withTutor);
        
        $withComputer = !empty($stepStructure->withComputer) ? $stepStructure->withComputer : false;
        $step->setWithComputer($withComputer);
        
        $durationHours = !empty($stepStructure->durationHours) ? intval($stepStructure->durationHours) : 0;
        $durationMinutes = !empty($jsonStep->durationMinutes) ? intval($stepStructure->durationMinutes) : 0;
        $step->setDuration(new \DateTime('00-00-00 ' . $durationHours . ':' . $durationMinutes . ':00'));
        
        $stepWho = null;
        if (!empty($stepStructure->who)) {
            $stepWho = $this->om->getRepository('InnovaPathBundle:StepWho')->findOneById($stepStructure->who);
        }
        $step->setStepWho($stepWho);
        
        $stepWhere = null;
        if (!empty($stepStructure->where)) {
            $stepWhere = $this->om->getRepository('InnovaPathBundle:StepWhere')->findOneById($stepStructure->where);
        }
        $step->setStepWhere($stepWhere);
        
        // Save modifications
        $this->om->persist($step);
        $this->om->flush();
        
        return $step;
    }
    
    public function getWho()
    {
        $results = $this->om->getRepository('InnovaPathBundle:StepWho')->findAll();
        
        $stepWhos = array();
        foreach ($results as $result) {
            $stepWhos[$result->getId()] = $result->getName();
        }
        
        return $stepWhos;
    }
    
    public function getWhere()
    {
        $results = $this->om->getRepository('InnovaPathBundle:StepWhere')->findAll();
        
        $stepWheres = array();
        foreach ($results as $result) {
            $stepWheres[$result->getId()] = $result->getName();
        }
        
        return $stepWheres;
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
