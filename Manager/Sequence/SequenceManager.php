<?php

namespace UJM\ExoBundle\Manager\Sequence;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Question;

/**
 * @DI\Service("ujm.exo.sequence_manager")
 */
class SequenceManager
{

    private $om;
    private $em;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager"),
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "translator" = @DI\Inject("translator")
     * })
     * @param ObjectManager $om
     * @param EntityManager $em
     * @param TranslatorInterface $translator
     */
    public function __construct(ObjectManager $om, EntityManager $em, TranslatorInterface $translator)
    {
        $this->om = $om;
        $this->em = $em;
        $this->translator = $translator;
    }

    /**
     * End the sequence
     * @param Exercise $exercise
     * @param User $user
     * @param Array $paper
     * @return type
     */
    public function endSequence(Exercise $exercise, $paper)
    {
        $response = array();
        $response['status'] = 'success';
        $response['messages'] = array();
        $response['data'] = array();

        return $response;
    }
    
    /**
     * 
     * @param Exercise $exercise
     * @param Question $question
     * @param type $answer
     * @return int
     */
    public function saveProgression(Exercise $exercise, Question $question, $answer){
        $response = array();
        $response['status'] = 'success';
        $response['messages'] = array();
        // return the brand new answer id
        $response['data'] = array('id' => 1);
        return $response;
    }

}
