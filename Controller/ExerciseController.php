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

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;

use UJM\ExoBundle\Form\ExerciseType;
use UJM\ExoBundle\Form\ExerciseHandler;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\ExerciseQuestion;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Entity\Interaction;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Exercise controller.
 *
 */
class ExerciseController extends Controller
{

    /**
     * Displays a form to edit an existing Exercise entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($id);
        $this->checkAccess($exercise);

        $workspace = $exercise->getWorkspace();

        $exoAdmin = $this->isExerciseAdmin($id);

        if ($exoAdmin == 1) {

            if (!$exercise) {
                throw $this->createNotFoundException('Unable to find Exercise entity.');
            }

            $editForm = $this->createForm(new ExerciseType(), $exercise);

            return $this->render(
                'UJMExoBundle:Exercise:edit.html.twig',
                array(
                    'workspace'   => $workspace,
                    'entity'      => $exercise,
                    'edit_form'   => $editForm->createView(),
                )
            );
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_open'));
        }
    }

    /**
     * Edits an existing Exercise entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($id);

        $entity = $em->getRepository('UJMExoBundle:Exercise')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Exercise entity.');
        }

        $editForm    = $this->createForm(new ExerciseType(), $entity);

        $formHandler = new ExerciseHandler(
            $editForm, $this->get('request'), $this->getDoctrine()->getManager(),
            $this->container->get('security.context')->getToken()->getUser(), 'update'
        );

        if ($formHandler->process()) {
            return $this->redirect(
                $this->generateUrl(
                    'claro_resource_open', array(
                    'resourceType' => $exercise->getResourceType()->getName(), 'resourceId' => $id)
                )
            );
        }

        return $this->render(
            'UJMExoBundle:Exercise:edit.html.twig',
            array(
                'entity'      => $entity,
                'edit_form'   => $editForm->createView(),
            )
        );
    }

    /**
     * Finds and displays a Exercise entity if the User is enrolled.
     *
     */
    public function openAction($exerciseId)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($exerciseId);
        $this->checkAccess($exercise);

        $allowToCompose = 0;
        $exoAdmin = $this->isExerciseAdmin($exerciseId);

        $workspace = $exercise->getWorkspace();

        if (!$exercise) {
            throw $this->createNotFoundException('Unable to find Exercise entity.');
        }

        if (($this->controlDate($exoAdmin, $exercise) === true)
            && ($this->controlMaxAttemps($exercise, $user, $exoAdmin) === true)
        ) {
            $allowToCompose = 1;
        }

        $nbQuestions = $em->getRepository('UJMExoBundle:ExerciseQuestion')->getCountQuestion($exerciseId);
        
