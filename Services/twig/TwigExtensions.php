<?php

namespace UJM\ExoBundle\Services\twig;

use Doctrine\Bundle\DoctrineBundle\Registry;

use \Symfony\Component\DependencyInjection\Container;

class TwigExtensions extends \Twig_Extension
{
    protected $doctrine;
    protected $container;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection
     * @param \Symfony\Component\DependencyInjection\Container $container
     *
     */
    public function __construct(Registry $doctrine, Container $container)
    {
        $this->doctrine  = $doctrine;
        $this->container = $container;
    }

    /**
     * Get name
     *
     * @access public
     *
     * Return String
     */
    public function getName()
    {
        return "twigExtensions";
    }

    /**
     * Get functions
     *
     * @access public
     *
     * Return array
     */
    public function getFunctions()
    {

        return array(
            'regexTwig'               => new \Twig_Function_Method($this, 'regexTwig'),
            'getInterTwig'            => new \Twig_Function_Method($this, 'getInterTwig'),
            'getCoordsGraphTwig'      => new \Twig_Function_Method($this, 'getCoordsGraphTwig'),
            'roundUpOrDown'           => new \Twig_Function_Method($this, 'roundUpOrDown'),
            'getQuestionRights'       => new \Twig_Function_Method($this, 'getQuestionRights'),
            'getProposal'             => new \Twig_Function_Method($this, 'getProposal'),
            'explodeString'           => new \Twig_Function_Method($this, 'explodeString'),
            'initTabResponseMatching' => new \Twig_Function_Method($this, 'initTabResponseMatching'),
            'goodResponseOpenOneWord' => new \Twig_Function_Method($this, 'goodResponseOpenOneWord'),
            'goodGraphCoords'         => new \Twig_Function_Method($this, 'goodGraphCoords'),
        );

    }

    /**
     * preg_match for twig
     *
     * @access public
     *
     * @param mixed $patern cast into string
     * @param mixed $str cast into string
     *
     * Return integer
     */
    public function regexTwig($pattern, $str)
    {

        return preg_match((string) $pattern, (string) $str);
    }

    /**
     * Get the InteractionX (InteractionQCM or InteractionGraphic or ...) and the score max of the interaction
     *
     * @access public
     *
     * @param integer $interId id InteractionX
     * @param String $typeInter type of interaction (QCM, graphic, ...)
     *
     * Return array
     */
    public function getInterTwig($interId, $typeInter)
    {
        $interSer        = $this->container->get('ujm.exo_' . $typeInter);
        $interactionX    = $interSer->getInteractionX($interId);
        $inter['question'] = $interactionX;
        $inter['maxScore'] = $interSer->maxScore($interactionX);

        return $inter;
    }

    /**
     * Get the coords of response zones of an InteractionGraphic
     *
     * @access public
     *
     * @param integer $interGraphId id InteractionGraphic
     *
     * Return array[Coords]
     */
    public function getCoordsGraphTwig($interGraphId)
    {
        $coords = $this->doctrine
                       ->getManager()
                       ->getRepository('UJMExoBundle:Coords')
                       ->findBy(array('interactionGraphic' => $interGraphId));

        return $coords;
    }

    /**
     * To round up and down a score
     *
     * @access public
     *
     * @param float $markToBeAdjusted
     *
     * Return float
     */
    public function roundUpOrDown($markToBeAdjusted)
    {
        $exerciseSer = $this->container->get('ujm.exo_exercise');

        return $exerciseSer->roundUpDown($markToBeAdjusted);
    }

    /**
     * Get a proposal entity
     *
     * @access public
     *
     * @param integer
     *
     * Return \UJM\ExoBundle\Entity\Proposal $proposal
     *
     */
    public function getProposal($id)
    {
        $proposal = $this->doctrine
                         ->getManager()
                         ->getRepository('UJMExoBundle:Proposal')
                         ->find($id);

        return $proposal;
    }

