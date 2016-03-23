<?php

namespace UJM\ExoBundle\Controller;

use Buzz\Message\Request;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Form\ExerciseType;
use UJM\ExoBundle\Form\ExerciseHandler;
use UJM\ExoBundle\Entity\Exercise;

class ExerciseController extends Controller
{
    /**
     * Opens an exercise.
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
        $nbPapers    = $em->getRepository('UJMExoBundle:Paper')->countPapers($exerciseId);

        //$exerciseJson = '{"id":1,"meta":{"authors":["Axel Penin"],"type":"3","created":"2016-03-10 10:57:05","title":"Full Exercise","description":null,"pick":0,"random":false,"maxAttempts":0,"dispButtonInterrupt":true,"markMode":"1","correctionMode":"1","correctionDate":"2016-03-10 10:57:05"},"steps":[{"id":"(unknown)","items":[{"id":1,"type":"application\/x.choice+json","title":"Quelle est la couleur du cheval blanc d\'Henry IV ?","description":null,"invite":"<p>Quelle est la couleur du cheval blanc d\'Henry IV ?<\/p>","random":false,"multiple":false,"choices":[{"id":"1","type":"text\/html","data":"Blanc"},{"id":"2","type":"text\/html","data":"Bleu"},{"id":"3","type":"text\/html","data":"Vous pouvez r\u00e9p\u00e9ter la question ?"},{"id":"4","type":"text\/html","data":"Ne se prononce pas"}]}]},{"id":"(unknown)","items":[{"id":2,"type":"application\/x.cloze+json","title":"Compl\u00e9tez les paroles du 1er couplet de la contin","description":null,"invite":"<p>Compl&eacute;tez les paroles du 1er couplet de la contine \"Au clair de la lune\"<\/p>","text":"<p>Au clair de la <input id=\"1\" class=\"blank\" autocomplete=\"off\" name=\"blank_1\" size=\"15\" type=\"text\" value=\"\" \/>, <br \/> Mon ami <input id=\"2\" class=\"blank\" autocomplete=\"off\" name=\"blank_2\" size=\"15\" type=\"text\" value=\"\" \/>, <br \/> Pr&ecirc;te-moi ta plume <br \/> Pour <input id=\"4\" class=\"blank\" autocomplete=\"off\" name=\"blank_4\" size=\"15\" type=\"text\" value=\"\" \/> un mot. <br \/> Ma <input id=\"3\" class=\"blank\" autocomplete=\"off\" name=\"blank_3\" size=\"15\" type=\"text\" value=\"\" \/> est morte, <br \/> Je n&acute;ai plus de <input id=\"5\" class=\"blank\" autocomplete=\"off\" name=\"blank_5\" size=\"15\" type=\"text\" value=\"\" \/>, <br \/> Ouvre-moi ta porte, <br \/> Pour l&acute;amour de Dieu.<\/p>","holes":[{"id":"1","type":"text\/html","selector":false,"position":"1","wordResponses":[{"id":"1","response":"lune","score":1,"feedback":null}]},{"id":"2","type":"text\/html","selector":false,"position":"2","wordResponses":[{"id":"2","response":"Pierrot","score":1,"feedback":null}]},{"id":"3","type":"text\/html","selector":false,"position":"3","wordResponses":[{"id":"3","response":"chandelle","score":1,"feedback":null}]},{"id":"4","type":"text\/html","selector":false,"position":"4","wordResponses":[{"id":"4","response":"\u00e9crire","score":1,"feedback":null}]},{"id":"5","type":"text\/html","selector":false,"position":"5","wordResponses":[{"id":"5","response":"feu","score":2,"feedback":null}]}]}]},{"id":"(unknown)","items":[{"id":4,"type":"application\/x.short+json","title":"Expliquez le sens de la vie.","description":null,"invite":"<p>Expliquez le sens de la vie.<\/p>","scoreMaxLongResp":5,"typeOpen":"long"}]},{"id":"(unknown)","items":[{"id":5,"type":"application\/x.match+json","title":"Associez chaque groupe au style de musique qu\'ils ","description":null,"invite":"<p>Associez chaque groupe au style de musique qu\'ils composent.<\/p>","random":true,"toBind":true,"firstSet":[{"id":"3","type":"text\/plain","data":"Gorgoroth"},{"id":"4","type":"text\/plain","data":"Hiss from the moat"},{"id":"2","type":"text\/plain","data":"Kampfar"},{"id":"1","type":"text\/plain","data":"Behemoth"}],"secondSet":[{"id":"3","type":"text\/plain","data":"Pagan"},{"id":"4","type":"text\/plain","data":"Death Metal"},{"id":"2","type":"text\/plain","data":"Blackened Death"},{"id":"1","type":"text\/plain","data":"Black Metal"}]}]},{"id":"(unknown)","items":[{"id":6,"type":"application\/x.graphic+json","title":"Retrouvez J\u00e9sus dans l\'image","description":null,"invite":"<p>Retrouvez J&eacute;sus dans l\'image<\/p>","width":567,"height":353,"document":{"id":1,"label":"La C\u00e8ne","url":".\/uploads\/ujmexo\/users_documents\/Elorfin\/images\/image038.jpg"},"coords":[{"id":"1"}]}]}]}';

        // Display the Summary of the Exercise
        return $this->render('UJMExoBundle:Exercise:open.html.twig', [
            // Used to build the Claroline Breadcrumbs
            '_resource'        => $exercise,
            'workspace'        => $exercise->getResourceNode()->getWorkspace(),

            'nbQuestion'       => $nbQuestions['nbq'],
            'nbUserPaper'      => $nbUserPaper,
            'nbPapers'         => $nbPapers,

            // Angular JS data
            'exercise'         => $this->get('ujm.exo.exercise_manager')->exportExercise($exercise, false),
            'editEnabled'      => $isExoAdmin,
            'composeEnabled'   => $isAllowedToCompose,
        ]);
    }

    /**
     * Update the properties of an Exercise
     *
     * @EXT\Route(
     *     "/{id}/update",
     *     name="ujm_exercise_update_meta",
     *     options={"expose"=true}
     * )
     * @EXT\Method("PUT")
     *
     * @param Exercise $exercise
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
     * @param Exercise $exercise
     * @param int  $pageGoNow    page going for the pagination
     * @param int  $maxPage      number max questions per page
     * @param int  $nbItem       number of question
     * @param bool $displayAll   to use pagination or not
     * @param int  $idExo        id exercise selected in the filter, -1 if not selection
     * @param bool $QuestionsExo if filter by exercise is used
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
        $exoAdmin = $services->isExerciseAdmin($exercise);

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

        if ($exoAdmin === true) {   
            if ($QuestionsExo == true) {

                $listQExo= $questionSer->getListQuestionExo($idExo,$user,$exercise);
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

                $max=$paginationSer->getMaxByDisplayAll($shared,$displayAll,$userQuestions);
                $sharedWithMe = $questionSer->getQuestionShare($shared);
                $doublePagination = $paginationSer->doublePagination($userQuestions, $sharedWithMe, $max, $pagerMy, $pagerShared);

                $interactionsPager = $doublePagination[0];
                $pagerfantaMy = $doublePagination[1];

                $sharedWithMePager = $doublePagination[2];
                $pagerfantaShared = $doublePagination[3];

                $pageGoNow=$paginationSer->getPageGoNow($nbItem,$maxPage,$pageToGo,$pageGoNow);
            }

            $listExo = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:Exercise')
                        ->getExerciseAdmin($user->getId());

            if ($QuestionsExo == false) {
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
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_open', ['id' => $exercise->getId()]));
        }
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
            $exo =$em->getRepository('UJMExoBundle:Exercise')->find($exoID) ;
            $pageGoNow = $request->request->get('pageGoNow');
            $qid = $request->request->get('qid');

            $result=$em->getRepository('UJMExoBundle:StepQuestion')->getMaxOrder($exo);

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
                    $this->container->get('ujm.exo_exercise')->createStepForOneQuestion($exo,$question,$order);
                    ++$maxOrdre;
                }
            }
//            $em->flush();
            $url = (string) $this->generateUrl('ujm_exercise_open', [ 'id' => $exoID ]) . '#/steps';

            return new \Symfony\Component\HttpFoundation\Response($url);
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_import_question', array('exoID' => $exoID)));
        }
    }

    /**
     * Add a Step to the Exercise
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
            'id'    => $step->getId(),
            'items' => [],
        ]);
    }

    /**
     * Delete a Step from the Exercise
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
     * @param Step $step
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
            ->findStepByExoQuestion($exercise,$question);
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
            $this->generateUrl('ujm_exercise_open', [ 'id' => $exercise->getId() ]) . '#/steps'
        );
    }
    /**
     * To display the docimology's histogramms.
     *
     * @EXT\Route("/docimology/{id}/{nbPapers}", name="ujm_exercise_docimology", options={"expose"=true})
     * @ParamConverter("Exercise", class="UJMExoBundle:Exercise")
     *
     * @param int $nbPapers   number of papers to this exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function docimologyAction(Exercise $exercise, $nbPapers)
    {
        $this->assertHasPermission('OPEN', $exercise);

        $docimoServ = $this->container->get('ujm.exo_docimology');
        $em = $this->getDoctrine()->getManager();
        
        $eqs = $em->getRepository('UJMExoBundle:StepQuestion')->findExoByOrder($exercise);
        
        $papers = $em->getRepository('UJMExoBundle:Paper')->getExerciseAllPapers($exercise->getId());

        if ($this->container->get('ujm.exo_exercise')->isExerciseAdmin($exercise)) {
            $workspace = $exercise->getResourceNode()->getWorkspace();

            $parameters['nbPapers'] = $nbPapers;
            $parameters['workspace'] = $workspace;
            $parameters['exoID'] = $exercise->getId();
            $parameters['_resource'] = $exercise;

            if ($nbPapers >= 12) {
                $histoMark = $docimoServ->histoMark($exercise->getId());
                $histoSuccess = $docimoServ->histoSuccess($exercise->getId(), $eqs, $papers);

                if ($exercise->getNbQuestion() == 0) {
                    $histoDiscrimination = $docimoServ->histoDiscrimination($exercise->getId(), $eqs, $papers);
                } else {
                    $histoDiscrimination['coeffQ'] = 'none';
                }

                $histoMeasureDifficulty = $docimoServ->histoMeasureOfDifficulty($exercise->getId(), $eqs);

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