        return $this->render(
            'UJMExoBundle:Exercise:show.html.twig',
            array(
                'workspace'      => $workspace,
                'entity'         => $exercise,
                'exoAdmin'       => $exoAdmin,
                'allowToCompose' => $allowToCompose,
                'userId'         => $user->getId(),
                'nbQuestion'     => $nbQuestions['nbq']
            )
        );
    }

    /**
     * Finds and displays a Question entity to this Exercise.
     *
     */
    public function showQuestionsAction($id, $pageNow, $category2Find, $title2Find)
    {
        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($id);
        $this->checkAccess($exercise);

        $workspace = $exercise->getWorkspace();

        $exoAdmin = $this->isExerciseAdmin($id);

        $max = 3; // Max Per Page
        $request = $this->get('request');
        $page = $request->query->get('page', 1);

        if ($exoAdmin == 1) {
            $Interactions = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Interaction')
                ->getExerciseInteraction($em, $id, 0);

            $questionWithResponse = array();
            foreach ($Interactions as $interaction) {
                $response = $em->getRepository('UJMExoBundle:Response')
                    ->findBy(array('interaction' => $interaction->getId()));
                if (count($response) > 0) {
                    $questionWithResponse[] = 1;
                } else {
                    $questionWithResponse[] = 0;
                }
            }

            if ($category2Find != '' && $title2Find != '' && $category2Find != 'z' && $title2Find != 'z') {
                $i = 1 ;
                $pos = 0 ;
                $temp = 0;

                foreach ($Interactions as $interaction) {
                    if ($interaction->getQuestion()->getCategory() == $category2Find) {
                        $temp = $i;
                    }
                    if ($interaction->getQuestion()->getTitle() == $title2Find && $temp == $i) {
                        $pos = $i;
                        break;
                    }
                    $i++;
                }

                if ($pos % $max == 0) {
                    $pageNow = $pos / $max;
                } else {
                    $pageNow = ceil($pos / $max);
                }
            }

            // Pagination finded documents
            $adapterQuestion = new ArrayAdapter($Interactions);
            $pagerQuestion = new Pagerfanta($adapterQuestion);

            try {
                if ($pageNow == 0) {
                    $interactions = $pagerQuestion
                        ->setMaxPerPage($max)
                        ->setCurrentPage($page)
                        ->getCurrentPageResults();
                } else {
                    $interactions = $pagerQuestion
                        ->setMaxPerPage($max)
                        ->setCurrentPage($pageNow)
                        ->getCurrentPageResults();
                }
            } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
                throw $this->createNotFoundException("Cette page n'existe pas.");
            }

            return $this->render(
                'UJMExoBundle:Question:exerciseQuestion.html.twig',
                array(
                    'workspace'            => $workspace,
                    'interactions'         => $interactions,
                    'exerciseID'           => $id,
                    'questionWithResponse' => $questionWithResponse,
                    'pagerQuestion'        => $pagerQuestion
                )
            );
        } else {
            return $this->redirect($this->generateUrl('exercise'));
        }
    }

    /**
    *To import in this Exercise a Question of the User's bank.
    *
    */
    public function importQuestionAction($exoID, $page2Go, $maxPage, $nbItem)
    {
        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($exoID);
        $this->checkAccess($exercise);

        $workspace = $exercise->getWorkspace();

        $user = $this->container->get('security.context')->getToken()->getUser();
        $uid = $user->getId();

        $exoAdmin = $this->isExerciseAdmin($exoID);

        // To paginate the result :
        $request = $this->get('request'); // Get the request which contains the following parameters :
        $page = $request->query->get('page', 1); // Get the choosen page (default 1)
        $click = $request->query->get('click', 'my'); // Get which array to change page (default 'my question')
        $pagerMy = $request->query->get('pagerMy', 1); // Get the page of the array my question (default 1)
        $pagerShared = $request->query->get('pagerShared', 1); // Get the pager of the array my shared question (default 1)
        $pageToGo = $request->query->get('pageToGo'); // Page to go for the list of the questions of the exercise
        $max = 2; // Max of questions per page

        // If change page of my questions array
        if ($click == 'my') {
            // The choosen new page is for my questions array
            $pagerMy = $page;
        // Else if change page of my shared questions array
        } else if ($click == 'shared') {
            // The choosen new page is for my shared questions array
            $pagerShared = $page;
        }

        if ($exoAdmin == 1) {

            $Interactions = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Interaction')
                ->getUserInteractionImport($this->getDoctrine()->getManager(), $uid, $exoID);

            $shared = $em->getRepository('UJMExoBundle:Share')
                    ->getUserInteractionSharedImport($exoID, $uid, $em);

            $SharedWithMe = array();

            $end = count($shared);

            for ($i = 0; $i < $end; $i++) {
                $SharedWithMe[] = $em->getRepository('UJMExoBundle:Interaction')
                    ->findOneBy(array('question' => $shared[$i]->getQuestion()->getId()));
            }

            // Do the pagination of the result depending on which page of which array was changed
            // (My questions array)
            $adapterMy = new ArrayAdapter($Interactions);
            $pagerfantaMy = new Pagerfanta($adapterMy);

            // (My shared questions array)
            $adapterShared = new ArrayAdapter($SharedWithMe);
            $pagerfantaShared = new Pagerfanta($adapterShared);

            try {
                // Test if my questions array exists (try) and affects the matching results (which page, how many per page ...)
                $interactions = $pagerfantaMy
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pagerMy)
                    ->getCurrentPageResults();

                // Test if my shared questions array exists (try) and affects the matching results (which page, how many per page ...)
                $sharedWithMe = $pagerfantaShared
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pagerShared)
                    ->getCurrentPageResults();
            } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
                // If page don't exist
                throw $this->createNotFoundException("Cette page n'existe pas.");
            }

            if ($pageToGo) {
                $page2Go = $pageToGo;
            } else {
                // If new item > max per page, display next page
                $rest = $nbItem % $maxPage;

                if ($nbItem == 0) {
                    $page2Go = 0;
                }

                if ($rest == 0) {
                    $page2Go += 1;
                }
            }

            return $this->render(
                'UJMExoBundle:Question:import.html.twig',
                array(
                    'workspace'    => $workspace,
                    'interactions' => $interactions,
                    'exoID'        => $exoID,
                    'sharedWithMe' => $sharedWithMe,
                    'pagerMy'      => $pagerfantaMy,
                    'pagerShared'  => $pagerfantaShared,
                    'page2go'      => $page2Go
                )
            );
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise'));
        }
    }

    /**
     * To record the Question's import.
     *
     */
    public function importValidateAction($exoID, $qid, $page2go)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $question = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Question')
            ->getControlOwnerQuestion($user->getId(), $qid);

        if (count($question) > 0) {
            $em = $this->getDoctrine()->getManager();

            $exo = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
            $question = $em->getRepository('UJMExoBundle:Question')->find($qid);

            $eq = new ExerciseQuestion($exo, $question);

            $dql = 'SELECT max(eq.ordre) FROM UJM\ExoBundle\Entity\ExerciseQuestion eq '
                 . 'WHERE eq.exercise='.$exoID;
            $query = $em->createQuery($dql);
            $maxOrdre = $query->getResult();

            $eq->setOrdre((int) $maxOrdre[0][1] + 1);
            $em->persist($eq);

            $em->flush();

            return $this->redirect($this->generateUrl(
                'ujm_exercise_questions',
                    array(
                        'id' => $exoID,
                        'pageNow' => $page2go
                    )
                )
            );
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_import_question', array('exoID' => $exoID)));
        }
    }

    public function importValidateSharedAction($exoID, $qid, $page2go)
    {
        $em = $this->getDoctrine()->getManager();

        $question = $em->getRepository('UJMExoBundle:Share')
            ->findBy(array('question' => $qid));

        if (count($question) > 0) {

            $exo = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
            $question = $em->getRepository('UJMExoBundle:Question')->find($qid);

            $eq = new ExerciseQuestion($exo, $question);

            $dql = 'SELECT max(eq.ordre) FROM UJM\ExoBundle\Entity\ExerciseQuestion eq '
                 . 'WHERE eq.exercise='.$exoID;
            $query = $em->createQuery($dql);
            $maxOrdre = $query->getResult();

            $eq->setOrdre((int) $maxOrdre[0][1] + 1);
            $em->persist($eq);

            $em->flush();

            return $this->redirect($this->generateUrl(
                    'ujm_exercise_questions',
                    array(
                        'id' => $exoID,
                        'pageNow' => $page2go
                    )
                )
            );
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_import_question', array('exoID' => $exoID)));
        }
    }

    /**
     * Delete the Question of the exercise.
     *
     */
    public function deleteQuestionAction($exoID, $qid, $pageNow, $maxPage, $nbItem, $lastPage)
    {
        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($exoID);

        $this->checkAccess($exercise);

        $exoAdmin = $this->isExerciseAdmin($exoID);

        if ($exoAdmin == 1) {
            $em = $this->getDoctrine()->getManager();
            $eq = $em->getRepository('UJMExoBundle:ExerciseQuestion')
                ->findOneBy(array('exercise' => $exoID, 'question' => $qid));
            $em->remove($eq);
            $em->flush();

             // If delete last item of page, display the previous one
            $rest = $nbItem % $maxPage;

            if ($rest == 1 && $pageNow == $lastPage) {
                $pageNow -= 1;
            }
        }

        return $this->redirect($this->generateUrl(
                'ujm_exercise_questions',
                array(
                    'id' => $exoID,
                    'pageNow' => $pageNow
                )
            )
        );
    }

    /**
     * To create a paper in order to take an assessment
     *
     */
    public function exercisePaperAction($id)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $uid = $user->getId();

        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($id);

        $exoAdmin = $this->isExerciseAdmin($id);
        $this->checkAccess($exercise);

        $workspace = $exercise->getWorkspace();

        if ($this->controlDate($exoAdmin, $exercise) === true) {
            $session = $this->getRequest()->getSession();
            $orderInter = '';
            $tabOrderInter = array();

            $dql = 'SELECT max(p.numPaper) FROM UJM\ExoBundle\Entity\Paper p '
                . 'WHERE p.exercise='.$id.' AND p.user='.$uid;
            $query = $em->createQuery($dql);
            $maxNumPaper = $query->getResult();

            //Verify if it exists a not finished paper
            $paper = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Paper')
                ->getPaper($user->getId(), $id);

            //if not exist a paper no finished
            if (count($paper) == 0) {
                if ($this->controlMaxAttemps($exercise, $user, $exoAdmin) === false) {
                   return $this->redirect($this->generateUrl('ujm_exercise_open', array('exerciseId' => $id)));
                }

                $paper = new Paper();
                $paper->setNumPaper((int) $maxNumPaper[0][1] + 1);
                $paper->setExercise($exercise);
                $paper->setUser($user);
                $paper->setStart(new \Datetime());
                $paper->setArchive(0);
                $paper->setInterupt(0);

                $interactions = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Interaction')
                    ->getExerciseInteraction(
                        $this->getDoctrine()->getManager(), $id,
                        $exercise->getShuffle(), $exercise->getNbQuestion()
                    );

                foreach ($interactions as $interaction) {
                    $orderInter = $orderInter.$interaction->getId().';';
                    $tabOrderInter[] = $interaction->getId();
                }

                $paper->setOrdreQuestion($orderInter);
                $em->persist($paper);
                $em->flush();
            } else {
                $paper = $paper[0];
                $tabOrderInter = explode(';', $paper->getOrdreQuestion());
                unset($tabOrderInter[count($tabOrderInter) - 1]);
                $interactions[0] = $em->getRepository('UJMExoBundle:Interaction')->find($tabOrderInter[0]);
            }

            $session->set('tabOrderInter', $tabOrderInter);
            $session->set('paper', $paper->getId());
            $session->set('exerciseID', $id);

            $typeInter = $interactions[0]->getType();

            //To display selectioned question
            return $this->displayQuestion(1, $interactions[0], $typeInter, $exercise->getDispButtonInterrupt(), $workspace);
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_open', array('exerciseId' => $id)));
        }
    }

    /**
     * To navigate in the Questions of the assessment
     *
     */
    public function exercisePaperNavAction()
    {
        $response = '';
        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();
        $paper = $em->getRepository('UJMExoBundle:Paper')->find($session->get('paper'));
        $workspace = $paper->getExercise()->getWorkspace();
        $request = $this->getRequest();
        $typeInterToRecorded = $request->get('typeInteraction');

        $tabOrderInter = $session->get('tabOrderInter');

        //To record response
        $exerciseSer = $this->container->get('ujm.exercise_services');
        $ip = $exerciseSer->getIP();
        $interactionToValidatedID = $request->get('interactionToValidated');
        $response = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Response')
            ->getAlreadyResponded($session->get('paper'), $interactionToValidatedID);

        switch ($typeInterToRecorded) {
            case "InteractionQCM":
                $res = $exerciseSer->responseQCM($request, $session->get('paper'));
                break;

            case "InteractionGraphic":
                $res = $exerciseSer->responseGraphic($request, $session->get('paper'));
                break;

            case "InteractionHole":

                break;

            case "InteractionOpen":

                break;
        }

        if (count($response) == 0) {
            //INSERT Response
            $response = new Response();
            $response->setNbTries(1);
            $response->setPaper($paper);
            $response->setInteraction($em->getRepository('UJMExoBundle:Interaction')->find($interactionToValidatedID));
        } else {
            //UPDATE Response
            $response = $response[0];
            $response->setNbTries($response->getNbTries() + 1);
        }

        $response->setIp($ip);
        $score = explode('/', $res['score']);
        $response->setMark($score[0]);
        $response->setResponse($res['response']);

        $em->persist($response);
        $em->flush();

        //To display selectioned question
        $numQuestionToDisplayed = $request->get('numQuestionToDisplayed');

        if ($numQuestionToDisplayed == 'finish') {
            return $this->finishExercise();
        } else if ($numQuestionToDisplayed == 'interupt') {
            return $this->interuptExercise();
        } else {
            $interactionToDisplayedID = $tabOrderInter[$numQuestionToDisplayed - 1];
            $interactionToDisplay = $em->getRepository('UJMExoBundle:Interaction')->find($interactionToDisplayedID);
            $typeInterToDisplayed = $interactionToDisplay->getType();

            return $this->displayQuestion(
                $numQuestionToDisplayed, $interactionToDisplay, $typeInterToDisplayed,
                $response->getPaper()->getExercise()->getDispButtonInterrupt(), $workspace
            );
        }
    }

    public function docimologyAction($exerciseId, $nbPapers)
    {
        $exerciseSer = $this->container->get('ujm.exercise_services');
        $scoreMax = 0;
        $questionsResponsesTab = array();
        $seriesResponsesTab = array();
        $responsesTab;
        $questionList = array();
        $maxY = 4;
        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($exerciseId);
        $this->checkAccess($exercise);

        if ($this->get('security.context')->isGranted('ROLE_WS_CREATOR')) {

            $workspace = $exercise->getWorkspace();

            $exoScoreMax = $this->container->get('ujm.exercise_services')->getExerciseTotalScore($exerciseId);

            $marks = $this->container->get('ujm.exercise_services')->getExerciseHistoMarks($exerciseId);

            $tabMarks = array();

            $scoreList = '';

            foreach ($marks as $mark) {
                $score = round(($mark["noteExo"] / $exoScoreMax) * 20, 2);

                if (isset($tabMarks[(string) $score])) {
                    $tabMarks[(string) $score] += 1;
                } else {
                    if ($scoreList == '') {
                        $scoreList = (string) $score;
                    } else {
                        $scoreList .= ','.(string) $score;
                    }
                    $tabMarks[(string) $score] = 1;

                }
            }

            if (max($tabMarks) > 4) {
                $maxY = max($tabMarks);
            }

            $frequencyMarks = implode(",", $tabMarks);//echo $frequencyMarks;die();


            $eqs = $em->getRepository('UJMExoBundle:ExerciseQuestion')->findBy(array('exercise' => $exerciseId));
            foreach ($eqs as $eq) {
                $questionList[] = $eq->getQuestion()->getTitle();

                $interaction = $em->getRepository('UJMExoBundle:Interaction')->getInteraction($eq->getQuestion()->getId());
                $responses = $em->getRepository('UJMExoBundle:Response')
                                ->getExerciseInterResponses($exerciseId, $interaction[0]->getId());
                switch ( $interaction[0]->getType()) {
                    case "InteractionQCM":
                        $scoreMax = $exerciseSer->qcmMaxScore($interaction[0]);
                        $responsesTab = $this->responseStatus($responses, $scoreMax);
                        break;

                    case "InteractionGraphic":
                        $scoreMax = $exerciseSer->graphicMaxScore($interaction[0]);
                        $responsesTab = $this->responseStatus($responses, $scoreMax);
                        break;

                    case "InteractionHole":

                        break;

                    case "InteractionOpen":

                        break;
                }
                $questionsResponsesTab[$eq->getQuestion()->getId()] = $responsesTab;

            }

            //no response
            $papers = $em->getRepository('UJMExoBundle:Paper')->getExerciseAllPapers($exerciseId);
            foreach ($papers as $paper) {
                $questions = $paper->getOrdreQuestion();
                $questions = substr($questions, 0, strlen($questions) - 1);

                $questionsTab = explode(";", $questions);
                foreach ($questionsTab as $question) {
                    $flag = $em->getRepository('UJMExoBundle:Response')->findOneBy(array('interaction' => $question,
                                                                                         'paper' => $paper->getId()));
                    if (!$flag) {
                        $interaction = $em->getRepository('UJMExoBundle:Interaction')->getInteraction($question);
                        $questionsResponsesTab[$interaction[0]->getQuestion()->getId()]['noResponse'] += 1;
                    }
                }
            }

            //creation serie for the graph jqplot
            foreach ($questionsResponsesTab as $responses) {
                $seriesResponsesTab[0] .= (string) $responses['correct'].',';
                $seriesResponsesTab[1] .= (string) $responses['partiallyRight'].',';
                $seriesResponsesTab[2] .= (string) $responses['wrong'].',';
                $seriesResponsesTab[3] .= (string) $responses['noResponse'].',';
            }

            foreach ($seriesResponsesTab as $s) {
                $s = substr($s, 0, strlen($s) - 1);
            }

            return $this->render(
                'UJMExoBundle:Exercise:docimology.html.twig',
                array(
                    'workspace'             => $workspace,
                    'exoID'                 => $exerciseId,
                    'nbPapers'              => $nbPapers,
                    'scoreList'             => $scoreList,
                    'frequencyMarks'        => $frequencyMarks,
                    'maxY'                  => $maxY,
                    'questionsList'         => $questionList,
                    'seriesResponsesTab'    => $seriesResponsesTab
                )
            );
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_open'));
        }
    }

    private function responseStatus($responses, $scoreMax)
    {
        $em = $this->getDoctrine()->getManager();

        $responsesTab = array();
        $responsesTab['correct']        = 0;
        $responsesTab['partiallyRight'] = 0;
        $responsesTab['wrong']          = 0;
        $responsesTab['noResponse']     = 0;

        foreach ($responses as $rep) {
            if ($rep['mark'] == $scoreMax) {
                $responsesTab['correct'] = $rep['nb'];
            } else if ($rep['mark'] == 0) {
                $responsesTab['wrong'] = $rep['nb'];
            } else {
                $responsesTab['partiallyRight'] += $rep['nb'];
            }
        }

        return $responsesTab;
    }

    /**
     * Finds and displays the question selectionned by the User in an assesment
     *
     */
    private function displayQuestion(
        $numQuestionToDisplayed, $interactionToDisplay,
        $typeInterToDisplayed, $dispButtonInterrupt, $workspace
    )
    {
        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();
        $tabOrderInter = $session->get('tabOrderInter');

        switch ($typeInterToDisplayed) {
            case "InteractionQCM":

                $interactionToDisplayed = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:InteractionQCM')
                    ->getInteractionQCM($interactionToDisplay->getId());

                if ($interactionToDisplayed[0]->getShuffle()) {
                    $interactionToDisplayed[0]->shuffleChoices();
                } else {
                    $interactionToDisplayed[0]->sortChoices();
                }

                $responseGiven = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Response')
                    ->getAlreadyResponded($session->get('paper'), $interactionToDisplay->getId());

                if (count($responseGiven) > 0) {
                    $responseGiven = $responseGiven[0]->getResponse();
                } else {
                    $responseGiven = '';
                }

                break;

            case "InteractionGraphic":

                $interactionToDisplayed = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:InteractionGraphic')
                    ->getInteractionGraphic($interactionToDisplay->getId());

                $coords = $em->getRepository('UJMExoBundle:Coords')
                    ->findBy(array('interactionGraphic' => $interactionToDisplayed[0]->getId()));

                $responseGiven = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Response')
                    ->getAlreadyResponded($session->get('paper'), $interactionToDisplay->getId());

                if (count($responseGiven) > 0) {
                    $responseGiven = $responseGiven[0]->getResponse();
                } else {
                    $responseGiven = '';
                }

                $array['listCoords'] = $coords;

                break;

            case "InteractionHole":

                break;

            case "InteractionOpen":

                break;
        }

        $array['workspace']              = $workspace;
        $array['tabOrderInter']          = $tabOrderInter;
        $array['interactionToDisplayed'] = $interactionToDisplayed[0];
        $array['interactionType']        = $typeInterToDisplayed;
        $array['numQ']                   = $numQuestionToDisplayed;
        $array['paper']                  = $session->get('paper');
        $array['response']               = $responseGiven;
        $array['dispButtonInterrupt']    = $dispButtonInterrupt;

        return $this->render(
            'UJMExoBundle:Exercise:paper.html.twig',
            $array
        );
    }

    /**
     * To finish an assessment
     *
     */
    private function finishExercise()
    {
        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();

        $paper = $em->getRepository('UJMExoBundle:Paper')->find($session->get('paper'));
        $paper->setInterupt(0);
        $paper->setEnd(new \Datetime());
        $em->persist($paper);
        $em->flush();

        return $this->forward('UJMExoBundle:Paper:show', array('id' => $paper->getId()));
    }

    /**
     * To interupt an assessment
     *
     */
    private function interuptExercise()
    {
        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();

        $paper = $em->getRepository('UJMExoBundle:Paper')->find($session->get('paper'));
        $paper->setInterupt(1);
        $em->persist($paper);
        $em->flush();

        return $this->redirect($this->generateUrl('ujm_exercise_open', array('exerciseId' => $paper->getExercise()->getId())));
    }

    /**
     * To control the subscription
     *
     */
    private function isExerciseAdmin($exoID)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $subscription = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Subscription')
            ->getControlExerciseEnroll($user->getId(), $exoID);

        if (count($subscription) > 0) {
            return $subscription[0]->getAdmin();
        } else {
            return 0;
        }

    }

    /**
     * The user must be registered and (the dates must be good or the user must to be admin for the exercise)
     *
     */
    private function controlDate($exoAdmin, $exercise)
    {
        if (
            ((($exercise->getStartDate()->format('Y-m-d H:i:s') <= date('Y-m-d H:i:s'))
            && (($exercise->getUseDateEnd() == 0)
            || ($exercise->getEndDate()->format('Y-m-d H:i:s') >= date('Y-m-d H:i:s'))))
            || ($exoAdmin == 1))
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * To control the max attemps
     *
     */
    private function controlMaxAttemps($exercise, $user, $exoAdmin)
    {
        if (($exoAdmin != 1) && ($exercise->getMaxAttempts() > 0)
            && ($exercise->getMaxAttempts() <= $this->container->get('ujm.exercise_services')
                ->getNbPaper($user->getId(), $exercise->getId()))
        ) {
            return false;
        } else {
            return true;
        }
    }

    private function checkAccess($exo)
    {
        $collection = new ResourceCollection(array($exo));

        if (!$this->get('security.context')->isGranted('OPEN', $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}