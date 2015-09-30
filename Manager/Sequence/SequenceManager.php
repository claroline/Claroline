<?php

namespace UJM\ExoBundle\Manager\Sequence;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Services\classes\ExerciseServices;
use Claroline\CoreBundle\Entity\User;

/**
 * @DI\Service("ujm.exo.sequence_manager")
 */
class SequenceManager
{

    private $om;
    private $em;
    private $exoM;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager"),
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "exoM" = @DI\Inject("ujm.exo_exercise"),
     *     "translator" = @DI\Inject("translator")
     * })
     * @param ObjectManager $om
     * @param EntityManager $em
     * @param ExerciseManager $exoM
     * @param TranslatorInterface $translator
     */
    public function __construct(ObjectManager $om, EntityManager $em, \UJM\ExoBundle\Services\classes\ExerciseServices $exoM, TranslatorInterface $translator)
    {
        $this->om = $om;
        $this->em = $em;
        $this->exoM = $exoM;
        $this->translator = $translator;
    }

    /**
     * Check if current user can (re)play the sequence
     * @param Exercise $exercise
     * @param $user
     * @return bool
     */
    public function userCanPlaySequence(Exercise $exercise, User $user)
    {
        $nbAttempts = $this->exoM->getNbPaper($user->getId(), $exercise->getId());
        $maxAttempts = $exercise->getMaxAttempts();

        // check if exercise has a limited number of attempts or if curent user has reached this maximum
        return $maxAttempts == 0 || $nbAttempts <= $maxAttempts;
    }

    /**
     * 
     * @param Exercise $exercise
     * @param type $user
     * @return type
     */
    public function endSequence(Exercise $exercise, User $user)
    {
        $response = array();
        $response['status'] = 'success';
        $response['messages'] = array();
        $response['data'] = array();

        return $response;
    }
    
    public function saveProgression(User $user, Exercise $exercise, Question $question, $answer){
        $response = array();
        $response['status'] = 'success';
        $response['messages'] = array();
        // return the brand new answer id
        $response['data'] = array('id' => 1);
        return $response;
    }

    public function update(Sequence $s)
    {
        $this->em->persist($s);
        $this->em->flush();
        return $s;
    }

}
