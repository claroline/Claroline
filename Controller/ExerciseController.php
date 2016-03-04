<?php

namespace UJM\ExoBundle\Controller;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\ExoBundle\Form\ExerciseType;
use UJM\ExoBundle\Form\ExerciseHandler;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Response;

class ExerciseController extends Controller
{
    /**
     * Displays a form to edit an existing Exercise entity.
     *
     * @EXT\Route("/{id}/edit", name="ujm_exercise_edit")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Exercise $exercise)
    {
        $this->checkAccess($exercise);

        $workspace = $exercise->getResourceNode()->getWorkspace();

        $exoAdmin = $this->container->get('ujm.exo_exercise')->isExerciseAdmin($exercise);

        if ($exoAdmin === true) {
            if (!$exercise) {
                throw $this->createNotFoundException('Unable to find Exercise entity.');
            }

            $editForm = $this->createForm(new ExerciseType(), $exercise);

            return $this->render(
                'UJMExoBundle:Exercise:edit.html.twig',
                array(
                    'workspace' => $workspace,
                    'entity' => $exercise,
                    'edit_form' => $editForm->createView(),
                    '_resource' => $exercise,
                )
            );
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_open', ['id' => $exercise->getid()]));
        }
    }

    /**
     * Edits an existing Exercise entity.
     *
     *
     * @EXT\Route("/{id}/update", name="ujm_exercise_update")
     * @EXT\Method("POST")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Exercise $exercise)
    {
        if (!$exercise) {
            throw $this->createNotFoundException('Unable to find Exercise entity.');
        }

        $editForm = $this->createForm(new ExerciseType(), $exercise);

        $formHandler = new ExerciseHandler(
            $editForm, $this->get('request'), $this->getDoctrine()->getManager(),
            $this->container->get('security.token_storage')->getToken()->getUser(), 'update'
        );

        if ($formHandler->process()) {
            return $this->redirect(
                $this->generateUrl(
                    'claro_resource_open', array(
                    'resourceType' => $exercise->getResourceNode()->getResourceType()->getName(),
                    'node' => $exercise->getResourceNode()->getId(), )
                )
            );
        }

        return $this->render(
            'UJMExoBundle:Exercise:edit.html.twig',
            array(
                'entity' => $exercise,
                'edit_form' => $editForm->createView(),
            )
        );
    }

    /**
     * Displays an exercise.
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
        $this->checkAccess($exercise);

        $em = $this->getDoctrine()->getManager();
        $exerciseSer = $this->container->get('ujm.exo_exercise');

        $userId = $exerciseSer->getUserId();
        $exerciseId = $exercise->getId();
        $isExoAdmin = $exerciseSer->isExerciseAdmin($exercise);
        $isAllowedToOpen = $exerciseSer->allowToOpen($exercise);
        $isAllowedToCompose = $isExoAdmin
            || $isAllowedToOpen
            && $exerciseSer->controlMaxAttemps($exercise, $userId, $isExoAdmin);

        if ($isAllowedToOpen && $userId !== 'anonymous') {
            $nbUserPaper = $exerciseSer->getNbPaper($userId, $exerciseId);
        } else {
            $nbUserPaper = 0;
        }

        $nbQuestions = $em->getRepository('UJMExoBundle:StepQuestion')->getCountQuestion($exercise);
        $nbPapers = $em->getRepository('UJMExoBundle:Paper')->countPapers($exerciseId);

        return $this->render(
            'UJMExoBundle:Exercise:show.html.twig',
            [
                'exercise' => $exercise,
                'allowedToCompose' => $isAllowedToCompose,
                'nbQuestion' => $nbQuestions['nbq'],
                'nbUserPaper' => $nbUserPaper,
                'nbPapers' => $nbPapers,
            ]
        );
    }

    /**
     * Publishes an exercise.
     *
     * @EXT\Route("/{id}/publish", name="ujm_exercise_publish")
     * @EXT\Method("POST")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function publishAction(Exercise $exercise)
    {
        $this->checkIsAllowed('ADMINISTRATE', $exercise);
        $this->get('ujm.exo.exercise_manager')->publish($exercise);

        return $this->redirect($this->generateUrl('ujm_exercise_open', ['id' => $exercise->getId()]));
    }

    /**
     * Unpublishes an exercise.
     *
     * @EXT\Route("/{id}/unpublish", name="ujm_exercise_unpublish")
     * @EXT\Method("POST")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unpublishAction(Exercise $exercise)
    {
        $this->checkIsAllowed('ADMINISTRATE', $exercise);
        $this->get('ujm.exo.exercise_manager')->unpublish($exercise);

        return $this->redirect($this->generateUrl('ujm_exercise_open', ['id' => $exercise->getId()]));
    }

    /**
     * Deletes all the papers associated with an exercise.
     *
     * @EXT\Route("/{id}/papers/delete", name="ujm_exercise_delete_papers")
     * @EXT\Method("POST")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deletePapersAction(Exercise $exercise)
    {
        $this->checkIsAllowed('ADMINISTRATE', $exercise);
        $this->get('ujm.exo.exercise_manager')->deletePapers($exercise);

        return $this->forward('UJMExoBundle:Paper:index', [
            'exoID' => $exercise->getId(),
            'page' => 1,
            'all' => 0,
        ]);
    }

    /**
     * Finds and displays a Question entity to this Exercise.
     *
     * @EXT\Route("/{id}/questions/{pageNow}/{displayAll}/{categoryToFind}/{titleToFind}",
     *              name="ujm_exercise_questions",
     *              defaults={"pageNow" = 0,"categoryToFind"= "z", "titleToFind"= "z", "displayAll"= 0 },
     *              requirements={"categoryToFind"=".+","titleToFind"= ".+"})     *
     * @ParamConverter("Exercise", class="UJMExoBundle:Exercise")
     *
     * @param int    $pageNow        actual page for the pagination
     * @param string $categoryToFind used for pagination (for example after creating a question, go back to page contaning this question)
     * @param string $titleToFind    used for pagination (for example after creating a question, go back to page contaning this question)
     * @param bool   $displayAll     to use pagination or not
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showQuestionsAction(Exercise $exercise, $pageNow, $categoryToFind, $titleToFind, $displayAll)
    {
        $user = $this->container->get('security.token_storage')
                                ->getToken()->getUser();
        $allowEdit = array();
        $em = $this->getDoctrine()->getManager();
        $this->checkAccess($exercise);

        $workspace = $exercise->getResourceNode()->getWorkspace();

        $exoAdmin = $this->container->get('ujm.exo_exercise')->isExerciseAdmin($exercise);
        $paginationSer = $this->container->get('ujm.exo_pagination');

        $max = 10; // Max Per Page
        $request = $this->get('request');
        $page = $request->query->get('page', 1);

        if ($exoAdmin === true) {
            $questions = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Question')
                ->findByExercise($exercise);
        
            if ($displayAll == 1) {
                $max = count($questions);
            }

            $questionWithResponse = array();

            foreach ($questions as $question) {
                $response = $em->getRepository('UJMExoBundle:Response')
                    ->findBy(array('question' => $question));
                if (count($response) > 0) {
                    $questionWithResponse[$question->getId()] = 1;
                } else {
                    $questionWithResponse[$question->getId()] = 0;
                }

                $share = $this->container->get('ujm.exo_question')->controlUserSharedQuestion(
                        $question->getId());

                if ($user->getId() == $question->getUser()->getId()) {
                    $allowEdit[$question->getId()] = 1;
                } else if(count($share) > 0) {
                    $allowEdit[$question->getId()] = $share[0]->getAllowToModify();
                } else {
                    $allowEdit[$question->getId()] = 0;
                }
            }

            if ($categoryToFind != '' && $titleToFind != '' && $categoryToFind != 'z' && $titleToFind != 'z') {
                $i = 1;
                $pos = 0;
                $temp = 0;

                foreach ($questions as $question) {
                    if ($question->getCategory() == $categoryToFind) {
                        $temp = $i;
                    }
                    if ($question->getTitle() == $titleToFind && $temp == $i) {
                        $pos = $i;
                        break;
                    }
                    ++$i;
                }

                if ($pos % $max == 0) {
                    $pageNow = $pos / $max;
                } else {
                    $pageNow = ceil($pos / $max);
                }
            }

            $pagination = $paginationSer->paginationWithIf($questions, $max, $page, $pageNow);

            $interactionsPager = $pagination[0];
            $pagerQuestion = $pagination[1];

            // if upload a none qti file
            if ($request->get('qtiError')) {
                return $this->render(
                    'UJMExoBundle:Question:exerciseQuestion.html.twig',
                    array(
                        'workspace' => $workspace,
                        'interactions' => $interactionsPager,
                        'exerciseID' => $exercise->getId(),
                        'questionWithResponse' => $questionWithResponse,
                        'pagerQuestion' => $pagerQuestion,
                        'displayAll' => $displayAll,
                        'allowEdit' => $allowEdit,
                        '_resource' => $exercise,
                        'qtiError' => $request->get('qtiError'),
                    )
                );
            } else {
                return $this->render(
                    'UJMExoBundle:Question:exerciseQuestion.html.twig',
                    array(
                        'workspace' => $workspace,
                        'interactions' => $interactionsPager,
                        'exerciseID' => $exercise->getId(),
                        'questionWithResponse' => $questionWithResponse,
                        'pagerQuestion' => $pagerQuestion,
                        'displayAll' => $displayAll,
                        'allowEdit' => $allowEdit,
                        '_resource' => $exercise,
                    )
                );
            }
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_open', ['id' => $exercise->getId()]));
        }
    }

    /**
     *To import in this Exercise a Question of the User's bank.
     *
     * @EXT\Route("/{id}/import/{pageGoNow}/{maxPage}/{nbItem}/{displayAll}/{idExo}/{QuestionsExo}",
     *              name="ujm_exercise_import_question",
     *              defaults={"pageGoNow"= 1, "maxPage"= 10, "nbItem"= 1, "displayAll"= 0, "idExo"= -1, "QuestionsExo"= "false"})
     *
     * @ParamConverter("Exercise", class="UJMExoBundle:Exercise")
     * @param int  $pageGoNow    page going for the pagination
     * @param int  $maxpage      number max questions per page
     * @param int  $nbItem       number of question
     * @param bool $displayAll   to use pagination or not
     * @param int  $idExo        id exercise selected in the filter, -1 if not selection
     * @param bool $QuestionsExo if filter by exercise is used
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importQuestionAction(Exercise $exercise, $pageGoNow, $maxPage, $nbItem, $displayAll, $idExo = -1, $QuestionsExo = 'false')
    {
        if ($QuestionsExo == '') {
            $QuestionsExo = 'false';
        }

        $vars = array();
        $sharedWithMe = array();
        $shareRight = array();
        $questionWithResponse = array();
        $alreadyShared = array();

        $em = $this->getDoctrine()->getManager();
        $this->checkAccess($exercise);

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
            if ($QuestionsExo == 'true') {

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
                $sharedWithMe=$questionSer->getQuestionShare($shared);
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
            $url = (string) $this->generateUrl('ujm_exercise_questions', array('id' => $exoID, 'pageNow' => $pageGoNow));

            return new \Symfony\Component\HttpFoundation\Response($url);
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_import_question', array('exoID' => $exoID)));
        }
    }

    /**
     * Delete the Question of the exercise.
     *
     * @EXT\Route("/{id}/{qid}/delete/{pageNow}/{maxPage}/{nbItem}/{lastPage}",
     *              name="ujm_exercise_question_delete",
     *              defaults={"pageNow"= 1, "maxPage"= 10, "nbItem"= 1, "lastPage"= 1})
     *
     * @ParamConverter("Exercise", class="UJMExoBundle:Exercise")
     * @param int $qid      id of question to delete
     * @param int $pageNow  actual page for the pagination
     * @param int $maxpage  number max questions per page
     * @param int $nbItem   number of question
     * @param int $lastPage number of last page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteQuestionAction(Exercise $exercise, $qid, $pageNow, $maxPage, $nbItem, $lastPage)
    {
        $em = $this->getDoctrine()->getManager();

        $this->checkAccess($exercise);

        $exoAdmin = $this->container->get('ujm.exo_exercise')->isExerciseAdmin($exercise);

        if ($exoAdmin === true) {
            $em = $this->getDoctrine()->getManager();
            $question = $em->getRepository('UJMExoBundle:Question')->find($qid);
            //Temporary : Waiting step manager
            $sq = $em->getRepository('UJMExoBundle:StepQuestion')
                ->findStepByExoQuestion($exercise,$question);
            $em->remove($sq);
            $em->flush();

             // If delete last item of page, display the previous one
            $rest = $nbItem % $maxPage;

            if ($rest == 1 && $pageNow == $lastPage) {
                $pageNow -= 1;
            }
        }

        return $this->redirect(
            $this->generateUrl(
                'ujm_exercise_questions',
                array(
                    'id' => $exercise->getId(),
                    'pageNow' => $pageNow,
                )
            )
        );
    }

    /**
     * To create a paper in order to take an assessment.
     *
     * @EXT\Route("/{id}/paper", name="ujm_exercise_paper")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exercisePaperAction(Exercise $exercise)
    {
        $exerciseSer = $this->container->get('ujm.exo_exercise');
        $paperSer = $this->container->get('ujm.exo_paper');
        $user = $exerciseSer->getUser();
        $uid = $exerciseSer->getUserId();

        $em = $this->getDoctrine()->getManager();
        if (!$exerciseSer->allowToOpen($exercise)) {
            return $this->redirect($this->generateUrl('ujm_exercise_open', ['id' => $exercise->getId()]));
        }

        $exoAdmin = $exerciseSer->isExerciseAdmin($exercise);
        $this->checkAccess($exercise);

        $workspace = $exercise->getResourceNode()->getWorkspace();

        if ($exoAdmin || $exercise->getResourceNode()->isPublished()) {
            $session = $this->getRequest()->getSession();

            if ($uid != 'anonymous') {
                $dql = 'SELECT max(p.numPaper) FROM UJM\ExoBundle\Entity\Paper p '
                    .'WHERE p.exercise='.$exercise->getId().' AND p.user='.$uid;
                $query = $em->createQuery($dql);
                $maxNumPaper = $query->getSingleResult();

                //Verify if it exists a not finished paper
                $paper = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Paper')
                    ->getPaper($uid, $exercise->getId());
            } else {
                $maxNumPaper[1] = 0;
                $paper = array();
            }

            //if not exist a paper no finished
            if (count($paper) == 0) {
                if ($exerciseSer->controlMaxAttemps($exercise, $uid, $exoAdmin) === false) {
                    return $this->redirect($this->generateUrl('ujm_paper_list', array('exoID' => $exercise->getId())));
                }

                $paper = new Paper();
                $paper->setNumPaper((int) $maxNumPaper[1] + 1);
                $paper->setExercise($exercise);
                if ($uid != 'anonymous') {
                    $paper->setUser($user);
                }
                $paper->setStart(new \Datetime());
                $paper->setArchive(0);
                $paper->setInterupt(1);

                if ($exercise->getNbQuestion() > 0 && $exercise->getKeepSameQuestion()) {
                    $papers = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:Paper')
                        ->getExerciseUserPapers($uid, $exercise->getId());
                    if (count($papers) == 0) {
                        $tab = $paperSer->prepareInteractionsPaper($exercise->getId(), $exercise);
                        $interactions = $tab['interactions'];
                        $orderInter = $tab['orderInter'];
                        $tabOrderInter = $tab['tabOrderInter'];
                    } else {
                        $lastPaper = $papers[count($papers) - 1];
                        $orderInter = $lastPaper->getOrdreQuestion();
                        $tabOrderInter = explode(';', $lastPaper->getOrdreQuestion());
                        unset($tabOrderInter[count($tabOrderInter) - 1]);
                        $interactions[0] = $em->getRepository('UJMExoBundle:Question')->find($tabOrderInter[0]);
                    }
                } else {
                    $tab = $paperSer->prepareInteractionsPaper($exercise->getId(), $exercise);
                    $interactions = $tab['interactions'];
                    $orderInter = $tab['orderInter'];
                    $tabOrderInter = $tab['tabOrderInter'];
                }

                $paper->setOrdreQuestion($orderInter);
                $em->persist($paper);
                $em->flush();
            } else {
                $paper = $paper[0];
                if (!$exercise->getDispButtonInterrupt()) {
                    $paperInt = $paperSer->forceFinishExercise($paper);

                    return $this->forward('UJMExoBundle:Exercise:exercisePaper', array('id' => $paperInt->getExercise()->getId()));
                }
                $tabOrderInter = explode(';', $paper->getOrdreQuestion());
                unset($tabOrderInter[count($tabOrderInter) - 1]);
                $interactions[0] = $em->getRepository('UJMExoBundle:Question')->find($tabOrderInter[0]);
            }

            $session->set('tabOrderInter', $tabOrderInter);
            $session->set('paper', $paper->getId());
            $session->set('exerciseID', $exercise->getId());

            $typeInter = $interactions[0]->getType();

            //To display selectioned question
            $array = $paperSer->displayQuestion(1, $interactions[0], $typeInter,
                    $exercise->getDispButtonInterrupt(),
                    $exercise->getMaxAttempts(),
                    $workspace, $paper, $session);

            return $this->render('UJMExoBundle:Exercise:paper.html.twig', $array);
        } else {
            return $this->redirect($this->generateUrl('ujm_paper_list', array('exoID' => $exercise->getId())));
        }
    }

    /**
     * To navigate in the Questions of the assessment.
     *
     * @EXT\Route("/paper/nav/", name="ujm_exercise_paper_nav")
     * @EXT\Method("POST")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exercisePaperNavAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $paper = $em->getRepository('UJMExoBundle:Paper')->find($session->get('paper'));
        $workspace = $paper->getExercise()->getResourceNode()->getWorkspace();
        $typeInterToRecorded = $request->get('typeInteraction');

        $tabOrderInter = $session->get('tabOrderInter');

        if ($paper->getEnd()) {
            return $this->forward('UJMExoBundle:Paper:show',
                                  array(
                                      'id' => $paper->getId(),
                                      'p' => -1,
                                       )
                                 );
        }

        //To record response
        $paperSer = $this->container->get('ujm.exo_paper');
        $ip = $paperSer->getIP($request);
        $interactionToValidatedID = $request->get('interactionToValidated');
        $response = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Response')
            ->getAlreadyResponded($session->get('paper'), $interactionToValidatedID);

        $interSer = $this->container->get('ujm.exo_'.$typeInterToRecorded);
        $res = $interSer->response($request, $session->get('paper'));

        if (count($response) == 0) {
            //INSERT Response
            $response = new Response();
            $response->setNbTries(1);
            $response->setPaper($paper);
            $response->setQuestion($em->getRepository('UJMExoBundle:Question')->find($interactionToValidatedID));
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
            $paperFinish = $paperSer->finishExercise($session);

            return $this->forward('UJMExoBundle:Paper:show', array('id' => $paperFinish->getId()));
        } elseif ($numQuestionToDisplayed == 'interupt') {
            $paperInt = $paperSer->interuptExercise($session);

            return $this->redirect($this->generateUrl('ujm_exercise_open', ['id' => $paperInt->getExercise()->getId()]));
        } else {
            $interactionToDisplayedID = $tabOrderInter[$numQuestionToDisplayed - 1];
            $interactionToDisplay = $em->getRepository('UJMExoBundle:Question')->find($interactionToDisplayedID);
            $typeInterToDisplayed = $interactionToDisplay->getType();

            $array = $paperSer->displayQuestion(
                $numQuestionToDisplayed, $interactionToDisplay, $typeInterToDisplayed,
                $response->getPaper()->getExercise()->getDispButtonInterrupt(),
                $response->getPaper()->getExercise()->getMaxAttempts(),
                $workspace, $paper, $session
            );

            return $this->render('UJMExoBundle:Exercise:paper.html.twig', $array);
        }
    }

    /**
     * To change the order of the questions into an exercise.
     *
     * @EXT\Route("/ExerciseQuestion/changeOrder", name="ujm_exercise_question_order")
     * @EXT\Method("POST")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeQuestionOrderAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->container->get('request');
        
        if ($request->isXmlHttpRequest()) {
            $exoID = $request->request->get('exoID');
            $exercise=$em->getRepository('UJMExoBundle:Exercise')->find($exoID);
            $order = $request->request->get('order');
            $currentPage = $request->request->get('currentPage');
            $questionMaxPerPage = $request->request->get('questionMaxPerPage');

            if ($exoID && $order && $currentPage && $questionMaxPerPage) {
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
            }
        }

        $em->persist($exoQuestion);
        $em->flush();

        return $this->redirect(
            $this->generateUrl('ujm_exercise_questions', array(
                'id' => $exoID,
                )
            )
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
        $docimoServ = $this->container->get('ujm.exo_docimology');
        $em = $this->getDoctrine()->getManager();
        $this->checkAccess($exercise);
        
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

    /**
     * To check the right to open exo or not.
     *
     * @return exception
     */
    private function checkAccess(Exercise $exercise)
    {
        $collection = new ResourceCollection(array($exercise->getResourceNode()));

        if (!$this->get('security.authorization_checker')->isGranted('OPEN', $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    private function checkIsAllowed($permission, Exercise $exercise)
    {
        $collection = new ResourceCollection(array($exercise->getResourceNode()));

        if (!$this->get('security.authorization_checker')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
