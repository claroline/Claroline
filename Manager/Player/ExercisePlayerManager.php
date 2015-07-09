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

        // add first page
        $first = new Page();
        $first->setIsFirstPage(true);
        $first->setPosition(1);
        $first->setDescription('<h1>This is the first Page</h1>');
        $first->setExercisePlayer($ep);
        $ep->addPage($first);

        // add last page
        $last = new Page();
        $last->setIsLastPage(true);
        $last->setPosition(2);
        $last->setDescription('<h1>This is the last Page</h1>');
        $last->setExercisePlayer($ep);
        $ep->addPage($last);

        $this->em->persist($ep);
        $this->em->flush();
        return $ep;
    }

    public function update(ExercisePlayer $ep) {
        $this->em->persist($ep);
        $this->em->flush();
        return $ep;
    }

}
