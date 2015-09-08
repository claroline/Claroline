<?php

namespace UJM\ExoBundle\Controller;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\ExoBundle\Form\ExerciseType;
use UJM\ExoBundle\Form\ExerciseHandler;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\ExerciseQuestion;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Response;

/**
 * Exercise controller.
 *
 */
class ExerciseController extends Controller
{

    /**
     * Displays a form to edit an existing Exercise entity.
     *
     * @access public
     *
     * @param integer $id id of exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($id);
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
                    'workspace'   => $workspace,
                    'entity'      => $exercise,
                    'edit_form'   => $editForm->createView(),
                    '_resource'   => $exercise
                )
            );
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_open', array('id' => $id)));
        }
    }

    /**
     * Edits an existing Exercise entity.
     *
     * @access public
     *
     * @param integer $id id of exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
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
            $this->container->get('security.token_storage')->getToken()->getUser(), 'update'
        );

        if ($formHandler->process()) {
            return $this->redirect(
                $this->generateUrl(
                    'ujm_exercise_open', array(
                    'id' => $exercise->getId())
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
     * Displays an exercise.
     *
     * @EXT\Route("/{id}", name="ujm_exercise_open")
     *
     * @param Exercise $exercise
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

        $nbQuestions = $em->getRepository('UJMExoBundle:ExerciseQuestion')->getCountQuestion($exerciseId);
        $nbPapers = $em->getRepository('UJMExoBundle:Paper')->countPapers($exerciseId);

        return $this->render(
            'UJMExoBundle:Exercise:show.html.twig',
            [
                'exercise'          => $exercise,
                'allowedToCompose'  => $isAllowedToCompose,
                'nbQuestion'        => $nbQuestions['nbq'],
                'nbUserPaper'       => $nbUserPaper,
                'nbPapers'          => $nbPapers,
            ]
        );
    }    

    /**
     * Publishes an exercise.
     *
     * @EXT\Route("/{id}/publish", name="ujm_exercise_publish")
     * @EXT\Method("POST")
     *
     * @param Exercise $exercise
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
     * @param Exercise $exercise
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
     * @param Exercise $exercise
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deletePapersAction(Exercise $exercise)
    {
        $this->checkIsAllowed('ADMINISTRATE', $exercise);
        $this->get('ujm.exo.exercise_manager')->deletePapers($exercise);

        return $this->forward('UJMExoBundle:Paper:index', [
            'exoID' => $exercise->getId(),
            'page'  => 1,
            'all'   => 0
        ]);
    }

    /**
     * Finds and displays a Question entity to this Exercise.
     *
     * @access public
     *
     * @param integer $id id of exercise
     * @param integer $pageNow actual page for the pagination
     * @param string $categoryToFind used for pagination (for example after creating a question, go back to page contaning this question)
     * @param string $titleToFind used for pagination (for example after creating a question, go back to page contaning this question)
     * @param boolean $displayAll to use pagination or not
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showQuestionsAction($id, $pageNow, $categoryToFind, $titleToFind, $displayAll)
    {
        $user = $this->container->get('security.token_storage')
                                ->getToken()->getUser();
        $allowEdit = array();
        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($id);
        $this->checkAccess($exercise);

        $workspace = $exercise->getResourceNode()->getWorkspace();

        $exoAdmin = $this->container->get('ujm.exo_exercise')->isExerciseAdmin($exercise);

        $max = 10; // Max Per Page
        $request = $this->get('request');
        $page = $request->query->get('page', 1);

        if ($exoAdmin === true) {
            $interactions = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Interaction')
                ->getExerciseInteraction($em, $id, 0);

            if ($displayAll == 1) {
                $max = count($interactions);
            }

            $questionWithResponse = array();
            foreach ($interactions as $interaction) {
                $response = $em->getRepository('UJMExoBundle:Response')
                    ->findBy(array('interaction' => $interaction->getId()));
                if (count($response) > 0) {
                    $questionWithResponse[$interaction->getId()] = 1;
                } else {
                    $questionWithResponse[$interaction->getId()] = 0;
                }

                $share = $this->container->get('ujm.exo_question')->controlUserSharedQuestion(
                        $interaction->getQuestion()->getId());

                if ($user->getId() == $interaction->getQuestion()->getUser()->getId()) {
                    $allowEdit[$interaction->getId()] = 1;
                } else if(count($share) > 0) {
                    $allowEdit[$interaction->getId()] = $share[0]->getAllowToModify();
                } else {
                    $allowEdit[$interaction->getId()] = 0;
                }

            }

            if ($categoryToFind != '' && $titleToFind != '' && $categoryToFind != 'z' && $titleToFind != 'z') {
                $i = 1 ;
                $pos = 0 ;
                $temp = 0;

                foreach ($interactions as $interaction) {
                    if ($interaction->getQuestion()->getCategory() == $categoryToFind) {
                        $temp = $i;
                    }
                    if ($interaction->getQuestion()->getTitle() == $titleToFind && $temp == $i) {
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

            $pagination = $this->paginationWithIf($interactions, $max, $page, $pageNow);

            $interactionsPager = $pagination[0];
            $pagerQuestion = $pagination[1];

            // if upload a none qti file
            if ( $request->get('qtiError') ) {
                return $this->render(
                    'UJMExoBundle:Question:exerciseQuestion.html.twig',
                    array(
                        'workspace'            => $workspace,
                        'interactions'         => $interactionsPager,
                        'exerciseID'           => $id,
                        'questionWithResponse' => $questionWithResponse,
                        'pagerQuestion'        => $pagerQuestion,
                        'displayAll'           => $displayAll,
                        'allowEdit'            => $allowEdit,
                        '_resource'            => $exercise,
                        'qtiError'              => $request->get('qtiError')
                    )
                );
            } else {
                return $this->render(
                    'UJMExoBundle:Question:exerciseQuestion.html.twig',
                    array(
                        'workspace'            => $workspace,
                        'interactions'         => $interactionsPager,
                        'exerciseID'           => $id,
                        'questionWithResponse' => $questionWithResponse,
                        'pagerQuestion'        => $pagerQuestion,
                        'displayAll'           => $displayAll,
                        'allowEdit'            => $allowEdit,
                        '_resource'            => $exercise
                    )
                );
            }

        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_open', ['id' => $id]));
        }
    }

    /**
    *To import in this Exercise a Question of the User's bank.
    *
    * @access public
    *
    * @param integer $exoID id of exercise
    * @param integer $pageGoNow page going for the pagination
    * @param integer $maxpage number max questions per page
    * @param integer $nbItem number of question
    * @param boolean $displayAll to use pagination or not
    * @param integer $idExo id exercise selected in the filter, -1 if not selection
    * @param boolean $QuestionsExo if filter by exercise is used
    *
    * @return \Symfony\Component\HttpFoundation\Response
    */
    public function importQuestionAction($exoID, $pageGoNow, $maxPage, $nbItem, $displayAll, $idExo = -1, $QuestionsExo = 'false')
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
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
        $this->checkAccess($exercise);

