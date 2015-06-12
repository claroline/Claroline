<?php

namespace UJM\ExoBundle\Services\classes;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Persistence\ObjectManager;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use UJM\ExoBundle\Entity\ExerciseQuestion;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Event\Log\LogExerciseEvaluatedEvent;

class exerciseServices
{
    protected $om;
    protected $tokenStorage;
    protected $authorizationChecker;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;
    protected $doctrine;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Claroline\CoreBundle\Persistence\ObjectManager $om Dependency Injection
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage Dependency Injection
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Dependency Injection
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher Dependency Injection
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection;
     *
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        EventDispatcherInterface $eventDispatcher,
        Registry $doctrine
    )
    {
        $this->om = $om;
        $this->tokenStorage         = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->eventDispatcher      = $eventDispatcher;
        $this->doctrine             = $doctrine;
    }

    /**
     * Get IP client
     *
     * @access public
     * @param Request $request
     *
     * @return IP Client
     */
    public function getIP(Request $request)
    {

        return $request->getClientIp();
    }


    /**
     * To process the user's response for an QCM and a paper (or a test)
     *
     * @access public
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $paperID id Paper or 0 if it's just a question test and not a paper
     *
     * Return array
     */
    public function responseQCM($request, $paperID = 0)
    {
        $res = array();
        $interactionQCMID = $request->request->get('interactionQCMToValidated');
        $response = array();

        $em = $this->doctrine->getManager();
        $interQCM = $em->getRepository('UJMExoBundle:InteractionQCM')->find($interactionQCMID);

        if ($interQCM->getTypeQCM()->getCode() == 2) {
            $response[] = $request->request->get('choice');
        } else {
            if ($request->request->get('choice') != null) {
                $response = $request->request->get('choice');
            }
        }

        $allChoices = $interQCM->getChoices();

        $penalty = 0;

        $session = $request->getSession();

        if ($paperID == 0) {
            if ($session->get('penalties')) {
                foreach ($session->get('penalties') as $penal) {
                    $penalty += $penal;
                }
            }
            $session->remove('penalties');
        } else {
            $penalty = $this->getPenalty($interQCM->getInteraction(), $paperID);
        }

        $score = $this->qcmMark($interQCM, $response, $allChoices, $penalty);

        $responseID = '';

        foreach ($response as $res) {
            if ($res != null) {
                $responseID .= $res.';';
            }
        }

        $res = array(
            'score'    => $score,
            'penalty'  => $penalty,
            'interQCM' => $interQCM,
            'response' => $responseID
        );

        return $res;

    }

    /**
     * To calculate the score for a QCM
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\InteractionQCM $interQCM
     * @param integer[] $response array of id Choice selected
     * @param Choice[] $allChoices choices linked at the QCM
     * @param float $penality penalty if the user showed hints
     *
     * @return string userScore/scoreMax
     */
    public function qcmMark(\UJM\ExoBundle\Entity\InteractionQCM $interQCM, array $response, $allChoices, $penality)
    {
        $score = 0;
        $scoreMax = $this->qcmMaxScore($interQCM);

        $rightChoices = array();
        $markByChoice = array();

        if (!$interQCM->getWeightResponse()) {
            foreach ($allChoices as $choice) {
                if ($choice->getRightResponse()) {
                    $rightChoices[] = (string) $choice->getId();
                }
            }

            $result = array_diff($response, $rightChoices);
            $resultBis = array_diff($rightChoices, $response);

            if ((count($result) == 0) && (count($resultBis) == 0)) {
                $score = $interQCM->getScoreRightResponse() - $penality;
            } else {
                $score = $interQCM->getScoreFalseResponse() - $penality;
            }
            if ($score < 0) {
                $score = 0;
            }

            $score .= ' / '.$scoreMax;
        } else {
            //points par réponse
            foreach ($allChoices as $choice) {
                $markByChoice[(string) $choice->getId()] = $choice->getWeight();
            }
            if ($response[0] != null) {
                foreach ($response as $res) {
                    $score += $markByChoice[$res];
                }
            }

            if ($score > $scoreMax) {
                $score = $scoreMax;
            }

            $score -= $penality;

            if ($score < 0) {
                $score = 0;
            }
            $score .= '/'.$scoreMax;
        }

        return $score;
    }

    /**
     * Return the number of papers for an exercise and for an user
     *
     * @access public
     *
     * @param integer $uid id User
     * @param integer $exoId id Exercise
     * @param boolean $finished to count or no paper n o finished
     *
     * @return integer
     */
    public function getNbPaper($uid, $exoID, $finished = false)
    {
        $papers = $this->om
                       ->getRepository('UJMExoBundle:Paper')
                       ->getExerciseUserPapers($uid, $exoID, $finished);

        return count($papers);
    }

    /**
     * To process the user's response for graphic question and a paper(or a test)
     *
     * @access public
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $paperID id Paper or 0 if it's just a question test and not a paper
     *
     * @return array
     */
    public function responseGraphic($request, $paperID = 0)
    {
        $answers = $request->request->get('answers'); // Answer of the student
        $graphId = $request->request->get('graphId'); // Id of the graphic interaction
        $max = $request->request->get('nbpointer'); // Number of answer zones

        $em = $this->doctrine->getManager();

        $rightCoords = $em->getRepository('UJMExoBundle:Coords')
            ->findBy(array('interactionGraphic' => $graphId));

        $interG = $em->getRepository('UJMExoBundle:InteractionGraphic')
            ->find($graphId);

        $doc = $em->getRepository('UJMExoBundle:Document')
            ->findOneBy(array('id' => $interG->getDocument()));

        $verif = array();
        $point = $z = $total = 0;

        $coords = preg_split('[;]', $answers); // Divide the answer zones into cells

        for ($i = 0; $i < $max - 1; $i++) {
            for ($j = 0; $j < $max - 1; $j++) {
                if (preg_match('/[0-9]+/', $coords[$j])) {
                    list($xa,$ya) = explode("-", $coords[$j]); // Answers of the student
                    list($xr,$yr) = explode(",", $rightCoords[$i]->getValue()); // Right answers

                    $valid = $rightCoords[$i]->getSize(); // Size of the answer zone

                    // If answer student is in right answer
                    if ((($xa + 8) < ($xr + $valid)) && (($xa + 8) > ($xr)) &&
                        (($ya + 8) < ($yr + $valid)) && (($ya + 8) > ($yr))
                    ) {
                        // Not get points twice for one answer
                        if ($this->alreadyDone($rightCoords[$i]->getValue(), $verif, $z)) {
                            $point += $rightCoords[$i]->getScoreCoords(); // Score of the student without penalty
                            $verif[$z] = $rightCoords[$i]->getValue(); // Add this answer zone to already answered zones
                            $z++;
                        }
                    }
                }
            }
            $total = $this->graphicMaxScore($interG); // Score max
        }

        $penalty = 0;

        $session = $request->getSession();

        // Not assessment
        if ($paperID == 0) {
            if ($session->get('penalties')) {
                foreach ($session->get('penalties') as $penal) {

                    $signe = substr($penal, 0, 1); // In order to manage the symbol of the penalty

                    if ($signe == '-') {
                        $penalty += substr($penal, 1);
                    } else {
                        $penalty += $penal;
                    }
                }
            }
            $session->remove('penalties');
        } else {
            $penalty = $this->getPenalty($interG->getInteraction(), $paperID);
        }

        $score = $point - $penalty; // Score of the student with penalty

        // Not negatif score
        if ($score < 0) {
            $score = 0;
        }

        if (!preg_match('/[0-9]+/', $answers)) {
            $answers = '';
        }

        $res = array(
            'point' => $point, // Score of the student without penalty
            'penalty' => $penalty, // Penalty (hints)
            'interG' => $interG, // The entity interaction graphic (for the id ...)
            'coords' => $rightCoords, // The coordonates of the right answer zones
            'doc' => $doc, // The answer picture (label, src ...)
            'total' => $total, // Score max if all answers right and no penalty
            'rep' => $coords, // Coordonates of the answer zones of the student's answer
            'score' => $score, // Score of the student (right answer - penalty)
            'response' => $answers // The student's answer (with all the informations of the coordonates)
        );

        return $res;
    }

    /**
     * To process the user's response for a open question and a paper(or a test)
     *
     * @access public
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $paperID id Paper or 0 if it's just a question test and not a paper
     *
     * @return array
     */
    public function responseOpen($request, $paperID = 0)
    {
        $res = array();
        $interactionOpenID = $request->request->get('interactionOpenToValidated');
        $response = '';
        $tempMark = true;

        $penalty = 0;
        $session = $request->getSession();

        $em = $this->doctrine->getManager();
        $interOpen = $em->getRepository('UJMExoBundle:InteractionOpen')->find($interactionOpenID);

        $response = $request->request->get('interOpen');

        // Not assessment
        if ($paperID == 0) {
            if ($session->get('penalties')) {
                foreach ($session->get('penalties') as $penal) {

                    $signe = substr($penal, 0, 1); // In order to manage the symbol of the penalty

                    if ($signe == '-') {
                        $penalty += substr($penal, 1);
                    } else {
                        $penalty += $penal;
                    }
                }
            }
            $session->remove('penalties');
        } else {
            $penalty = $this->getPenalty($interOpen->getInteraction(), $paperID);
        }

        if ($interOpen->getTypeOpenQuestion() == 'long') {
            $score = -1;
        } else if ($interOpen->getTypeOpenQuestion() == 'oneWord') {
            $score = $this->getScoreOpenOneWord($response, $interOpen);
        } else if ($interOpen->getTypeOpenQuestion() == 'short') {
            $score = $this->getScoreShortResponse($response, $interOpen);
        }

        if ($interOpen->getTypeOpenQuestion() != 'long') {
            $score -= $penalty;
            if ($score < 0) {
                $score = 0;
            }
        }

        $score .= '/'.$this->openMaxScore($interOpen);

        $res = array(
            'penalty'   => $penalty,
            'interOpen' => $interOpen,
            'response'  => $response,
            'score'     => $score,
            'tempMark'  => $tempMark
        );

        return $res;
    }

    /**
     * To process the user's response for an question with holes and a paper(or a test)
     *
     * @access public
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $paperID id Paper or 0 if it's just a question test and not a paper
     *
     * @return array
     */
    public function responseHole($request, $paperID = 0)
    {
        $em = $this->doctrine->getManager();
        $res = array();
        $interactionHoleID = $request->request->get('interactionHoleToValidated');
        $tabResp = array();

        $penalty = 0;
        $session = $request->getSession();

        $interHole = $em->getRepository('UJMExoBundle:InteractionHole')->find($interactionHoleID);

        // Not assessment
        if ($paperID == 0) {
            if ($session->get('penalties')) {
                foreach ($session->get('penalties') as $penal) {

                    $signe = substr($penal, 0, 1); // In order to manage the symbol of the penalty

                    if ($signe == '-') {
                        $penalty += substr($penal, 1);
                    } else {
                        $penalty += $penal;
                    }
                }
            }
            $session->remove('penalties');
        } else {
            $penalty = $this->getPenalty($interHole->getInteraction(), $paperID);
        }

        $score = $this->holeMark($interHole, $request->request, $penalty);

        foreach($interHole->getHoles() as $hole) {
            $response = $request->get('blank_'.$hole->getPosition());
            $response = trim($response);
            $response = preg_replace('/\s+/', ' ', $response);

            if ($hole->getSelector()) {
                $wr = $em->getRepository('UJMExoBundle:WordResponse')->find($response);
                $tabResp[$hole->getPosition()] = $wr->getResponse();
            } else {
                $from = array("'", '"');
                $to = array("\u0027","\u0022");
                $tabResp[$hole->getPosition()] = str_replace($from, $to, $response);
            }
        }

        $response = json_encode($tabResp);

        $res = array(
            'penalty'   => $penalty,
            'interHole' => $interHole,
            'response'  => $response,
            'score'     => $score
        );

        return $res;
    }

    /**
     * To calculate the score for a question with holes
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Paper\InteractionHole $interHole
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param float $penality penalty if the user showed hints
     *
     * @return string userScore/scoreMax
     */
    public function holeMark($interHole, $request, $penalty)
    {
        $em = $this->doctrine->getManager();
        $score = 0;

        foreach($interHole->getHoles() as $hole) {
            $response = $request->get('blank_'.$hole->getPosition());
            $response = trim($response);
            $response = preg_replace('/\s+/', ' ', $response);
            if ($hole->getSelector() == true) {
                $wr = $em->getRepository('UJMExoBundle:WordResponse')->find($response);
                $score += $wr->getScore();
            } else {
                foreach ($hole->getWordResponses() as $wr) {
                    $score += $this->getScoreWordResponse($wr, $response);
                }
            }
        }

        $scoreMax = $this->holeMaxScore($interHole);

        $score -= $penalty;

        if ($score < 0) {
            $score = 0;
        }

        $score .= '/'.$scoreMax;

        return $score;

    }

    /**
     * Get max score possible for a question with holes
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Paper\InteractionHole $interHole
     *
     * @return float
     */
    public function holeMaxScore($interHole) {
        $scoreMax = 0;
        foreach ($interHole->getHoles() as $hole) {
            $scoretemp = 0;
            foreach ($hole->getWordResponses() as $wr) {
                if ($wr->getScore() > $scoretemp) {
                    $scoretemp = $wr->getScore();
                }
            }
            $scoreMax += $scoretemp;
        }

        return $scoreMax;
    }


    /**
     * To process the user's response for a matching and a paper (or a test)
     *
     * @access public
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $paperID id Paper or 0 if it's just a question test and not a paper
     *
     * @return array
     */
    public function responseMatching($request, $paperID = 0)
    {
        $interactionMatchingId = $request->request->get('interactionMatchingToValidated');
        $response = $request->request->get('jsonResponse');

        $em = $this->doctrine->getManager();
        $interMatching = $em->getRepository('UJMExoBundle:InteractionMatching')->find($interactionMatchingId);

        $penalty = 0;

        $session = $request->getSession();

        if ( $paperID == 0 ) {
            if ($session->get('penalties')) {
                foreach ($session->get('penalties') as $penal) {
                    $penalty += $penal;
                }
            }
            $session->remove('penalties');
        } else {
            $penalty = $this->getPenalty($interMatching->getInteraction(), $paperID);
        }

        $tabsResponses = $this->initTabResponseMatching($response, $interMatching);
        $tabRightResponse = $tabsResponses[1];
        $tabResponseIndex = $tabsResponses[0];

        $score = $this->matchingMark($interMatching, $penalty, $tabRightResponse, $tabResponseIndex);

        $res = array(
          'score'            => $score,
          'penalty'          => $penalty,
          'interMatching'    => $interMatching,
          'tabRightResponse' => $tabRightResponse,
          'tabResponseIndex' => $tabResponseIndex,
          'response'         => $response
        );

        return $res;
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
     * @return array of arrays
     */
    function initTabResponseMatching($response, $interMatching) {

        $tabsResponses = array();

        $tabResponseIndex = $this->getTabResponseIndex($response);
        $tabRightResponse = $this->initTabRightResponse($interMatching);

        //add in $tabResponseIndex label empty
        foreach ($interMatching->getLabels() as $label) {
            if (!isset($tabResponseIndex[$label->getId()])) {
                $tabResponseIndex[$label->getId()] = null;
            }
        }


        $tabsResponses[0] = $tabResponseIndex;
        $tabsResponses[1] = $tabRightResponse;

        return $tabsResponses;

    }

     /**
     * init array of rights responses indexed by labelId
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Paper\InteractionMatching $interMatching
     *
     * @return array
     */
    function initTabRightResponse($interMatching) {
        $tabRightResponse = array();

        //array of rights responses indexed by labelId
        foreach ($interMatching->getProposals() as $proposal) {
            $associateLabel = $proposal->getAssociatedLabel();
            if ($associateLabel != null) {
                foreach ($associateLabel as $associatedLabel) {
                    $index = $associatedLabel->getId();
                    if (isset($tabRightResponse[$index])) {
                        $tabRightResponse[$index] .= '-' . $proposal->getId();
                    } else {
                        $tabRightResponse[$index] = $proposal->getId();
                    }
                }
            }
        }

        //add in $tabRightResponse label empty
        foreach ($interMatching->getLabels() as $label) {
            if (!isset($tabRightResponse[$label->getId()])) {
                $tabRightResponse[$label->getId()] = null;
            }
        }

        return $tabRightResponse;
    }

    public function getTabResponseIndex($response) {
        $tabResponse = explode(';', substr($response, 0, -1));
        $tabResponseIndex = array();

        //array of responses of user indexed by labelId
        foreach ($tabResponse as $rep) {
            $tabTmp = preg_split('(,)', $rep);
            for ($i = 1; $i < count($tabTmp);$i++) {
                if (isset($tabResponseIndex[$tabTmp[$i]])) {
                    $tabResponseIndex[$tabTmp[$i]] .= '-' . $tabTmp[0];
                } else {
                    $tabResponseIndex[$tabTmp[$i]] = $tabTmp[0];
                }
            }
        }

        return $tabResponseIndex;
    }


    /**
     * To calculate the score for a matching question
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\InteractionMatching $interMatching
     * @param float $penality penalty if the user showed hints
     * @param array $tabRightResponse
     * @param array $tabResponseIndex
     *
     * @return string userScore/scoreMax
     */
    public function matchingMark(\UJM\ExoBundle\Entity\InteractionMatching $interMatching, $penalty, $tabRightResponse, $tabResponseIndex)
    {
        $scoretmp = 0;
        $scoreMax = $this->matchingMaxScore($interMatching);

        foreach ($tabRightResponse as $labelId => $value) {
            if ( isset($tabResponseIndex[$labelId]) && $tabRightResponse[$labelId] != null
                    && (!substr_compare($tabRightResponse[$labelId], $tabResponseIndex[$labelId], 0)) ) {
                $label = $this->om
                              ->getRepository('UJMExoBundle:Label')
                              ->find($labelId);
                $scoretmp += $label->getScoreRightResponse();
            }
            if ($tabRightResponse[$labelId] == null && !isset($tabResponseIndex[$labelId])) {
                $label = $this->om
                              ->getRepository('UJMExoBundle:Label')
                              ->find($labelId);
                $scoretmp += $label->getScoreRightResponse();
            }
        }

        $score = $scoretmp - $penalty;
        if ($score < 0) {
            $score = 0;
        }
        $score .= '/'.$scoreMax;

        return $score;
    }


    /**
     * Graphic question : Check if the suggested answer zone isn't already right in order not to have points twice
     *
     * @access public
     *
     * @param String $coor coords of one right answer
     * @param array $verif list of the student's placed answers zone
     * @param integer $z number of rights placed answers by the user
     *
     * @return boolean
     */
    public function alreadyDone($coor, $verif, $z)
    {
        $resu = true;

        for ($v = 0; $v < $z; $v++) {
            // if already placed at this right place
            if ($coor == $verif[$v]) {
                $resu = false;
                break;
            } else {
                $resu = true;
            }
        }

        return $resu;
    }

    /**
     * Get max score possible for an exercise
     *
     * @access public
     *
     * @param integer $exoID id Exercise
     *
     * @return float
     */
    public function getExerciseTotalScore($exoID)
    {
        $exoTotalScore = 0;

        $eqs = $this->om
                    ->getRepository('UJMExoBundle:ExerciseQuestion')
                    ->findBy(array('exercise' => $exoID));

        foreach ($eqs as $eq) {
            $interaction = $this->om
                                ->getRepository('UJMExoBundle:Interaction')
                                ->getInteraction($eq->getQuestion()->getId());
            switch ($interaction->getType()){
                case 'InteractionQCM':
                    $interQCM = $this->om
                                     ->getRepository('UJMExoBundle:InteractionQCM')
                                     ->getInteractionQCM($interaction->getId());
                    $scoreMax = $this->qcmMaxScore($interQCM[0]);
                    break;
                case 'InteractionGraphic':
                    $interGraphic = $this->om
                                         ->getRepository('UJMExoBundle:InteractionGraphic')
                                         ->getInteractionGraphic($interaction->getId());
                    $scoreMax = $this->graphicMaxScore($interGraphic[0]);
                    break;
                case 'InteractionOpen':
                    $interOpen = $this->om
                                      ->getRepository('UJMExoBundle:InteractionOpen')
                                      ->getInteractionOpen($interaction->getId());
                    $scoreMax = $this->openMaxScore($interOpen[0]);
                    break;
                case 'InteractionHole':
                    $interHole = $this->om
                                      ->getRepository('UJMExoBundle:InteractionHole')
                                      ->getInteractionHole($interaction->getId());
                    $scoreMax = $this->holeMaxScore($interHole[0]);
                    break;
                case 'InteractionMatching':
                    $interMatching = $this->om
                                      ->getRepository('UJMExoBundle:InteractionMatching')
                                      ->getInteractionMatching($interaction->getId());
                    $scoreMax = $this->matchingMaxScore($interMatching[0]);
                    break;
            }

            $exoTotalScore += $scoreMax;
        }

        return $exoTotalScore;
    }

    /**
     * Get total score for an paper
     *
     * @access public
     *
     * @param integer $paperID id Paper
     *
     * @return float
     */
    public function getExercisePaperTotalScore($paperID)
    {
        $exercisePaperTotalScore = 0;
        $paper = $interaction = $this->om
                                     ->getRepository('UJMExoBundle:Paper')
                                     ->find($paperID);

        $interQuestions = $paper->getOrdreQuestion();
        $interQuestions = substr($interQuestions, 0, strlen($interQuestions) - 1);
        $interQuestionsTab = explode(";", $interQuestions);

        foreach ($interQuestionsTab as $interQuestion) {
            $interaction = $this->om->getRepository('UJMExoBundle:Interaction')->find($interQuestion);
            switch ( $interaction->getType()) {
                case "InteractionQCM":
                    $interQCM = $this->om
                                     ->getRepository('UJMExoBundle:InteractionQCM')
                                     ->getInteractionQCM($interaction->getId());
                    $exercisePaperTotalScore += $this->qcmMaxScore($interQCM[0]);
                    break;

                case "InteractionGraphic":
                    $interGraphic = $this->om
                                         ->getRepository('UJMExoBundle:InteractionGraphic')
                                         ->getInteractionGraphic($interaction->getId());
                    $exercisePaperTotalScore += $this->graphicMaxScore($interGraphic[0]);
                    break;

                case "InteractionHole":
                    $interHole = $this->om
                                      ->getRepository('UJMExoBundle:InteractionHole')
                                      ->getInteractionHole($interaction->getId());
                    $exercisePaperTotalScore += $this->holeMaxScore($interHole[0]);
                    break;

                case "InteractionOpen":
                    $interOpen = $this->om
                                      ->getRepository('UJMExoBundle:InteractionOpen')
                                      ->getInteractionOpen($interaction->getId());
                    $exercisePaperTotalScore += $this->openMaxScore($interOpen[0]);
                    break;

                case "InteractionMatching":
                    $interMatching = $this->om
                                          ->getRepository('UJMExoBundle:InteractionMatching')
                                          ->getInteractionMatching($interaction->getId());
                    $exercisePaperTotalScore += $this->matchingMaxScore($interMatching[0]);
                    break;
            }
        }

        return $exercisePaperTotalScore;
    }

    /**
     * Get score max possible for a QCM
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Paper\InteractionQCM $interQCM
     *
     * @return float
     */
    public function qcmMaxScore($interQCM)
    {
        $scoreMax = 0;

        if (!$interQCM->getWeightResponse()) {
            $scoreMax = $interQCM->getScoreRightResponse();
        } else {
            foreach ($interQCM->getChoices() as $choice) {
                if ($choice->getRightResponse()) {
                    $scoreMax += $choice->getWeight();
                }
            }
        }

        return $scoreMax;
    }

    /**
     * Get score max possible for a graphic question
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Paper\InteractionGraphic $interGraphic
     *
     * @return float
     */
    public function graphicMaxScore($interGraphic)
    {
        $scoreMax = 0;

        $rightCoords = $this->om
                            ->getRepository('UJMExoBundle:Coords')
                            ->findBy(array('interactionGraphic' => $interGraphic->getId()));

        foreach ($rightCoords as $score) {
            $scoreMax += $score->getScoreCoords(); // Score max
        }

        return $scoreMax;
    }

    /**
     * Get score max possible for a open question
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Paper\InteractionOpen $interOpen
     *
     * @return float
     */
    public function openMaxScore($interOpen)
    {
        $scoreMax = 0;

        if ($interOpen->getTypeOpenQuestion() == 'long') {
            $scoreMax = $interOpen->getScoreMaxLongResp();
        } else if ($interOpen->getTypeOpenQuestion() == 'oneWord') {
            $scoreMax = $this->om
                             ->getRepository('UJMExoBundle:WordResponse')
                             ->getScoreMaxOneWord($interOpen->getId());
        } else if ($interOpen->getTypeOpenQuestion() == 'short') {
            $scoreMax = $this->om
                             ->getRepository('UJMExoBundle:WordResponse')
                             ->getScoreMaxShort($interOpen->getId());
        }

        return $scoreMax;
    }

    /**
     * Get score max possible for a matching question
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Paper\InteractionMatching $interMatching
     *
     * @return float
     */
    public function matchingMaxScore($interMatching)
    {
        $scoreMax = 0;
        foreach ($interMatching->getLabels() as $label) {
            $scoreMax += $label->getScoreRightResponse();
        }

        return $scoreMax;
    }

    /**
     * To link a question with an exercise
     *
     * @access public
     *
     * @param UJM\ExoBundle\Entity\Exercise $exercise instance of Exercise
     * @param InteractionQCM or InteractionGraphic or ... $interX
     *
     */
    public function setExerciseQuestion($exercise, $interX, $order = -1)
    {
        $eq = new ExerciseQuestion($exercise, $interX->getInteraction()->getQuestion());

        if ($order == -1) {
            $dql = 'SELECT max(eq.ordre) FROM UJM\ExoBundle\Entity\ExerciseQuestion eq '
                  . 'WHERE eq.exercise='.$exercise->getId();
            $query = $this->doctrine->getManager()->createQuery($dql);
            $maxOrdre = $query->getResult();

            $eq->setOrdre((int) $maxOrdre[0][1] + 1);
        } else {
            $eq->setOrdre($order);
        }
        $this->om->persist($eq);

        $this->om->flush();
    }

    /**
     * To round up and down a score
     *
     * @access public
     *
     * @param float $toBeAdjusted
     *
     * @return float
     */
    public function roundUpDown($toBeAdjusted)
    {
        return (round($toBeAdjusted / 0.5) * 0.5);
    }

    /**
     * To know if an user is the creator of an exercise
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Exercise $exercise
     *
     * @return boolean
     */
    public function isExerciseAdmin($exercise)
    {
        $collection = new ResourceCollection(array($exercise->getResourceNode()));
        if ($this->authorizationChecker->isGranted('ADMINISTRATE', $collection)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get informations about a paper response, maxExoScore, scorePaper, scoreTemp (all questions marked or no)
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Paper\paper $paper
     *
     * @return array
     */
    public function getInfosPaper($paper)
    {
        $infosPaper = array();
        $scorePaper = 0;
        $scoreTemp = false;

        $em = $this->doctrine->getManager();

        $interactions = $this->om
                             ->getRepository('UJMExoBundle:Interaction')
                             ->getPaperInteraction($em, str_replace(';', '\',\'', substr($paper->getOrdreQuestion(), 0, -1)));

        $interactions = $this->orderInteractions($interactions, $paper->getOrdreQuestion());

        $infosPaper['interactions'] = $interactions;

        $responses = $this->om
                          ->getRepository('UJMExoBundle:Response')
                          ->getPaperResponses($paper->getUser()->getId(), $paper->getId());

        $responses = $this->orderResponses($responses, $paper->getOrdreQuestion());

        $infosPaper['responses'] = $responses;

        $infosPaper['maxExoScore'] = $this->getExercisePaperTotalScore($paper->getId());

        foreach ($responses as $response) {
            if ($response->getMark() != -1) {
                $scorePaper += $response->getMark();
            } else {
                $scoreTemp = true;
            }
        }

        $infosPaper['scorePaper'] = $scorePaper;
        $infosPaper['scoreTemp'] = $scoreTemp;

        return $infosPaper;
    }

    /**
     * For all papers for an user and an exercise get scorePaper, maxExoScore, scoreTemp (all questions marked or no)
     *
     * @access public
     *
     * @param integer $userId id User
     * @param integer $exoId id Exercise
     *
     * @return array
     */
    public function getScoresUser($userId, $exoId)
    {
        $tabScoresUser = array();
        $i = 0;

        $papers = $this->om
                       ->getRepository('UJMExoBundle:Paper')
                       ->getExerciseUserPapers($userId, $exoId);

        foreach ($papers as $paper) {
            $infosPaper = $this->getInfosPaper($paper);
            $tabScoresUser[$i]['score']       = $infosPaper['scorePaper'];
            $tabScoresUser[$i]['maxExoScore'] = $infosPaper['maxExoScore'];
            $tabScoresUser[$i]['scoreTemp']   = $infosPaper['scoreTemp'];

            $i++;
        }

        return $tabScoresUser;
    }

    /**
     * To control the User's rights to this shared question
     *
     * @access public
     *
     * @param integer $questionID id Question
     *
     * @return array
     */
    public function controlUserSharedQuestion($questionID)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $questions = $this->om
                          ->getRepository('UJMExoBundle:Share')
                          ->getControlSharedQuestion($user->getId(), $questionID);

        return $questions;
    }

    /**
     * Trigger an event to log informations after to execute an exercise if the score is not temporary
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Paper\paper $paper
     *
     */
    public function manageEndOfExercise(Paper $paper)
    {
        $paperInfos = $this->getInfosPaper($paper);

        if (!$paperInfos['scoreTemp']) {
            $event = new LogExerciseEvaluatedEvent($paper->getExercise(), $paperInfos);
            $this->eventDispatcher->dispatch('log', $event);
        }
    }

    /**
     * Get information if the categories are linked with question, allow to know if a category can be deleted or no
     *
     * @access public
     *
     * @return array[boolean]
     */
    public function getLinkedCategories()
    {
        $linkedCategory = array();
        $repositoryCategory = $this->om
                                   ->getRepository('UJMExoBundle:Category');

        $repositoryQuestion = $this->om
                                   ->getRepository('UJMExoBundle:Question');

        $categoryList = $repositoryCategory->findAll();


        foreach ($categoryList as $category) {
          $questionLink = $repositoryQuestion->findOneBy(array('category' => $category->getId()));
          if (!$questionLink) {
              $linkedCategory[$category->getId()] = 0;
          } else {
              $linkedCategory[$category->getId()] = 1;
          }
        }

        return $linkedCategory;
    }

    /**
     * To control the max attemps, allow to know if an user can again execute an exercise
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Exercise $exercise
     * @param \UJM\ExoBundle\Entity\User $user
     * @param boolean $exoAdmin
     *
     * @return boolean
     */
    public function controlMaxAttemps($exercise, $user, $exoAdmin)
    {
        if (($exoAdmin === false) && ($exercise->getMaxAttempts() > 0)
            && ($exercise->getMaxAttempts() <= $this->getNbPaper($user->getId(),
            $exercise->getId(), true))
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * The user must be registered (and the dates must be good or the user must to be admin for the exercise)
     *
     * @access public
     *
     * @param boolean $exoAdmin
     * @param \UJM\ExoBundle\Entity\Exercise $exercise
     *
     * @return boolean
     */
    public function controlDate($exoAdmin, $exercise)
    {
        if (
            ((($exercise->getStartDate()->format('Y-m-d H:i:s') <= date('Y-m-d H:i:s'))
            && (($exercise->getUseDateEnd() == 0)
            || ($exercise->getEndDate()->format('Y-m-d H:i:s') >= date('Y-m-d H:i:s'))))
            || ($exoAdmin === true))
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * to return badges linked with the exercise
     *
     * @access public
     *
     * @param integer $resourceId id Claroline Resource
     *
     * @return \Icap\BadgeBundle\Entity\Badge[]
     */
    public function getBadgeLinked($resourceId)
    {
        $badges = array();
        $em = $this->doctrine->getManager();
        $badgesRules = $em->getRepository('IcapBadgeBundle:BadgeRule')
                          ->findBy(array('resource' => $resourceId));

        foreach ($badgesRules as $br) {
            $badge = $em->getRepository('IcapBadgeBundle:Badge')
                          ->findBy(array('id' => $br->getAssociatedBadge(), 'deletedAt' => null));
            if($badge) {
                $badges[] = $br->getAssociatedBadge();
            }
        }

        return $badges;
    }

    /**
     *
     * to return infos badges for an exercise and an user
     *
     * @access public
     *
     * @param integer $userId id User
     * @param integer $resourceId id Claroline Resource
     * @param String $locale given by container->getParameter('locale') FR, EN ....
     *
     * @return array[\Claroline\CoreBundle\Entity\Badge\Badge]
     */
    public function badgesInfoUser($userId, $resourceId, $locale)
    {
        $em = $this->doctrine->getManager();
        $badgesInfoUser = array();
        $i = 0;

        $exoBadges = $this->getBadgeLinked($resourceId);
        foreach($exoBadges as $badge) {
            //if ($badge->getDeletedAt() == '') {
                $brs = $em->getRepository('IcapBadgeBundle:BadgeRule')
                          ->findBy(array(
                              'associatedBadge' => $badge->getId()
                       ));
                if (count($brs) == 1) {
                    $trans = $em->getRepository('IcapBadgeBundle:BadgeTranslation')
                                ->findOneBy(array(
                                    'badge'  => $badge->getId(),
                                    'locale' => $locale
                             ));
                    $badgesInfoUser[$i]['badgeName'] = $trans->getName();

                    $userBadge = $em->getRepository('IcapBadgeBundle:UserBadge')
                                    ->findOneBy(array(
                                        'user'  => $userId,
                                        'badge' => $badge->getId()
                                 ));
                    if ($userBadge) {
                        $badgesInfoUser[$i]['issued'] = $userBadge->getIssuedAt();
                    } else {
                        $badgesInfoUser[$i]['issued'] = -1;
                    }

                    $i++;
                }
            //}
        }

        return $badgesInfoUser;
    }

    /**
     *
     * Call after applied a filter in a questions list to know the actions allowed for each interaction
     *
     * @access public
     *
     * @param Collection of \UJM\ExoBundle\Entity\Interaction $listInteractions
     * @param integer $userID id User
     * @param Doctrine EntityManager $em
     *
     * @return array
     */
    public function getActionsAllQuestions($listInteractions, $userID, $em)
    {
        $allActions           = array();
        $actionQ              = array();
        $questionWithResponse = array();
        $alreadyShared        = array();
        $sharedWithMe         = array();
        $shareRight           = array();

        foreach ($listInteractions as $interaction) {
                if ($interaction->getQuestion()->getUser()->getId() == $userID) {
                    $actionQ[$interaction->getQuestion()->getId()] = 1; // my question

                    $actions = $this->getActionInteraction($em, $interaction);
                    $questionWithResponse += $actions[0];
                    $alreadyShared += $actions[1];
                } else {
                    $sharedQ = $em->getRepository('UJMExoBundle:Share')
                    ->findOneBy(array('user' => $userID, 'question' => $interaction->getQuestion()->getId()));

                    if (count($sharedQ) > 0) {
                        $actionQ[$interaction->getQuestion()->getId()] = 2; // shared question

                        $actionsS = $this->getActionShared($em, $sharedQ);
                        $sharedWithMe += $actionsS[0];
                        $shareRight += $actionsS[1];
                        $questionWithResponse += $actionsS[2];
                    } else {
                        $actionQ[$interaction->getQuestion()->getId()] = 3; // other
                    }
                }
            }

        $allActions[0] = $actionQ;
        $allActions[1] = $questionWithResponse;
        $allActions[2] = $alreadyShared;
        $allActions[3] = $sharedWithMe;
        $allActions[4] = $shareRight;

        return $allActions;
    }

    /**
     * For an interaction know if it's linked with response and if it's shared
     *
     * @access public
     *
     * @param Doctrine EntityManager $em
     * @param \UJM\ExoBundle\Entity\Interaction $interaction
     *
     * @return array
     */
    public function getActionInteraction($em, $interaction)
    {
        $response = $em->getRepository('UJMExoBundle:Response')
            ->findBy(array('interaction' => $interaction->getId()));
        if (count($response) > 0) {
            $questionWithResponse[$interaction->getId()] = 1;
        } else {
            $questionWithResponse[$interaction->getId()] = 0;
        }

        $share = $em->getRepository('UJMExoBundle:Share')
            ->findBy(array('question' => $interaction->getQuestion()->getId()));
        if (count($share) > 0) {
            $alreadyShared[$interaction->getQuestion()->getId()] = 1;
        } else {
            $alreadyShared[$interaction->getQuestion()->getId()] = 0;
        }

        $actions[0] = $questionWithResponse;
        $actions[1] = $alreadyShared;

        return $actions;
    }

    /**
     * For an shared interaction whith me, know if it's linked with response and if I can modify it
     *
     * @access public
     *
     * @param Doctrine EntityManager $em
     * @param \UJM\ExoBundle\Entity\Share $shared
     *
     * @return array
     */
    public function getActionShared($em, $shared)
    {
        $inter = $em->getRepository('UJMExoBundle:Interaction')
                ->findOneBy(array('question' => $shared->getQuestion()->getId()));

        $sharedWithMe[$shared->getQuestion()->getId()] = $inter;
        $shareRight[$inter->getId()] = $shared->getAllowToModify();

        $response = $em->getRepository('UJMExoBundle:Response')
            ->findBy(array('interaction' => $inter->getId()));

        if (count($response) > 0) {
            $questionWithResponse[$inter->getId()] = 1;
        } else {
            $questionWithResponse[$inter->getId()] = 0;
        }

        $actionsS[0] = $sharedWithMe;
        $actionsS[1] = $shareRight;
        $actionsS[2] = $questionWithResponse;

        return $actionsS;
    }

    /**
     * Get the types of QCM, Multiple response, unique response
     *
     * @access public
     *
     * @return array
     */
    public function getTypeQCM()
    {
        $em = $this->doctrine->getManager();

        $typeQCM = array();
        $types = $em->getRepository('UJMExoBundle:TypeQCM')
                    ->findAll();

        foreach ($types as $type) {
            $typeQCM[$type->getId()] = $type->getCode();
        }

        return $typeQCM;
    }

    /**
     * Get the types of open question long, short, numeric, one word
     *
     * @access public
     *
     * @return array
     */
    public function getTypeOpen()
    {
        $em = $this->doctrine->getManager();

        $typeOpen = array();
        $types = $em->getRepository('UJMExoBundle:TypeOpenQuestion')
                    ->findAll();

        foreach ($types as $type) {
            $typeOpen[$type->getId()] = $type->getCode();
        }

        return $typeOpen;
    }

    /**
     * Get the types of Matching, Multiple response, unique response
     *
     * @access public
     *
     * @return array
     */
    public function getTypeMatching()
    {
        $em = $this->doctrine->getManager();

        $typeMatching = array();
        $types = $em->getRepository('UJMExoBundle:TypeMatching')
                    ->findAll();

        foreach ($types as $type) {
            $typeMatching[$type->getId()] = $type->getCode();
        }

        return $typeMatching;
    }

    /**
     * Get penalty for an interaction and a paper
     *
     * @access private
     *
     * @param \UJM\ExoBundle\Entity\Interaction $interaction
     * @param integer $paperID id Paper
     *
     * @return array
     */
    private function getPenalty($interaction, $paperID)
    {
        $penalty = 0;

        $hints = $interaction->getHints();

        foreach ($hints as $hint) {
            $lhp = $this->om
                        ->getRepository('UJMExoBundle:LinkHintPaper')
                        ->getLHP($hint->getId(), $paperID);
            if (count($lhp) > 0) {
                $signe = substr($hint->getPenalty(), 0, 1);

                if ($signe == '-') {
                    $penalty += substr($hint->getPenalty(), 1);
                } else {
                    $penalty += $hint->getPenalty();
                }
            }
        }

        return $penalty;
    }

    /**
     * Get interactions in order for a paper
     *
     * @access private
     *
     * @param Collection of \UJM\ExoBundle\Entity\Interaction $interactions
     * @param String $order
     *
     * @return array[Interaction]
     */
    private function orderInteractions($interactions, $order)
    {
        $inter = array();
        $order = substr($order, 0, strlen($order) - 1);
        $order = explode(';', $order);

        foreach ($order as $interId) {
            foreach ($interactions as $key => $interaction) {
                if ($interaction->getId() == $interId) {
                    $inter[] = $interaction;
                    unset($interactions[$key]);
                    break;
                }
            }
        }

        return $inter;
    }

    /**
     * Get responses in order for a paper
     *
     * @access private
     *
     * @param Collection of \UJM\ExoBundle\Entity\Response $responses
     * @param String $order
     *
     * @çeturn array[Interaction]
     */
    private function orderResponses($responses, $order)
    {
        $resp = array();
        $order = substr($order, 0, strlen($order) - 1);
        $order = explode(';', $order);
        foreach ($order as $interId) {
            $tem = 0;
            foreach ($responses as $key => $response) {
                if ($response->getInteraction()->getId() == $interId) {
                    $tem++;
                    $resp[] = $response;
                    unset($responses[$key]);
                    break;
                }
            }
            //if no response
            if ($tem == 0) {
                $response = new \UJM\ExoBundle\Entity\Response();
                $response->setResponse('');
                $response->setMark(0);

                $resp[] = $response;
            }
        }

        return $resp;
    }

    /**
     * Get score for an open question with one word
     *
     * @access private
     *
     * @param String $response
     * @param \UJM\ExoBundle\Entity\InteractionOpen $interOpen
     *
     * @return float
     */
    private function getScoreOpenOneWord($response, $interOpen)
    {
        $score = 0;
        foreach ($interOpen->getWordResponses() as $wr) {
            $score += $this->getScoreWordResponse($wr, $response);
        }

        return $score;

    }

    /**
     * Get score for a question with key word
     *
     * @access private
     *
     * @param \UJM\ExoBundle\Entity\WordResponse $wr
     * @param String $response
     *
     * @return float
     */
    private function getScoreWordResponse($wr, $response)
    {
        $score = 0;
        if ( ((strcasecmp(trim($wr->getResponse()), trim($response)) == 0
                && $wr->getCaseSensitive() == false))
                    || (trim($wr->getResponse()) == trim($response)) ) {
            $score = $wr->getScore();
        }

        return $score;
    }

    /**
     * Get score for an open question with short answer
     *
     * @access private
     *
     * @param String $response
     * @param \UJM\ExoBundle\Entity\InteractionOpen $interOpen
     *
     * @return float
     */
    private function getScoreShortResponse($response, $interOpen)
    {
        $score = 0;

        foreach($interOpen->getWordResponses() as $wr) {
            $pattern = '/'.$wr->getResponse().'/';
            if (!$wr->getCaseSensitive()) {
                $pattern .= 'i';
            }
            $subject = '/'.$response.'/';
            if (preg_match($pattern, $subject)) {
                $score += $wr->getScore();
            }
        }

        return $score;
    }

    /**
     * Add an Interaction in an exercise if created since an exercise
     *
     * @access public
     *
     * @param type $inter
     * @param UJM\ExoBundle\Entity\Exercise $exercise instance of Exercise
     * @param Doctrine EntityManager $em
     */
    public function addQuestionInExercise($inter, $exercise) {
        if ($exercise != null) {
            if ($this->isExerciseAdmin($exercise)) {
                $this->setExerciseQuestion($exercise, $inter);
            }
        }
    }

    /**
     * To control the User's rights to this question
     *
     * @access private
     *
     * @param integer $questionID id Question
     *
     * @return Doctrine Query Result
     */
    public function controlUserQuestion($questionID, $container, $em)
    {
        $user = $container->get('security.token_storage')->getToken()->getUser();

        $question = $em
            ->getRepository('UJMExoBundle:Question')
            ->getControlOwnerQuestion($user->getId(), $questionID);

        return $question;
    }
}