    /**
     * For the correction of a matching question :
     * init array of responses of user indexed by labelId
     * init array of rights responses indexed by labelId
     *
     * @access public
     *
     * @param String $response
     * @param \UJM\ExoBundle\Entity\Paper\InteractionMatching $interMatching
     *
     * Return array of arrays
     */
    public function initTabResponseMatching($response, $interMatching)
    {
        $interMatchSer = $this->container->get('ujm.exo_InteractionMatching');

        return $interMatchSer->initTabResponseMatching($response, $interMatching);
    }

    /**
     * To explode a string
     *
     * @access public
     *
     * @param string $lim the boundary string
     * @param string $str The input string
     *
     * Return array
     *
     */
    public function explodeString($lim, $str) {

        return explode($lim, $str);
    }

    /**
     * Cet rights for a question and an user, this method is used in the views with a table of questions
     *
     * @access public
     *
     * @param String $questionsList the type of the list (share, import, my, exoList)
     * @param boolean $shareRight if the user can be edit a shared question
     * @param integer $actionQ info right about a question
     * @param boolean $qexoEdit if the user can edit a question in an exercise
     *
     * Return array
     */
    public function getQuestionRights($questionsList, $shareRight, $actionQ, $qexoEdit)
    {

        $questionRights = array();
        $questionRights['dispSharedBy']                  = FALSE;
        $questionRights['allowShareQuestion']            = FALSE;
        $questionRights['allowDuplicateQuestion']        = FALSE;
        $questionRights['allowEditQuestion']             = FALSE;
        $questionRights['allowDeleteQuestion']           = FALSE;
        $questionRights['allowDeleteQuestionOfMyBank']   = FALSE;
        $questionRights['allowDeleteQuestionOfExercise'] = FALSE;
        $questionRights['allowImportQuestion']           = FALSE;

        //display shared by
        if ( ($questionsList == 'share') || ($questionsList == 'importShare')
                || ( ($questionsList == 'exoList') && $actionQ == 2)
                || ( ($questionsList == 'importExoList') && ($actionQ == 2) ) ) {

            $questionRights['dispSharedBy'] = TRUE;
        }

        //allow to share a question
        if ( ($questionsList == 'my') || ( ($questionsList == 'exoList')
                && ($actionQ == 1) ) ) {

            $questionRights['allowShareQuestion'] = TRUE;
        }

        //allow to duplicate a question
        if ( ($questionsList == 'my') || ($questionsList == 'share') || ($actionQ <= 2) ) {

            $questionRights['allowDuplicateQuestion'] = TRUE;
        }

        //allow to edit a question
        if ( (($questionsList == 'my' ) || ($shareRight === TRUE) || ($qexoEdit == 1)
                || ( ($questionsList == 'exoList') && ($actionQ == 1) ))
             && ($questionsList != 'importExoList') ){

            $questionRights['allowEditQuestion'] = TRUE;
        }

        //allow to delete a question
        if ($questionsList == 'my') {

            $questionRights['allowDeleteQuestion'] = TRUE;
        }

        //allow to delete a question of my bank
        if ( ($questionsList == 'share') || (($questionsList == 'exoList')
                && ($actionQ <= 2)) ) {

            $questionRights['allowDeleteQuestionOfMyBank'] = TRUE;
        }

        //allow to delete a question of an exercise
        if ($questionsList == 'exercise') {

            $questionRights['allowDeleteQuestionOfExercise'] = TRUE;
        }

        //allow to import question in an exercise
        if ($questionsList == 'importMy' || $questionsList == 'importShare'
                || $questionsList == 'importExoList') {

            $questionRights['allowImportQuestion'] = TRUE;
        }

        return $questionRights;

    }

    /**
     * return the good response for an open question with one word
     *
     * @access public
     *
     * @param integer $interOpenId id InteractionOpen
     *
     * Return integer
     */
    public function goodResponseOpenOneWord($interOpenId)
    {

        return $this->doctrine
                    ->getManager()
                    ->getRepository('UJMExoBundle:WordResponse')
                    ->getgoodResponseOneWord($interOpenId);
    }

    public function goodGraphCoords($interGraph)
    {
         $coords = $this->doctrine
                        ->getManager()
                        ->getRepository('UJMExoBundle:Coords')
                        ->findBy(array('interactionGraphic' => $interGraph->getId()));

         return $coords;
    }
}
