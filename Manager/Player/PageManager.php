<?php

namespace UJM\ExoBundle\Manager\Player;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\TranslatorInterface;
use UJM\ExoBundle\Entity\Player\ExercisePlayer;
use UJM\ExoBundle\Entity\Player\Page;

/**
 * Description of ExercisePlayerManager
 *
 * @author patrick
 */
class PageManager {

    protected $em;
    protected $translator;

    public function __construct(EntityManager $em, TranslatorInterface $translator) {
        $this->em = $em;
        $this->translator = $translator;
    }

    public function getRepository() {
        return $this->em->getRepository('UJMExoBundle:Player\Page');
    }

    /**
     * Get first page
     * @param ExercisePlayer $ep
     * @return \UJM\ExoBundle\Entity\Player\Page
     */
    public function getFirstPage(ExercisePlayer $ep) {
        $page = $this->getRepository()->findOneBy(array('exercisePlayer' => $ep, 'isFirstPage' => true));
        return $page;
    }

    /**
     * Get first page
     * @param ExercisePlayer $ep
     * @return \UJM\ExoBundle\Entity\Player\Page
     */
    public function getLastPage(ExercisePlayer $ep) {
        $page = $this->getRepository()->findOneBy(array('exercisePlayer' => $ep, 'isLastPage' => true));
        return $page;
    }

    /**
     * Get all pages
     * @param ExercisePlayer $ep
     * @return ArrayCollection
     */
    public function getPages(ExercisePlayer $ep) {
        $pages = $this->getRepository()->findBy(array('exercisePlayer' => $ep), array('position' => 'ASC'));
        return $pages;
    }

    /**
     * 
     * @param ExercisePlayer $ep
     * @param type $pages
     */
    public function updatePages(ExercisePlayer $ep, $pages) {

        // validate data or throws exception
        $this->validatePlayerPagesData($pages);

        // get original pages before update to delete unused pages
        $oldPages = $this->getPages($ep);
        
        $this->deleteUnusedPages($oldPages, $pages);

        foreach ($pages as $page) {
            $pageEntity = null;
            $toDelete = false;
            if (isset($page['id'])) {
                $pageEntity = $this->getRepository()->findOneBy(array('id' => $page['id']));
                
            } else {
                $pageEntity = new Page();
                $pageEntity->setExercisePlayer($ep);
            }
            if (!$toDelete) {
                $pageEntity->setPosition($page['position']);
                $pageEntity->setDescription($page['description']);
                $pageEntity->setShuffle(isset($page['shuffle']) ? $page['shuffle'] : false);
                $this->em->persist($pageEntity);
            }            
            $this->em->flush();
        }

        return $this->getPages($ep);
    }

    /**
     * Since we get an array from angular service we have to check the received data for each page
     * @param Array $pages
     * @return boolean
     * @throws Exception
     */
    private function validatePlayerPagesData($pages) {
        $valid = true;

        if (!$valid) {
            throw new Exception('error');
        }
        return $valid;
    }
    
    /**
     * Compare two Page(s) collection, the old one and the new one
     * if an item is in the old collection and in the new one we keep it
     * if an item in the new collection has no id we also keep it
     * if an item has an id but can not be found in the new collection we remove it
     * @param ArrayCollection $oldCollection
     * @param Array $newCollection
     */
    private function deleteUnusedPages($oldCollection, $newCollection){
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
                $pageEntity = $this->getRepository()->findOneBy(array('id' => $currentId));
                $this->em->remove($pageEntity);
                $this->em->flush();
            }
        }
    }

    public function addPage(ExercisePlayer $ep, $page) {

        $pageEntity = new Page();
        $pageEntity->setExercisePlayer($ep);
        $pageEntity->setPosition($page['position']);
        $pageEntity->setDescription($page['description']);
        $pageEntity->setShuffle(isset($page['shuffle']) ? $page['shuffle'] : false);
        $this->em->persist($pageEntity);
        $this->em->flush();
    }

}
