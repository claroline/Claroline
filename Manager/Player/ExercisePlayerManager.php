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
class ExercisePlayerManager {

    protected $em;
    protected $translator;

    public function __construct(EntityManager $em, TranslatorInterface $translator) {
        $this->em = $em;
        $this->translator = $translator;
    }

    public function getRepository() {
        return $this->em->getRepository('UJMExoBundle:Player\ExercisePlayer');
    }

    public function createFirstAndLastPage(ExercisePlayer $ep) {
        
        //var_dump($ep->getPages());
        // add first page
        $first = new Page();
        $first->setIsFirstPage(true);
        $first->setExercisePlayer($ep);
        $ep->addPage($first);
        //$this->em->persist($ep);

        // add last page
        $last = new Page();
        $last->setIsLastPage(true);
        $last->setExercisePlayer($ep);
        $ep->addPage($last);
        
        //var_dump($ep->getPages());die;
        
        $this->em->persist($ep);
        $this->em->flush();
        return $ep;
    }

}
