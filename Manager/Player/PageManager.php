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
     * Get all pages excluding first and last
     * @param ExercisePlayer $ep
     * @return ArrayCollection
     */
    public function getPages(ExercisePlayer $ep) {
        $pages = $this->getRepository()->findBy(array('exercisePlayer' => $ep/*, 'isFirstPage' => false, 'isLastPage' => false*/), array('position' => 'ASC'));
        return $pages;
    }

}
