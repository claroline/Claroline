<?php

/**
 * ExoOnLine
 * Copyright or © or Copr. Université Jean Monnet (France), 2012
 * dsi.dev@univ-st-etienne.fr
 *
 * This software is a computer program whose purpose is to [describe
 * functionalities and technical features of your software].
 *
 * This software is governed by the CeCILL license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
*/

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace UJM\ExoBundle\Services\classes;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeClaim;
use Claroline\CoreBundle\Entity\Badge\BadgeCollection;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Badge\BadgeTranslation;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity\SoftDeleteableEntity;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

use UJM\ExoBundle\Entity\ExerciseQuestion;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Event\Log\LogExerciseEvaluatedEvent;

class exerciseServices
{
    protected $doctrine;
    protected $securityContext;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(Registry $doctrine, SecurityContextInterface $securityContext, EventDispatcherInterface $eventDispatcher)
    {
        $this->doctrine        = $doctrine;
        $this->securityContext = $securityContext;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getIP()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    public function responseQCM($request, $paperID = 0)
    {
        $res = array();
        $interactionQCMID = $request->request->get('interactionQCMToValidated');
        $response = array();

        $em = $this->doctrine->getManager();
        $interQCM = $em->getRepository('UJMExoBundle:InteractionQCM')->find($interactionQCMID);

        if ($interQCM->getTypeQCM()->getId() == 2) {
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

            foreach ($response as $res) {
                $score += $markByChoice[$res];
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
     * Return the number of papers for an exercise and foran user
     *
     */
    public function getNbPaper($uid, $exoID)
    {
        $papers = $this->doctrine
                       ->getManager()
                       ->getRepository('UJMExoBundle:Paper')
                       ->getExerciseUserPapers($uid, $exoID);

        return count($papers);
    }

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

        if ($interOpen->getTypeOpenQuestion() == 'long') {
            $response = $request->request->get('interOpenLong');
        }

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

    public function responseHole($request, $paperID = 0)
    {
        $em = $this->doctrine->getManager();
        $res = array();
        $interactionHoleID = $request->request->get('interactionHoleToValidated');
        $tabResp = array();

        $penalty = 0;
        $session = $request->getSession();

        $em = $this->doctrine->getManager();
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


        //$score .= '/'.$this->holeMaxScore($interHole);
        $score = $this->holeMark($interHole, $request->request, $penalty);

        foreach($interHole->getHoles() as $hole) {
            $response = $request->get('blank_'.$hole->getPosition());
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

    public function holeMark($interHole, $request, $penalty)
    {
        $em = $this->doctrine->getManager();
        $score = 0;

        foreach($interHole->getHoles() as $hole) {
            $response = $request->get('blank_'.$hole->getPosition());
            if ($hole->getSelector() == true) {
                $wr = $em->getRepository('UJMExoBundle:WordResponse')->find($response);
                $score += $wr->getScore();
            } else {
                foreach ($hole->getWordResponses() as $wr) {
                    if ($wr->getResponse() == $response) {
                        $score += $wr->getScore();
                    }
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

    // Check if the suggested answer zone isn't already right in order not to have points twice
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

    public function getExerciseTotalScore($exoID)
    {
        $exoTotalScore = 0;

        $eqs = $this->doctrine
            ->getManager()
            ->getRepository('UJMExoBundle:ExerciseQuestion')
            ->findBy(array('exercise' => $exoID));

        foreach ($eqs as $eq) {
            $interaction = $this->doctrine
                ->getManager()
                ->getRepository('UJMExoBundle:Interaction')
                ->getInteraction($eq->getQuestion()->getId());//echo $interaction[0]->getInvite();

            switch ($interaction[0]->getType()){
                case 'InteractionQCM':
                    $interQCM = $this->doctrine
                                     ->getManager()
                                     ->getRepository('UJMExoBundle:InteractionQCM')
                                     ->getInteractionQCM($interaction[0]->getId());
                    $scoreMax = $this->qcmMaxScore($interQCM[0]);
                    break;
                case 'InteractionGraphic':
                    $interGraphic = $this->doctrine
                                         ->getManager()
                                         ->getRepository('UJMExoBundle:InteractionGraphic')
                                         ->getInteractionGraphic($interaction[0]->getId());
                    $scoreMax = $this->graphicMaxScore($interGraphic[0]);
                    break;
                case 'InteractionOpen':
                    $interOpen = $this->doctrine
                                      ->getManager()
                                      ->getRepository('UJMExoBundle:InteractionOpen')
                                      ->getInteractionOpen($interaction[0]->getId());
                    $scoreMax = $this->openMaxScore($interOpen[0]);
                    break;
                case 'InteractionHole':
                    $interHole = $this->doctrine
                                      ->getManager()
                                      ->getRepository('UJMExoBundle:InteractionHole')
                                      ->getInteractionHole($interaction[0]->getId());
                    $scoreMax = $this->holeMaxScore($interHole[0]);
                    break;
            }

            $exoTotalScore += $scoreMax;
        }

        return $exoTotalScore;
    }

    public function getExercisePaperTotalScore($paperID)
    {
        $exercisePaperTotalScore = 0;
        $paper = $interaction = $this->doctrine
                                     ->getManager()
                                     ->getRepository('UJMExoBundle:Paper')
                                     ->find($paperID);

        $interQuestions = $paper->getOrdreQuestion();
        $interQuestions = substr($interQuestions, 0, strlen($interQuestions) - 1);
        $interQuestionsTab = explode(";", $interQuestions);

        foreach ($interQuestionsTab as $interQuestion) {
            $interaction = $this->doctrine->getManager()->getRepository('UJMExoBundle:Interaction')->find($interQuestion);
            switch ( $interaction->getType()) {
                case "InteractionQCM":
                    $interQCM = $this->doctrine
                                     ->getManager()
                                     ->getRepository('UJMExoBundle:InteractionQCM')
                                     ->getInteractionQCM($interaction->getId());
                    $exercisePaperTotalScore += $this->qcmMaxScore($interQCM[0]);
                    break;

                case "InteractionGraphic":
                    $interGraphic = $this->doctrine
                                         ->getManager()
                                         ->getRepository('UJMExoBundle:InteractionGraphic')
                                         ->getInteractionGraphic($interaction->getId());
                    $exercisePaperTotalScore += $this->graphicMaxScore($interGraphic[0]);
                    break;

                case "InteractionHole":
                    $interHole = $this->doctrine
                                         ->getManager()
                                         ->getRepository('UJMExoBundle:InteractionHole')
                                         ->getInteractionHole($interaction->getId());
                    $exercisePaperTotalScore += $this->holeMaxScore($interHole[0]);
                    break;

                case "InteractionOpen":
                    $interOpen = $this->doctrine
                                      ->getManager()
                                      ->getRepository('UJMExoBundle:InteractionOpen')
                                      ->getInteractionOpen($interaction->getId());
                    $exercisePaperTotalScore += $this->openMaxScore($interOpen[0]);
                    break;
            }
        }

        return $exercisePaperTotalScore;
    }

    public function qcmMaxScore($interQCM)
    {
        $scoreMax = 0;

        /*$interQCM = $this->doctrine
            ->getManager()
            ->getRepository('UJMExoBundle:InteractionQCM')
            ->getInteractionQCM($interaction->getId());*/

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

    public function graphicMaxScore($interGraphic)
    {
        $scoreMax = 0;

        /*$interGraphic = $this->doctrine
            ->getManager()
            ->getRepository('UJMExoBundle:InteractionGraphic')
            ->getInteractionGraphic($interaction->getId());*/

        $rightCoords = $this->doctrine
            ->getManager()
            ->getRepository('UJMExoBundle:Coords')
            ->findBy(array('interactionGraphic' => $interGraphic->getId()));

        foreach ($rightCoords as $score) {
            $scoreMax += $score->getScoreCoords(); // Score max
        }

        return $scoreMax;
    }

    public function openMaxScore($interOpen)
    {
        $scoreMax = 0;

        /*$interOpen = $this->doctrine
            ->getManager()
            ->getRepository('UJMExoBundle:InteractionOpen')
            ->getInteractionOpen($interaction->getId());*/

        if ($interOpen->getTypeOpenQuestion() == 'long') {
            $scoreMax = $interOpen->getScoreMaxLongResp();
        }

        return $scoreMax;
    }

    public function setExerciseQuestion($exercise, $interX)
    {
        $exo = $this->doctrine->getManager()->getRepository('UJMExoBundle:Exercise')->find($exercise);
        $eq = new ExerciseQuestion($exo, $interX->getInteraction()->getQuestion());

        $dql = 'SELECT max(eq.ordre) FROM UJM\ExoBundle\Entity\ExerciseQuestion eq '
              . 'WHERE eq.exercise='.$exercise;
        $query = $this->doctrine->getManager()->createQuery($dql);
        $maxOrdre = $query->getResult();

        $eq->setOrdre((int) $maxOrdre[0][1] + 1);
        $this->doctrine->getManager()->persist($eq);

        $this->doctrine->getManager()->flush();
    }

    /**
     * Round up or down parameter's value
     *
     */
    public function roundUpDown($toBeAdjusted)
    {
        return (round($toBeAdjusted / 0.5) * 0.5);
    }

    /**
     * To control the subscription
     *
     */
    public function isExerciseAdmin($exercise)
    {
        $user = $this->securityContext->getToken()->getUser();

        $subscription = $this->doctrine
            ->getManager()
            ->getRepository('UJMExoBundle:Subscription')
            ->getControlExerciseEnroll($user->getId(), $exercise->getId());

        if (count($subscription) > 0) {
            return $subscription[0]->getAdmin();
        } else {
            $collection = new ResourceCollection(array($exercise->getResourceNode()));
            if ($this->securityContext->isGranted('edit', $collection)) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    public function getInfosPaper($paper)
    {
        $infosPaper = array();
        $scorePaper = 0;
        $scoreTemp = false;

        $em = $this->doctrine->getManager();

        $interactions = $this->doctrine
            ->getManager()
            ->getRepository('UJMExoBundle:Interaction')
            ->getPaperInteraction($em, str_replace(';', '\',\'', substr($paper->getOrdreQuestion(), 0, -1)));

        $interactions = $this->orderInteractions($interactions, $paper->getOrdreQuestion());

        $infosPaper['interactions'] = $interactions;

        $responses = $this->doctrine
            ->getManager()
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

    public function getScoresUser($userId, $exoId)
    {
        $tabScoresUser = array();
        $i = 0;

        $papers = $this->doctrine
                       ->getManager()
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
     */
    public function controlUserSharedQuestion($questionID)
    {
        $user = $this->securityContext->getToken()->getUser();

        $questions = $this->doctrine
                          ->getManager()
                          ->getRepository('UJMExoBundle:Share')
                          ->getControlSharedQuestion($user->getId(), $questionID);

        return $questions;
    }

    public function manageEndOfExercise(Paper $paper)
    {
        $paperInfos = $this->getInfosPaper($paper);

        if (!$paperInfos['scoreTemp']) {
            $event = new LogExerciseEvaluatedEvent($paper->getExercise(), $paperInfos['scorePaper']);
            $this->eventDispatcher->dispatch('log', $event);
        }
    }

    public function getLinkedCategories()
    {
        $linkedCategory = array();
        $repositoryCategory = $this->doctrine
                   ->getManager()
                   ->getRepository('UJMExoBundle:Category');

        $repositoryQuestion = $this->doctrine
                   ->getManager()
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
     * To control the max attemps
     *
     */
    public function controlMaxAttemps($exercise, $user, $exoAdmin)
    {
        if (($exoAdmin != 1) && ($exercise->getMaxAttempts() > 0)
            && ($exercise->getMaxAttempts() <= $this->getNbPaper($user->getId(),
            $exercise->getId()))
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     *
     * to return badges linked with the exercise
     */
    public function getBadgeLinked($resourceId)
    {
        /*$badges = array();
        $em = $this->doctrine->getManager();
        $badgesRules = $em->getRepository('ClarolineCoreBundle:Badge\BadgeRule')
                          ->findBy(array('resource' => $resourceId));

        foreach ($badgesRules as $br) {

            $badgesSearch = $em->getRepository('ClarolineCoreBundle:Badge\Badge')
                               ->findByNameAndLocale('BadgeExercice2', 'fr');

            foreach($badgesSearch as $badge) {
                if ($badge->getId() == $br->getAssociatedBadge()->getId()) {
                    $badges[] = $badge;
                }
            }

        }*/

        $badges = array();
        $em = $this->doctrine->getManager();
        $badgesRules = $em->getRepository('ClarolineCoreBundle:Badge\BadgeRule')
                          ->findBy(array('resource' => $resourceId));

        foreach ($badgesRules as $br) {
            $badge = $em->getRepository('ClarolineCoreBundle:Badge\Badge')
                          ->findBy(array('id' => $br->getAssociatedBadge(), 'deletedAt' => null));
            if($badge) {
                $badges[] = $br->getAssociatedBadge();
            }
        }

        return $badges;
    }

    /**
     *
     * to return infos badges fon an exercise and an user
     */
    public function badgesInfoUser($userId, $resourceId, $locale)
    {
        $em = $this->doctrine->getManager();
        $badgesInfoUser = array();
        $i = 0;

        $exoBadges = $this->getBadgeLinked($resourceId);
        foreach($exoBadges as $badge) {
            //if ($badge->getDeletedAt() == '') {
                $brs = $em->getRepository('ClarolineCoreBundle:Badge\BadgeRule')
                          ->findBy(array(
                              'associatedBadge' => $badge->getId()
                       ));
                if (count($brs) == 1) {
                    $trans = $em->getRepository('ClarolineCoreBundle:Badge\BadgeTranslation')
                                ->findOneBy(array(
                                    'badge'  => $badge->getId(),
                                    'locale' => $locale
                             ));
                    $badgesInfoUser[$i]['badgeName'] = $trans->getName();

                    $userBadge = $em->getRepository('ClarolineCoreBundle:Badge\UserBadge')
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

    private function getPenalty($interaction, $paperID)
    {
        $penalty = 0;
        $em = $this->doctrine->getManager();

        $hints = $interaction->getHints();

        foreach ($hints as $hint) {
            $lhp = $this->doctrine
                        ->getManager()
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
}
