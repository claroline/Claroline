<?php

namespace UJM\ExoBundle\Services\twig;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Request;

use UJM\ExoBundle\Services\classes\exerciseServices;

class TwigExtensions extends \Twig_Extension
{
    protected $doctrine;
    protected $exerciseSer;

    public function __construct(Registry $doctrine, exerciseServices $exerciseSer)
    {
        $this->doctrine  = $doctrine;
        $this->exerciseSer = $exerciseSer;
    }

    public function getName()
    {
        return "twigExtensions";
    }

    public function getFunctions()
    {

        return array(
            'regexTwig'            => new \Twig_Function_Method($this, 'regexTwig'),
            'getInterTwig'         => new \Twig_Function_Method($this, 'getInterTwig'),
            'getCoordsGraphTwig'   => new \Twig_Function_Method($this, 'getCoordsGraphTwig'),
            'roundUpOrDown'        => new \Twig_Function_Method($this, 'roundUpOrDown'),
            'getQuestionRights'    => new \Twig_Function_Method($this, 'getQuestionRights'),
        );

    }

    public function regexTwig($pattern, $str)
    {
        //return int
        return preg_match((string) $pattern, (string) $str);
    }

    public function getInterTwig($interId, $typeInter)
    {
        //$em = $this->doctrine->getManager();

        switch ($typeInter)
        {
            case "InteractionQCM":
                $interQCM = $this->doctrine
                                 ->getManager()
                                 ->getRepository('UJMExoBundle:InteractionQCM')
                                 ->getInteractionQCM($interId);
                $inter['question'] = $interQCM[0];
                $inter['maxScore'] = $this->getQCMScoreMax($interQCM[0]);
            break;

            case "InteractionGraphic":
                $interG = $this->doctrine
                               ->getManager()
                               ->getRepository('UJMExoBundle:InteractionGraphic')
                               ->getInteractionGraphic($interId);
                $inter['question'] = $interG[0];
                $inter['maxScore'] = $this->getGraphicScoreMax($interG[0]);
            break;

            case "InteractionHole":
                $interHole = $this->doctrine
                                  ->getManager()
                                  ->getRepository('UJMExoBundle:InteractionHole')
                                  ->getInteractionHole($interId);
                $inter['question'] = $interHole[0];
                $inter['maxScore'] = $this->getHoleScoreMax($interHole[0]);
            break;

            case "InteractionOpen":
                $interOpen = $this->doctrine
                               ->getManager()
                               ->getRepository('UJMExoBundle:InteractionOpen')
                               ->getInteractionOpen($interId);
                $inter['question'] = $interOpen[0];
                $inter['maxScore'] = $this->getOpenScoreMax($interOpen[0]);
            break;
        }

        return $inter;
    }

    public function getCoordsGraphTwig($interGraphId)
    {
        $coords = $this->doctrine
                       ->getManager()
                       ->getRepository('UJMExoBundle:Coords')
                       ->findBy(array('interactionGraphic' => $interGraphId));

        return $coords;
    }

    public function roundUpOrDown($markToBeAdjusted)
    {
        return $this->exerciseSer->roundUpDown($markToBeAdjusted);
    }

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

    private function getQCMScoreMax($interQCM)
    {
        return $this->exerciseSer->qcmMaxScore($interQCM);
    }

    private function getOpenScoreMax($interOpen)
    {
        return $this->exerciseSer->openMaxScore($interOpen);
    }

    private function getHoleScoreMax($interHole)
    {
        return $this->exerciseSer->holeMaxScore($interHole);
    }

    private function getGraphicScoreMax($interG)
    {
        return $this->exerciseSer->graphicMaxScore($interG);
    }
}