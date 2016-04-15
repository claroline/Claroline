<?php

namespace UJM\ExoBundle\Controller;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Entity\Exercise;

class ExerciseController extends Controller
{
    /**
     * Opens an exercise.
     *
     * @param Exercise $exercise
     *
     * @EXT\Route(
     *     "/{id}",
     *     name="ujm_exercise_open",
     *     requirements={"id"="\d+"},
     *     options={"expose"=true}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function openAction(Exercise $exercise)
    {
        $this->assertHasPermission('OPEN', $exercise);

        $em = $this->getDoctrine()->getManager();
        $exerciseSer = $this->container->get('ujm.exo_exercise');

        $userId = $exerciseSer->getUserId();
        $exerciseId = $exercise->getId();
        $isExoAdmin = $exerciseSer->isExerciseAdmin($exercise);
        $isAllowedToCompose = $exerciseSer->controlMaxAttemps($exercise, $userId, $isExoAdmin);

        if ($userId !== 'anonymous') {
            $nbUserPaper = $exerciseSer->getNbPaper($userId, $exerciseId);
        } else {
            $nbUserPaper = 0;
        }

        $nbQuestions = $em->getRepository('UJMExoBundle:StepQuestion')->getCountQuestion($exercise);
        $nbPapers = $em->getRepository('UJMExoBundle:Paper')->countPapers($exerciseId);

        // Display the Summary of the Exercise
        return $this->render('UJMExoBundle:Exercise:open.html.twig', [
            // Used to build the Claroline Breadcrumbs
            '_resource' => $exercise,
            'workspace' => $exercise->getResourceNode()->getWorkspace(),

            'nbQuestion' => $nbQuestions['nbq'],
            'nbUserPaper' => $nbUserPaper,
            'nbPapers' => $nbPapers,

            // Angular JS data
            'exercise' => $this->get('ujm.exo.exercise_manager')->exportExercise($exercise, false),
            'editEnabled' => $isExoAdmin,
            'composeEnabled' => $isAllowedToCompose,
            'duration' => $exercise->getDuration(),
        ]);
    }

    /**
     * Update the properties of an Exercise.
     *
     * @EXT\Route(
     *     "/{id}/update",
     *     name="ujm_exercise_update_meta",
     *     options={"expose"=true}
     * )
     * @EXT\Method("PUT")
     *
     * @param Exercise $exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateMetadataAction(Exercise $exercise)
    {
        // Get Exercise data from the Request
        $dataRaw = $this->get('request')->getContent();
        if (!empty($dataRaw)) {
            $this->get('ujm.exo.exercise_manager')->updateMetadata($exercise, json_decode($dataRaw));
        }

        return new JsonResponse($this->get('ujm.exo.exercise_manager')->exportExercise($exercise, false));
    }

    /**
     * Publishes an exercise.
     *
     * @EXT\Route(
     *     "/{id}/publish",
     *     name="ujm_exercise_publish",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * @param Exercise $exercise
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function publishAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $this->get('ujm.exo.exercise_manager')->publish($exercise);

        return new JsonResponse($this->get('ujm.exo.exercise_manager')->exportExercise($exercise, false));
    }

    /**
     * Unpublishes an exercise.
     *
     * @EXT\Route(
     *     "/{id}/unpublish",
     *     name="ujm_exercise_unpublish",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * @param Exercise $exercise
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unpublishAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $this->get('ujm.exo.exercise_manager')->unpublish($exercise);

        return new JsonResponse($this->get('ujm.exo.exercise_manager')->exportExercise($exercise, false));
    }

    /**
     * Deletes all the papers associated with an exercise.
     *
     * @EXT\Route(
     *     "/{id}/papers",
     *     name="ujm_exercise_delete_papers",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     *
     * @param Exercise $exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deletePapersAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $this->get('ujm.exo.exercise_manager')->deletePapers($exercise);

        return new JsonResponse([]);
    }

    /**
     * To import in this Exercise a Question of the User's bank.
     *
     * @EXT\Route(
     *     "/{id}/import/{pageGoNow}/{maxPage}/{nbItem}/{displayAll}/{idExo}/{QuestionsExo}",
     *     name="ujm_exercise_import_question",
     *     defaults={"pageGoNow"= 1, "maxPage"= 10, "nbItem"= 1, "displayAll"= 0, "idExo"= -1, "QuestionsExo"= "false"},
     *     options={"expose"=true}
     * )
     *
     * @ParamConverter("Exercise", class="UJMExoBundle:Exercise")
     *
     * @param Exercise $exercise
     * @param int      $pageGoNow    page going for the pagination
     * @param int      $maxPage      number max questions per page
     * @param int      $nbItem       number of question
     * @param bool     $displayAll   to use pagination or not
     * @param int      $idExo        id exercise selected in the filter, -1 if not selection
     * @param bool     $QuestionsExo if filter by exercise is used
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importQuestionAction(Exercise $exercise, $pageGoNow, $maxPage, $nbItem, $displayAll, $idExo = -1, $QuestionsExo = false)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        if ($QuestionsExo == '') {
            $QuestionsExo = false;
        }

        $vars = array();
        $sharedWithMe = array();
        $shareRight = array();
        $questionWithResponse = array();
        $alreadyShared = array();

        $em = $this->getDoctrine()->getManager();

        $workspace = $exercise->getResourceNode()->getWorkspace();

        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $services = $this->container->get('ujm.exo_exercise');
        $questionSer = $this->container->get('ujm.exo_question');
        $paginationSer = $this->container->get('ujm.exo_pagination');

        // To paginate the result :
        $request = $this->get('request'); // Get the request which contains the following parameters :
        $page = $request->query->get('page', 1); // Get the choosen page (default 1)
        $click = $request->query->get('click', 'my'); // Get which array to change page (default 'my question')
        $pagerMy = $request->query->get('pagerMy', 1); // Get the page of the array my question (default 1)
        $pagerShared = $request->query->get('pagerShared', 1); // Get the pager of the array my shared question (default 1)
        $pageToGo = $request->query->get('pageGoNow'); // Page to go for the list of the questions of the exercise

        // If change page of my questions array
        if ($click == 'my') {
            // The choosen new page is for my questions array
            $pagerMy = $page;
        // Else if change page of my shared questions array
        } elseif ($click == 'shared') {
            // The choosen new page is for my shared questions array
            $pagerShared = $page;
        }

        if ($QuestionsExo == 'true') {
            $listQExo = $questionSer->getListQuestionExo($idExo, $user, $exercise);
            $allActions = $questionSer->getActionsAllQuestions($listQExo, $user->getId());

            $actionQ = $allActions[0];
            $questionWithResponse = $allActions[1];
            $alreadyShared = $allActions[2];
            $sharedWithMe = $allActions[3];
            $shareRight = $allActions[4];
        } else {
            $userQuestions = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Question')
                ->findByUserNotInExercise($user, $exercise);

            $shared = $em->getRepository('UJMExoBundle:Share')
                    ->getUserInteractionSharedImport($exercise->getId(), $user->getId(), $em);

            $max = $paginationSer->getMaxByDisplayAll($shared, $displayAll, $userQuestions);
            $sharedWithMe = $questionSer->getQuestionShare($shared);
            $doublePagination = $paginationSer->doublePagination($userQuestions, $sharedWithMe, $max, $pagerMy, $pagerShared);

            $interactionsPager = $doublePagination[0];
            $pagerfantaMy = $doublePagination[1];

            $sharedWithMePager = $doublePagination[2];
            $pagerfantaShared = $doublePagination[3];

            $pageGoNow = $paginationSer->getPageGoNow($nbItem, $maxPage, $pageToGo, $pageGoNow);
        }

        $listExo = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Exercise')
                    ->getExerciseAdmin($user->getId());

        if ($QuestionsExo == 'false') {
            $vars['pagerMy'] = $pagerfantaMy;
            $vars['pagerShared'] = $pagerfantaShared;
            $vars['interactions'] = $interactionsPager;
            $vars['sharedWithMe'] = $sharedWithMePager;
            $vars['pageToGo'] = $pageGoNow;
        } else {
            $vars['interactions'] = $listQExo;
            $vars['actionQ'] = $actionQ;
            $vars['pageToGo'] = 1;
        }

        $vars['questionWithResponse'] = $questionWithResponse;
        $vars['alreadyShared'] = $alreadyShared;
        $vars['shareRight'] = $shareRight;
        $vars['displayAll'] = $displayAll;
        $vars['listExo'] = $listExo;
        $vars['exoID'] = $exercise->getId();
        $vars['QuestionsExo'] = $QuestionsExo;
        $vars['workspace'] = $workspace;
        $vars['_resource'] = $exercise;
        $vars['idExo'] = $idExo;

        return $this->render('UJMExoBundle:Question:import.html.twig', $vars);
    }

    /**
     * To record the question's import.
     *
     * @EXT\Route("/import", name="ujm_exercise_validate_import")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importValidateAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            $exoID = $request->request->get('exoID');
            $exo = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
            $pageGoNow = $request->request->get('pageGoNow');
            $qid = $request->request->get('qid');

            $result = $em->getRepository('UJMExoBundle:StepQuestion')->getMaxOrder($exo);

            $maxOrdre = (int) $result[0][1] + 1;

            foreach ($qid as $q) {
                $question = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Question')
                    ->find($q);

                if (count($question) > 0) {
                    $question = $em->getRepository('UJMExoBundle:Question')->find($q);
                    $order = (int) $maxOrdre;
                    //Create a step for one question in the exercise
                    $this->container->get('ujm.exo_exercise')->createStepForOneQuestion($exo, $question, $order);
                    ++$maxOrdre;
                }
            }
//            $em->flush();
            $url = (string) $this->generateUrl('ujm_exercise_open', ['id' => $exoID]).'#/steps';

            return new \Symfony\Component\HttpFoundation\Response($url);
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_import_question', array('exoID' => $exoID)));
        }
    }

    /**
     * Add a Step to the Exercise.
     *
     * @EXT\Route(
     *     "/{id}/step",
     *     name="ujm_exercise_step_add",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @ParamConverter("Exercise", class="UJMExoBundle:Exercise")
     *
     * @param Exercise $exercise
     *
     * @return JsonResponse
     */
    public function addStepAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $step = new Step();
        $step->setText(' ');
        $step->setNbQuestion('0');
        $step->setDuration(0);
        $step->setMaxAttempts(0);
        $step->setOrder($exercise->getSteps()->count() + 1);

