<?php

namespace UJM\ExoBundle\Manager\Sequence;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\TranslatorInterface;
use UJM\ExoBundle\Entity\Sequence\Sequence;
use UJM\ExoBundle\Entity\Sequence\Step;

/**
 * Description of StepManager
 *
 */
class StepManager {

    protected $em;
    protected $translator;

    public function __construct(EntityManager $em, TranslatorInterface $translator) {
        $this->em = $em;
        $this->translator = $translator;
    }

    public function getRepository() {
        return $this->em->getRepository('UJMExoBundle:Sequence\Step');
    }
   
    /**
     * Get all steps
     * @param Sequence $s
     * @return ArrayCollection
     */
    public function getSteps(Sequence $s) {
        $steps = $this->getRepository()->findBy(array('sequence' => $s), array('position' => 'ASC'));
        return $steps;
    }

    /**
     * 
     * @param Sequence $s
     * @param type $steps
     */
    public function updateSteps(Sequence $s, $steps) {

        // validate data or throws exception
        $this->validateStepsData($steps);

        // get original pages before update to delete unused steps
        $oldSteps = $this->getSteps($s);        
        $this->deleteUnusedSteps($oldSteps, $steps);

        foreach ($steps as $step) {
            $stepEntity = null;
            $toDelete = false;
            if (isset($step['id'])) {
                $stepEntity = $this->getRepository()->findOneBy(array('id' => $step['id']));                
            } else {
                $stepEntity = new Step();
                $stepEntity->setSequence($s);
            }
            if (!$toDelete) {
                $stepEntity->setPosition($step['position']);
                $stepEntity->setDescription($step['description']);
                $stepEntity->setShuffle(isset($step['shuffle']) ? $step['shuffle'] : false);
                $this->em->persist($stepEntity);
            }            
            $this->em->flush();
        }

        return $this->getSteps($s);
    }

    /**
     * Since we get an array from angular service we have to check the received data for each step
     * @param Array $steps
     * @return boolean
     * @throws Exception
     */
    private function validateStepsData($steps) {
        $valid = true;

        if (!$valid) {
            throw new Exception('error');
        }
        return $valid;
    }
    
    /**
     * Compare two Step(s) collection, the old one and the new one
     * if an item is in the old collection and in the new one we keep it
     * if an item in the new collection has no id we also keep it
     * if an item has an id but can not be found in the new collection we remove it
     * @param ArrayCollection $oldCollection
     * @param Array $newCollection
     */
    private function deleteUnusedSteps($oldCollection, $newCollection){
        foreach ($oldCollection as $toCheck){
            $toKeep = false;
            $currentId = $toCheck->getId();
            foreach($newCollection as $new){
                if(!isset($new['id']) || $new['id'] == $currentId){
                    $toKeep = true;
                    break;
                }
            }
            if(!$toKeep){
                $step = $this->getRepository()->findOneBy(array('id' => $currentId));
                $this->em->remove($step);
                $this->em->flush();
            }
        }
    }

    public function addStep(Sequence $s, $step) {

        $stepEntity = new Step();
        $stepEntity->setExercisePlayer($s);
        $stepEntity->setPosition($step['position']);
        $stepEntity->setDescription($step['description']);
        $stepEntity->setShuffle(isset($step['shuffle']) ? $step['shuffle'] : false);
        $this->em->persist($stepEntity);
        $this->em->flush();
    }

}