        $workspace = $exercise->getResourceNode()->getWorkspace();

        $user = $this->container->get('security.token_storage')
                                ->getToken()->getUser();
        $uid = $user->getId();

        $services    = $this->container->get('ujm.exo_exercise');
        $questionSer = $this->container->get('ujm.exo_question');

        $exoAdmin = $services->isExerciseAdmin($exercise);

        // To paginate the result :
        $request = $this->get('request'); // Get the request which contains the following parameters :
        $page = $request->query->get('page', 1); // Get the choosen page (default 1)
        $click = $request->query->get('click', 'my'); // Get which array to change page (default 'my question')
        $pagerMy = $request->query->get('pagerMy', 1); // Get the page of the array my question (default 1)
        $pagerShared = $request->query->get('pagerShared', 1); // Get the pager of the array my shared question (default 1)
        $pageToGo = $request->query->get('pageGoNow'); // Page to go for the list of the questions of the exercise
        $max = 10; // Max of questions per page

        // If change page of my questions array
        if ($click == 'my') {
            // The choosen new page is for my questions array
            $pagerMy = $page;
        // Else if change page of my shared questions array
        } else if ($click == 'shared') {
            // The choosen new page is for my shared questions array
            $pagerShared = $page;
        }

        if ($exoAdmin === true) {

            if ($QuestionsExo == 'true') {

                $actionQ = array();

                if($idExo == -2) {
                    $listQExo = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:Interaction')
                        ->getUserModelImport($this->getDoctrine()->getManager(), $uid, $exoID);
                } else {
                    $listQExo = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:Interaction')
                        ->getExerciseInteractionImport($em, $idExo, $exoID);
                }

                $allActions = $questionSer->getActionsAllQuestions($listQExo, $uid);

                $actionQ = $allActions[0];
                $questionWithResponse = $allActions[1];
                $alreadyShared = $allActions[2];
                $sharedWithMe = $allActions[3];
                $shareRight = $allActions[4];

            } else {

                $interactions = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Interaction')
                    ->getUserInteractionImport($this->getDoctrine()->getManager(), $uid, $exoID);

                $shared = $em->getRepository('UJMExoBundle:Share')
                        ->getUserInteractionSharedImport($exoID, $uid, $em);

                if ($displayAll == 1) {
                    if (count($interactions) > count($shared)) {
                        $max = count($interactions);
                    } else {
                        $max = count($shared);
                    }
                }

                $sharedWithMe = array();

                $end = count($shared);

                for ($i = 0; $i < $end; $i++) {
                    $sharedWithMe[] = $em->getRepository('UJMExoBundle:Interaction')
                        ->findOneBy(array('question' => $shared[$i]->getQuestion()->getId()));
                }

                $doublePagination = $this->doublePagination($interactions, $sharedWithMe, $max, $pagerMy, $pagerShared);

                $interactionsPager = $doublePagination[0];
                $pagerfantaMy = $doublePagination[1];

                $sharedWithMePager = $doublePagination[2];
                $pagerfantaShared = $doublePagination[3];

                if ($pageToGo) {
                    $pageGoNow = $pageToGo;
                } else {
                    // If new item > max per page, display next page
                    $rest = $nbItem % $maxPage;

                    if ($nbItem == 0) {
                        $pageGoNow = 0;
                    }

                    if ($rest == 0) {
                        $pageGoNow += 1;
                    }
                }
            }

            $listExo = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:Exercise')
                        ->getExerciseAdmin($user->getId());

            if ($QuestionsExo == 'false') {
                $vars['pagerMy']      = $pagerfantaMy;
                $vars['pagerShared']  = $pagerfantaShared;
                $vars['interactions'] = $interactionsPager;
                $vars['sharedWithMe'] = $sharedWithMePager;
                $vars['pageToGo']     = $pageGoNow;
            } else {
                $vars['interactions'] = $listQExo;
                $vars['actionQ']      = $actionQ;
                $vars['pageToGo']     = 1;
            }
            $vars['questionWithResponse'] = $questionWithResponse;
            $vars['alreadyShared']        = $alreadyShared;
            $vars['shareRight']           = $shareRight;
            $vars['displayAll']           = $displayAll;
            $vars['listExo']              = $listExo;
            $vars['exoID']                = $exoID;
            $vars['QuestionsExo']         = $QuestionsExo;
            $vars['workspace']            = $workspace;
            $vars['_resource']            = $exercise;
            $vars['idExo']                = $idExo;

            return $this->render('UJMExoBundle:Question:import.html.twig', $vars);
        } else {
            return $this->redirect($this->generateUrl('ujm_exercise_open', ['id' => $exoID]));
        }
    }

    /**
     * To record the question's import.
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importValidateAction()
    {
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            $exoID = $request->request->get('exoID');
            $pageGoNow = $request->request->get('pageGoNow');
            $qid = $request->request->get('qid');

            $em = $this->getDoctrine()->getManager();
            $dql = 'SELECT max(eq.ordre) FROM UJM\ExoBundle\Entity\ExerciseQuestion eq '
                         . 'WHERE eq.exercise='.$exoID;
            $query = $em->createQuery($dql);
            $result = $query->getResult();
            $maxOrdre = (int) $result[0][1] + 1;

            foreach ($qid as $q) {
                $question = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Question')
                    ->find($q);

                if (count($question) > 0) {

                    $exo = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
                    $question = $em->getRepository('UJMExoBundle:Question')->find($q);

                    $eq = new ExerciseQuestion($exo, $question);
                    $eq->setOrdre((int) $maxOrdre);
                    $em->persist($eq);
                    $maxOrdre++;

                }

            }
            $em->flush();
            $url = (string)$this->generateUrl('ujm_exercise_questions',array('id' => $exoID,'pageNow' => $pageGoNow));

            return new \Symfony\Component\HttpFoundation\Response($url);
         } else {
            return $this->redirect($this->generateUrl('ujm_exercise_import_question', array('exoID' => $exoID)));
        }
    }

    /**
     * Delete the Question of the exercise.
     *
     * @access public
     *
     * @param integer $exoID id of exercise
     * @param integer $qid id of question to delete
     * @param integer $pageNow actual page for the pagination
     * @param integer $maxpage number max questions per page
     * @param integer $nbItem number of question
     * @param integer $lastPage number of last page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteQuestionAction($exoID, $qid, $pageNow, $maxPage, $nbItem, $lastPage)
    {
        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);

        $this->checkAccess($exercise);

        $exoAdmin = $this->container->get('ujm.exo_exercise')->isExerciseAdmin($exercise);

        if ($exoAdmin === true) {
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

        return $this->redirect(
            $this->generateUrl(
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
     * @access public
     *
     * @param integer $id id of exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exercisePaperAction($id)
    {
        $exerciseSer = $this->container->get('ujm.exo_exercise');

        $user = $exerciseSer->getUser();
        $uid  = $exerciseSer->getUserId();

        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($id);
        if (!$exerciseSer->allowToOpen($exercise)) {
            return $this->redirect($this->generateUrl('ujm_exercise_open', ['id' => $id]));
        }

        $exoAdmin = $exerciseSer->isExerciseAdmin($exercise);
        $this->checkAccess($exercise);

        $workspace = $exercise->getResourceNode()->getWorkspace();

        if ($exoAdmin || $exercise->getResourceNode()->isPublished()) {
            $session = $this->getRequest()->getSession();

            if ($uid != 'anonymous') {
                $dql = 'SELECT max(p.numPaper) FROM UJM\ExoBundle\Entity\Paper p '
                    . 'WHERE p.exercise='.$id.' AND p.user='.$uid;
                $query = $em->createQuery($dql);
                $maxNumPaper = $query->getSingleResult();

                //Verify if it exists a not finished paper
                $paper = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Paper')
                    ->getPaper($uid, $id);
            } else {
                $maxNumPaper[1] = 0;
                $paper = array();
            }

            //if not exist a paper no finished
            if (count($paper) == 0) {
                if ($exerciseSer->controlMaxAttemps($exercise, $uid, $exoAdmin) === false) {
                   return $this->redirect($this->generateUrl('ujm_paper_list', array('exoID' => $id)));
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

                if ( ($exercise->getNbQuestion() > 0) && ($exercise->getKeepSameQuestion()) == true ) {
                    $papers = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:Paper')
                        ->getExerciseUserPapers($uid, $id);
                    if(count($papers) == 0) {
                        $tab = $this->prepareInteractionsPaper($id, $exercise);
                        $interactions  = $tab['interactions'];
                        $orderInter    = $tab['orderInter'];
                        $tabOrderInter = $tab['tabOrderInter'];
                    } else {
                        $lastPaper = $papers[count($papers) - 1];
                        $orderInter = $lastPaper->getOrdreQuestion();
                        $tabOrderInter = explode(';', $lastPaper->getOrdreQuestion());
                        unset($tabOrderInter[count($tabOrderInter) - 1]);
                        $interactions[0] = $em->getRepository('UJMExoBundle:Interaction')->find($tabOrderInter[0]);
                    }
                } else {
                    $tab = $this->prepareInteractionsPaper($id, $exercise);
                    $interactions  = $tab['interactions'];
                    $orderInter    = $tab['orderInter'];
                    $tabOrderInter = $tab['tabOrderInter'];
                }

                $paper->setOrdreQuestion($orderInter);
                $em->persist($paper);
                $em->flush();
            } else {
                $paper = $paper[0];
                if (!$exercise->getDispButtonInterrupt()) {
                   return $this->forceFinishExercise($paper);
                }
                $tabOrderInter = explode(';', $paper->getOrdreQuestion());
                unset($tabOrderInter[count($tabOrderInter) - 1]);
                $interactions[0] = $em->getRepository('UJMExoBundle:Interaction')->find($tabOrderInter[0]);
            }

            $session->set('tabOrderInter', $tabOrderInter);
            $session->set('paper', $paper->getId());
            $session->set('exerciseID', $id);

            $typeInter = $interactions[0]->getType();

            //To display selectioned question
            return $this->displayQuestion(1, $interactions[0], $typeInter,
                    $exercise->getDispButtonInterrupt(),
                    $exercise->getMaxAttempts(),
                    $workspace, $paper);
        } else {
            return $this->redirect($this->generateUrl('ujm_paper_list', array('exoID' => $id)));
        }
    }

    /**
     * To create new paper
     *
     * @access private
     *
     * @param integer $id id of exercise
     * @param \UJM\ExoBundle\Entity\Exercise $exercise
     *
     * @return array
     */
    private function prepareInteractionsPaper($id, $exercise)
    {
        $orderInter = '';
        $tabOrderInter = array();
        $tab = array();

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

        $tab['interactions']  = $interactions;
        $tab['orderInter']    = $orderInter;
        $tab['tabOrderInter'] = $tabOrderInter;

        return $tab;
    }

    /**
     * To navigate in the Questions of the assessment
     *
     * @access public
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
                                      'p'  => -1
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

        $interSer  = $this->container->get('ujm.exo_' . $typeInterToRecorded);
        $res       = $interSer->response($request, $session->get('paper'));

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
            return $this->finishExercise($session);
        } else if ($numQuestionToDisplayed == 'interupt') {
            return $this->interuptExercise();
        } else {
            $interactionToDisplayedID = $tabOrderInter[$numQuestionToDisplayed - 1];
            $interactionToDisplay = $em->getRepository('UJMExoBundle:Interaction')->find($interactionToDisplayedID);
            $typeInterToDisplayed = $interactionToDisplay->getType();

            return $this->displayQuestion(
                $numQuestionToDisplayed, $interactionToDisplay, $typeInterToDisplayed,
                $response->getPaper()->getExercise()->getDispButtonInterrupt(),
                $response->getPaper()->getExercise()->getMaxAttempts(),
                $workspace, $paper
            );
        }
    }

    /**
     * To change the order of the questions into an exercise
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeQuestionOrderAction()
    {
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            $exoID = $request->request->get('exoID');
            $order = $request->request->get('order');
            $currentPage = $request->request->get('currentPage');
            $questionMaxPerPage = $request->request->get('questionMaxPerPage');

            if ($exoID && $order && $currentPage && $questionMaxPerPage) {

                $length = count($order);

                $em = $this->getDoctrine()->getManager();
                $exoQuestions = $em->getRepository('UJMExoBundle:ExerciseQuestion')->findBy(array('exercise' => $exoID));

                foreach ($exoQuestions as $exoQuestion) {
                    for ($i = 0; $i < $length; $i++) {
                        if ($exoQuestion->getQuestion()->getId() == $order[$i]) {
                            $newOrder = $i + 1 + (((int)$currentPage - 1) * (int)$questionMaxPerPage);
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
                'id' => $exoID
                )
            )
        );
    }

    /**
     * For the navigation in a paper
     * Finds and displays the question selectionned by the User in an assesment
     *
     * @access private
     *
     * @param integer $numQuestionToDisplayed position of the question in the paper
     * @param \UJM\ExoBundle\Entity\Interaction $interactionToDisplay interaction (question) to displayed
     * @param String $typeInterToDisplayed
     * @param boolean $dispButtonInterrupt to display or no the button "Interrupt"
     * @param integer $maxAttempsAllowed the number of max attemps allowed for the exercise
     * @param Claroline workspace $workspace
     * @param \UJM\ExoBundle\Entity\Paper $paper current paper
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function displayQuestion(
        $numQuestionToDisplayed, $interactionToDisplay,
        $typeInterToDisplayed, $dispButtonInterrupt, $maxAttempsAllowed,
        $workspace, $paper
    )
    {
        $session = $this->getRequest()->getSession();
        $tabOrderInter = $session->get('tabOrderInter');

        $interSer       = $this->container->get('ujm.exo_' .  $interactionToDisplay->getType());
        $interactionToDisplayed = $interSer->getInteractionX($interactionToDisplay->getId());
        $responseGiven  = $interSer->getResponseGiven($interactionToDisplay, $session, $interactionToDisplayed);

        $array['workspace']              = $workspace;
        $array['tabOrderInter']          = $tabOrderInter;
        $array['interactionToDisplayed'] = $interactionToDisplayed;
        $array['interactionType']        = $typeInterToDisplayed;
        $array['numQ']                   = $numQuestionToDisplayed;
        $array['paper']                  = $session->get('paper');
        $array['numAttempt']             = $paper->getNumPaper();
        $array['response']               = $responseGiven;
        $array['dispButtonInterrupt']    = $dispButtonInterrupt;
        $array['maxAttempsAllowed']      = $maxAttempsAllowed;
        $array['_resource']              = $paper->getExercise();

        return $this->render(
            'UJMExoBundle:Exercise:paper.html.twig',
            $array
        );
    }

    /**
     * To finish an assessment
     *
     * @access private
     *
     * @param Symfony\Component\HttpFoundation\Session\SessionInterface  $session
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function finishExercise(SessionInterface $session)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \UJM\ExoBundle\Entity\Paper $paper */
        $paper = $em->getRepository('UJMExoBundle:Paper')->find($session->get('paper'));
        $paper->setInterupt(0);
        $paper->setEnd(new \Datetime());
        $em->persist($paper);
        $em->flush();

        $this->container->get('ujm.exo_exercise')->manageEndOfExercise($paper);

        $session->remove('penalties');

        return $this->forward('UJMExoBundle:Paper:show', array('id' => $paper->getId()));
    }

    /**
     * To force finish an assessment
     *
     * @access private
     *
     * @param \UJM\ExoBundle\Entity\Paper $paperToClose
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function forceFinishExercise($paperToClose)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \UJM\ExoBundle\Entity\Paper $paper */
        $paper = $paperToClose;
        $paper->setInterupt(0);
        $paper->setEnd(new \Datetime());
        $em->persist($paper);
        $em->flush();

        $this->container->get('ujm.exo_exercise')->manageEndOfExercise($paper);

        return $this->forward('UJMExoBundle:Exercise:exercisePaper', array('id' => $paper->getExercise()->getId()));
    }

    /**
     * To interupt an assessment
     *
     * @access private
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function interuptExercise()
    {
        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();

        $paper = $em->getRepository('UJMExoBundle:Paper')->find($session->get('paper'));
        $paper->setInterupt(1);
        $em->persist($paper);
        $em->flush();

        return $this->redirect($this->generateUrl('ujm_exercise_open', ['id' => $paper->getExercise()->getId()]));
    }

    /**
     * To display the docimology's histogramms
     *
     * @access public
     *
     * @param integer $exerciseId exercise id
     * @param integer $nbPapers number of papers to this exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function docimologyAction($exerciseId, $nbPapers)
    {
        $docimoServ = $this->container->get('ujm.exo_docimology') ;
        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exerciseId);
        $this->checkAccess($exercise);

        $eqs = $em->getRepository('UJMExoBundle:ExerciseQuestion')->findBy(
            array('exercise' => $exerciseId),
            array('ordre' => 'ASC')
        );

        $papers = $em->getRepository('UJMExoBundle:Paper')->getExerciseAllPapers($exerciseId);

        if ($this->container->get('ujm.exo_exercise')->isExerciseAdmin($exercise)) {

            $workspace = $exercise->getResourceNode()->getWorkspace();

            $parameters['nbPapers']  = $nbPapers;
            $parameters['workspace'] = $workspace;
            $parameters['exoID']     = $exerciseId;
            $parameters['_resource'] = $exercise;

            if ($nbPapers >= 12) {
                $histoMark = $docimoServ->histoMark($exerciseId);
                $histoSuccess = $docimoServ->histoSuccess($exerciseId, $eqs, $papers);

                if ($exercise->getNbQuestion() == 0) {
                    $histoDiscrimination = $docimoServ->histoDiscrimination($exerciseId, $eqs, $papers);
                } else {
                    $histoDiscrimination['coeffQ'] = 'none';
                }

                $histoMeasureDifficulty = $docimoServ->histoMeasureOfDifficulty($exerciseId, $eqs);

                $parameters['scoreList']          = $histoMark['scoreList'];
                $parameters['frequencyMarks']     = $histoMark['frequencyMarks'];
                $parameters['maxY']               = $histoMark['maxY'];
                $parameters['questionsList']      = $histoSuccess['questionsList'];
                $parameters['seriesResponsesTab'] = $histoSuccess['seriesResponsesTab'];
                $parameters['maxY2']              = $histoSuccess['maxY'];
                $parameters['coeffQ']             = $histoDiscrimination['coeffQ'];
                $parameters['MeasureDifficulty']  = $histoMeasureDifficulty;
            }

            return $this->render('UJMExoBundle:Exercise:docimology.html.twig', $parameters);
        } else {

            return $this->redirect($this->generateUrl('ujm_exercise_open', ['id' => $exerciseId]));
        }
    }

    /**
     * To check the right to open exo or not
     *
     * @access private
     *
     * @param \UJM\ExoBundle\Entity\Exercise $exo
     *
     * @return exception
     */
    private function checkAccess($exo)
    {
        $collection = new ResourceCollection(array($exo->getResourceNode()));

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

    /**
     * To paginate two tables on one page
     *
     * @access private
     *
     * @param Doctrine Collection of \UJM\ExoBundle\Entity\Interaction $entityToPaginateOne
     * @param Doctrine Collection of \UJM\ExoBundle\Entity\Interaction $entityToPaginateTwo
     * @param integer $max number max items per page
     * @param integer $pageOne set current page for the first pagination
     * @param integer $pageTwo set current page for the second pagination
     *
     * @return array
     */
    private function doublePagination($entityToPaginateOne, $entityToPaginateTwo, $max, $pageOne, $pageTwo)
    {
        $adapterOne = new ArrayAdapter($entityToPaginateOne);
        $pagerOne = new Pagerfanta($adapterOne);

        $adapterTwo = new ArrayAdapter($entityToPaginateTwo);
        $pagerTwo = new Pagerfanta($adapterTwo);

        try {
            $entityPaginatedOne = $pagerOne
                ->setMaxPerPage($max)
                ->setCurrentPage($pageOne)
                ->getCurrentPageResults();

            $entityPaginatedTwo = $pagerTwo
                ->setMaxPerPage($max)
                ->setCurrentPage($pageTwo)
                ->getCurrentPageResults();
        } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
            throw $this->createNotFoundException("Cette page n'existe pas.");
        }


        $doublePagination[0] = $entityPaginatedOne;
        $doublePagination[1] = $pagerOne;

        $doublePagination[2] = $entityPaginatedTwo;
        $doublePagination[3] = $pagerTwo;

        return $doublePagination;
    }

    /**
     * To paginate table
     *
     * @access private
     *
     * @param Doctrine Collection of \UJM\ExoBundle\Entity\Interaction $entityToPaginate
     * @param integer $max number max items per page
     * @param integer $page set current page for the pagination
     * @param integer $pageNow is the current page
     *
     * @return array
     */
    private function paginationWithIf($entityToPaginate, $max, $page, $pageNow)
    {
        $adapter = new ArrayAdapter($entityToPaginate);
        $pager = new Pagerfanta($adapter);

        try {
            if ($pageNow == 0) {
                $entityPaginated = $pager
                    ->setMaxPerPage($max)
                    ->setCurrentPage($page)
                    ->getCurrentPageResults();
            } else {
                $entityPaginated = $pager
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pageNow)
                    ->getCurrentPageResults();
            }
        } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
            throw $this->createNotFoundException("Cette page n'existe pas.");
        }

        $pagination[0] = $entityPaginated;
        $pagination[1] = $pager;

        return $pagination;
    }
}