        // Link the Step to the Exercise
        $exercise->addStep($step);

        $this->getDoctrine()->getManager()->persist($step);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'id' => $step->getId(),
            'items' => [],
        ]);
    }

    /**
     * Delete a Step from the Exercise.
     *
     * @EXT\Route(
     *     "/{id}/step/{sid}",
     *     name="ujm_exercise_step_delete",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @ParamConverter("Exercise", class="UJMExoBundle:Exercise")
     *
     * @param Exercise $exercise
     * @param Step     $step
     *
     * @return JsonResponse
     */
    public function deleteStepAction(Exercise $exercise, $sid)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $em = $this->getDoctrine()->getManager();
        $step = $em->getRepository('UJMExoBundle:Step')->find($sid);
        if (!empty($step)) {
            $exercise->removeStep($step);

            $em->remove($step);
            $em->flush();
        }

        // Return updated list of steps
        return new JsonResponse($this->get('ujm.exo.exercise_manager')->exportSteps($exercise, false));
    }

    /**
     * Delete the Question of the exercise.
     *
     * @EXT\Route(
     *     "/{id}/question/{qid}",
     *     name="ujm_exercise_question_delete",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @ParamConverter("Exercise", class="UJMExoBundle:Exercise")
     *
     * @param Exercise $exercise
     * @param int      $qid      id of question to delete
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteQuestionAction(Exercise $exercise, $qid)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $em = $this->getDoctrine()->getManager();
        $question = $em->getRepository('UJMExoBundle:Question')->find($qid);
        //Temporary : Waiting step manager
        $sq = $em->getRepository('UJMExoBundle:StepQuestion')
            ->findStepByExoQuestion($exercise, $question);
        $em->remove($sq);
        $em->flush();

        return new JsonResponse($this->get('ujm.exo.exercise_manager')->exportExercise($exercise, false));
    }

    /**
     * To change the order of the questions into an exercise.
     *
     * @EXT\Route(
     *     "/{id}/question/update_order",
     *     name="ujm_exercise_question_order",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * @param Exercise $exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeQuestionOrderAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $em = $this->getDoctrine()->getManager();
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            $order = $request->request->get('order');
            $currentPage = $request->request->get('currentPage');
            $questionMaxPerPage = $request->request->get('questionMaxPerPage');

            if ($order && $currentPage && $questionMaxPerPage) {
                $length = count($order);

                $em = $this->getDoctrine()->getManager();
                $exoQuestions = $em->getRepository('UJMExoBundle:StepQuestion')->findExoByOrder($exercise);

                foreach ($exoQuestions as $exoQuestion) {
                    for ($i = 0; $i < $length; ++$i) {
                        if ($exoQuestion->getQuestion()->getId() == $order[$i]) {
                            $newOrder = $i + 1 + (((int) $currentPage - 1) * (int) $questionMaxPerPage);
                            $exoQuestion->setOrdre($newOrder);
                        }
                    }
                }

                $em->persist($exoQuestion);
                $em->flush();
            }
        }

        return $this->redirect(
            $this->generateUrl('ujm_exercise_open', ['id' => $exercise->getId()]).'#/steps'
        );
    }
    /**
     * To display the docimology's histogramms.
     *
     * @EXT\Route("/docimology/{id}/{nbPapers}", name="ujm_exercise_docimology", options={"expose"=true})
     * @ParamConverter("Exercise", class="UJMExoBundle:Exercise")
     *
     * @param int $nbPapers number of papers to this exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function docimologyAction(Exercise $exercise, $nbPapers)
    {
        $this->assertHasPermission('OPEN', $exercise);

        $docimoServ = $this->container->get('ujm.exo_docimology');
        $em = $this->getDoctrine()->getManager();

        $sqs = $em->getRepository('UJMExoBundle:StepQuestion')->findExoByOrder($exercise);

        $papers = $em->getRepository('UJMExoBundle:Paper')->getExerciseAllPapers($exercise->getId());

        if ($this->container->get('ujm.exo_exercise')->isExerciseAdmin($exercise)) {
            $workspace = $exercise->getResourceNode()->getWorkspace();

            $parameters['nbPapers'] = $nbPapers;
            $parameters['workspace'] = $workspace;
            $parameters['exoID'] = $exercise->getId();
            $parameters['_resource'] = $exercise;

            if ($nbPapers >= 12) {
                $histoMark = $docimoServ->histoMark($exercise->getId());
                $histoSuccess = $docimoServ->histoSuccess($exercise->getId(), $sqs, $papers);

                if ($exercise->getNbQuestion() == 0) {
                    $histoDiscrimination = $docimoServ->histoDiscrimination($exercise->getId(), $sqs, $papers);
                } else {
                    $histoDiscrimination['coeffQ'] = 'none';
                }

                $histoMeasureDifficulty = $docimoServ->histoMeasureOfDifficulty($exercise->getId(), $sqs);

                $parameters['scoreList'] = $histoMark['scoreList'];
                $parameters['frequencyMarks'] = $histoMark['frequencyMarks'];
                $parameters['maxY'] = $histoMark['maxY'];
                $parameters['questionsList'] = $histoSuccess['questionsList'];
                $parameters['seriesResponsesTab'] = $histoSuccess['seriesResponsesTab'];
                $parameters['maxY2'] = $histoSuccess['maxY'];
                $parameters['coeffQ'] = $histoDiscrimination['coeffQ'];
                $parameters['MeasureDifficulty'] = $histoMeasureDifficulty;
            }

            return $this->render('UJMExoBundle:Exercise:docimology.html.twig', $parameters);
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_open', ['id' => $exercise->getId()]));
        }
    }

    private function assertHasPermission($permission, Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if (!$this->get('security.authorization_checker')->isGranted($permission, $collection)) {
            throw new AccessDeniedHttpException();
        }
    }
}
