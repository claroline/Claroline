<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\DomCrawler\Crawler;
//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

use UJM\ExoBundle\Entity\Category;
use UJM\ExoBundle\Entity\Choice;
use UJM\ExoBundle\Entity\Coords;
use UJM\ExoBundle\Entity\Document;
use UJM\ExoBundle\Entity\Interaction;
use UJM\ExoBundle\Entity\InteractionGraphic;
use UJM\ExoBundle\Entity\InteractionHole;
use UJM\ExoBundle\Entity\InteractionMatching;
use UJM\ExoBundle\Entity\InteractionOpen;
use UJM\ExoBundle\Entity\InteractionQCM;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Entity\Share;

use UJM\ExoBundle\Form\InteractionGraphicType;
use UJM\ExoBundle\Form\InteractionHoleType;
use UJM\ExoBundle\Form\InteractionMatchingType;
use UJM\ExoBundle\Form\InteractionOpenType;
use UJM\ExoBundle\Form\InteractionQCMType;
use UJM\ExoBundle\Form\QuestionType;
use UJM\ExoBundle\Form\ResponseType;

use UJM\ExoBundle\Repository\InteractionGraphicRepository;
/**
 * Question controller.
 *
 */
class QuestionController extends Controller
{
     /**
      * Lists the User's Question entities.
      *
      * @access public
      *
      * @param integer $pageNow for the pagination : actual page of my questions list
      * @param integer $pageNowShared for the pagination : actual page of my shared questions list
      * @param string $categoryToFind used for pagination (for example after creating a question, go back to page contaning this question)
      * @param string $titleToFind used for pagination (for example after creating a question, go back to page contaning this question)
      * @param integer $id resource id if the bank has acceded by an exercise
      * @param  boolean $displayAll to use pagination or not
      *
      * @return \Symfony\Component\HttpFoundation\Response
      */
    public function indexAction($pageNow = 0, $pageNowShared = 0, $categoryToFind = '', $titleToFind = '', $resourceId = -1, $displayAll = 0)
    {
        if(base64_decode($categoryToFind)) {
            $categoryToFind = base64_decode($categoryToFind);
            $titleToFind    = base64_decode($titleToFind);
        }
        $vars = array();
        $sharedWithMe = array();
        $shareRight = array();
        $questionWithResponse = array();
        $alreadyShared = array();

        $em = $this->getDoctrine()->getManager();

        $services = $this->container->get('ujm.exercise_services');

        if ($resourceId != -1) {
            $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($resourceId);
            $vars['_resource'] = $exercise;
        }

        // To paginate the result :
        $request = $this->get('request'); // Get the request which contains the following parameters :
        $page = $request->query->get('page', 1); // Get the choosen page (default 1)
        $click = $request->query->get('click', 'my'); // Get which array to fchange page (default 'my question')
        $pagerMy = $request->query->get('pagerMy', 1); // Get the page of the array my question (default 1)
        $pagerShared = $request->query->get('pagerShared', 1); // Get the pager of the array my shared question (default 1)
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

        $user = $this->container->get('security.context')->getToken()->getUser();
        $uid = $user->getId();

        $interactions = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Interaction')
            ->getUserInteraction($uid);

        foreach ($interactions as $interaction) {
            $actions = $services->getActionInteraction($em, $interaction);
            $questionWithResponse += $actions[0];
            $alreadyShared += $actions[1];
        }

        $shared = $em->getRepository('UJMExoBundle:Share')
            ->findBy(array('user' => $uid));

        foreach ($shared as $s) {
            $actionsS = $services->getActionShared($em, $s);
            $sharedWithMe += $actionsS[0];
            $shareRight += $actionsS[1];
            $questionWithResponse += $actionsS[2];
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

        if ($displayAll == 1) {
            if (count($interactions) > count($shared)) {
                $max = count($interactions);
            } else {
                $max = count($shared);
            }
        }

        $doublePagination = $this->doublePaginationWithIf($interactions, $sharedWithMe, $max, $pagerMy, $pagerShared, $pageNow, $pageNowShared);

        $interactionsPager = $doublePagination[0];
        $pagerfantaMy = $doublePagination[1];

        $sharedWithMePager = $doublePagination[2];
        $pagerfantaShared = $doublePagination[3];

        $listExo = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:Exercise')
                        ->getExerciseAdmin($uid);

        $vars['pagerMy']              = $pagerfantaMy;
        $vars['pagerShared']          = $pagerfantaShared;
        $vars['interactions']         = $interactionsPager;
        $vars['sharedWithMe']         = $sharedWithMePager;
        $vars['questionWithResponse'] = $questionWithResponse;
        $vars['alreadyShared']        = $alreadyShared;
        $vars['shareRight']           = $shareRight;
        $vars['displayAll']           = $displayAll;
        $vars['listExo']              = $listExo;
        $vars['idExo']                = -1;
        $vars['QuestionsExo']         = 'false';

        return $this->render('UJMExoBundle:Question:index.html.twig', $vars);
    }

    /**
     * To filter question by exercise
     *
     * @access public
     *
     * @param integer $idExo id of exercise selected in the list to filter questions
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bankFilterAction($idExo = -1)
    {
        $vars = array();
        $sharedWithMe = array();
        $shareRight = array();
        $questionWithResponse = array();
        $alreadyShared = array();

        $em = $this->getDoctrine()->getManager();

        $services = $this->container->get('ujm.exercise_services');

        $user = $this->container->get('security.context')->getToken()->getUser();
        $uid = $user->getId();

        $actionQ = array();

        if ($idExo == -2) {
            $listQExo = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Interaction')
                ->getUserModel($uid);
        } else {
            $listQExo = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Interaction')
                ->getExerciseInteraction($em, $idExo, 0);
        }

        $allActions = $services->getActionsAllQuestions($listQExo, $uid, $em);

        $actionQ = $allActions[0];
        $questionWithResponse = $allActions[1];
        $alreadyShared = $allActions[2];
        $sharedWithMe = $allActions[3];
        $shareRight = $allActions[4];

        $listExo = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:Exercise')
                        ->getExerciseAdmin($uid);

        $vars['interactions']         = $listQExo;
        $vars['actionQ']              = $actionQ;
        $vars['questionWithResponse'] = $questionWithResponse;
        $vars['alreadyShared']        = $alreadyShared;
        $vars['shareRight']           = $shareRight;
        $vars['displayAll']           = 0;
        $vars['listExo']              = $listExo;
        $vars['idExo']                = $idExo;
        $vars['QuestionsExo']         = 'true';

        return $this->render('UJMExoBundle:Question:index.html.twig', $vars);
    }

    /**
     * Finds and displays a Question entity.
     *
     * @access public
     *
     * @param integer $id id Interaction
     * @param integer $exoID id Exercise if the user is in an exercise, -1 if the user is in the question bank
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($id, $exoID)
    {
        $vars = array();
        $allowToAccess = 0;

        if ($exoID != -1) {
            $exercise = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Exercise')->find($exoID);
            $vars['_resource'] = $exercise;

            if ($this->container->get('ujm.exercise_services')
                     ->isExerciseAdmin($exercise)) {
                $allowToAccess = 1;
            }
        }

        $question = $this->controlUserQuestion($id);
        $sharedQuestion = $this->container->get('ujm.exercise_services')->controlUserSharedQuestion($id);

        if (count($question) > 0 || count($sharedQuestion) > 0 || $allowToAccess == 1) {
            $interaction = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Interaction')
                ->getInteraction($id);

            $typeInter = $interaction->getType();

            switch ($typeInter) {
                case "InteractionQCM":

                    $response = new Response();
                    $interactionQCM = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionQCM')
                        ->getInteractionQCM($interaction->getId());

                    if ($interactionQCM[0]->getShuffle()) {
                        $interactionQCM[0]->shuffleChoices();
                    } else {
                        $interactionQCM[0]->sortChoices();
                    }

                    $form   = $this->createForm(new ResponseType(), $response);

                    $vars['interactionToDisplayed'] = $interactionQCM[0];
                    $vars['form']           = $form->createView();
                    $vars['exoID']          = $exoID;

                    return $this->render('UJMExoBundle:InteractionQCM:paper.html.twig', $vars);

                case "InteractionGraphic":

                    $interactionGraph = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionGraphic')
                        ->getInteractionGraphic($interaction->getId());

                    $repository = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:Coords');

                    $listeCoords = $repository->findBy(array('interactionGraphic' => $interactionGraph[0]));

                    $vars['interactionToDisplayed'] = $interactionGraph[0];
                    $vars['listeCoords']        = $listeCoords;
                    $vars['exoID']              = $exoID;

                    return $this->render('UJMExoBundle:InteractionGraphic:paper.html.twig', $vars);

                case "InteractionHole":

                    $response = new Response();
                    $interactionHole = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionHole')
                        ->getInteractionHole($interaction->getId());

                    $form   = $this->createForm(new ResponseType(), $response);

                    $vars['interactionToDisplayed'] = $interactionHole[0];
                    $vars['form']            = $form->createView();
                    $vars['exoID']           = $exoID;

                    return $this->render('UJMExoBundle:InteractionHole:paper.html.twig', $vars);

                case "InteractionOpen":
                    $response = new Response();
                    $interactionOpen = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionOpen')
                        ->getInteractionOpen($interaction->getId());

                    $form   = $this->createForm(new ResponseType(), $response);

                    $vars['interactionToDisplayed'] = $interactionOpen[0];
                    $vars['form']            = $form->createView();
                    $vars['exoID']           = $exoID;

                    return $this->render('UJMExoBundle:InteractionOpen:paper.html.twig', $vars);

                case "InteractionMatching":
                    $response = new Response();
                    $interactionMatching = $this->getDoctrine()
                            ->getManager()
                            ->getRepository('UJMExoBundle:InteractionMatching')
                            ->getInteractionMatching($interaction->getId());

                    $form = $this->createForm(new ResponseType(), $response);

                    $vars['interactionToDisplayed'] = $interactionMatching[0];
                    $vars['form'] = $form->createView();
                    $vars['exoID'] = $exoID;

                    return $this->render('UJMExoBundle:InteractionMatching:paper.html.twig', $vars);
            }
        } else {
            return $this->redirect($this->generateUrl('ujm_question_index'));
        }
    }

    /**
     * Displays a form to create a new Question entity with interaction.
     *
     * @access public
     *
     * @param integer $exoID id Exercise if the user is in an exercise, -1 if the user is in the question bank
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction($exoID)
    {
        $variables = array(
            'exoID' => $exoID,
            'linkedCategory' =>  $this->container->get('ujm.exercise_services')->getLinkedCategories()
        );

        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);

        if ($exercise) {
            $variables['_resource'] = $exercise;
        }

        return $this->render('UJMExoBundle:Question:new.html.twig', $variables);
    }

    /**
     * Creates a new Question entity.
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $entity  = new Question();
        $request = $this->getRequest();
        $form    = $this->createForm(new QuestionType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('question_show', array('id' => $entity->getId())));
        }

        return $this->render(
            'UJMExoBundle:Question:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'linkedCategory' =>  $this->container->get('ujm.exercise_services')->getLinkedCategories()
            )
        );
    }

    /**
     * Displays a form to edit an existing Question entity.
     *
     * @access public
     *
     * @param integer $id id Interaction
     * @param integer $exoID id Exercise if the user is in an exercise, -1 if the user is in the question bank
     * @param \Symfony\Component\Form\FormBuilder $form if form is not valid (see the methods update in InteractionGraphicContoller, InteractionQCMConteroller ...)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id, $exoID, $form = null)
    {
        $services = $this->container->get('ujm.exercise_services');
        $question = $this->controlUserQuestion($id);
        $share    = $this->container->get('ujm.exercise_services')->controlUserSharedQuestion($id);
        $user     = $this->container->get('security.context')->getToken()->getUser();
        $catID    = -1;

        if(count($share) > 0) {
            $shareAllowEdit = $share[0]->getAllowToModify();
        }

        if ( (count($question) > 0) || ($shareAllowEdit) ) {
            $interaction = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Interaction')
                ->getInteraction($id);

            $typeInter = $interaction->getType();

            $nbResponses = 0;
            $em = $this->getDoctrine()->getManager();
            $response = $em->getRepository('UJMExoBundle:Response')
                ->findBy(array('interaction' => $interaction->getId()));
            $nbResponses = count($response);

            $linkedCategory = $this->container->get('ujm.exercise_services')->getLinkedCategories();

            if ($user->getId() != $interaction->getQuestion()->getUser()->getId()) {
                $catID = $interaction->getQuestion()->getCategory()->getId();
            }

            switch ($typeInter) {
                case "InteractionQCM":

                    $interactionQCM = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionQCM')
                        ->getInteractionQCM($interaction->getId());
                    //fired a sort function
                    $interactionQCM[0]->sortChoices();

                    if ($form == null) {
                        $editForm = $this->createForm(
                            new InteractionQCMType(
                                $this->container->get('security.context')
                                    ->getToken()->getUser(), $catID
                            ), $interactionQCM[0]
                        );
                    } else {
                        $editForm = $form;
                    }

                    $typeQCM = $services->getTypeQCM();

                    $variables['entity']         = $interactionQCM[0];
                    $variables['edit_form']      = $editForm->createView();
                    $variables['nbResponses']    = $nbResponses;
                    $variables['linkedCategory'] = $linkedCategory;
                    $variables['typeQCM'       ] = json_encode($typeQCM);
                    $variables['exoID']          = $exoID;

                    if ($exoID != -1) {
                        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
                        $variables['_resource'] = $exercise;
                    }

                    return $this->render('UJMExoBundle:InteractionQCM:edit.html.twig', $variables);

                case "InteractionGraphic":
                    $docID = -1;
                    $interactionGraph = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionGraphic')
                        ->getInteractionGraphic($interaction->getId());

                    $position = $em->getRepository('UJMExoBundle:Coords')->findBy(
                        array('interactionGraphic' => $interactionGraph[0]->getId()
                        )
                    );

                    if ($user->getId() != $interactionGraph[0]->getInteraction()->getQuestion()->getUser()->getId()) {
                        $docID = $interactionGraph[0]->getDocument()->getId();
                    }

                    $editForm = $this->createForm(
                        new InteractionGraphicType(
                            $this->container->get('security.context')
                                ->getToken()->getUser(), $catID, $docID
                        ), $interactionGraph[0]
                    );

                    $variables['entity']         = $interactionGraph[0];
                    $variables['edit_form']      = $editForm->createView();
                    $variables['nbResponses']    = $nbResponses;
                    $variables['linkedCategory'] = $linkedCategory;
                    $variables['position']       = $position;
                    $variables['exoID']          = $exoID;

                    if ($exoID != -1) {
                        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
                        $variables['_resource'] = $exercise;
                    }

                    return $this->render('UJMExoBundle:InteractionGraphic:edit.html.twig', $variables);

                case "InteractionHole":
                    $interactionHole = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionHole')
                        ->getInteractionHole($interaction->getId());

                    $editForm = $this->createForm(
                        new InteractionHoleType(
                            $this->container->get('security.context')
                                ->getToken()->getUser(), $catID
                        ), $interactionHole[0]
                    );

                    return $this->render(
                        'UJMExoBundle:InteractionHole:edit.html.twig', array(
                        'entity'      => $interactionHole[0],
                        'edit_form'   => $editForm->createView(),
                        'nbResponses' => $nbResponses,
                        'linkedCategory' => $linkedCategory,
                        'exoID' => $exoID
                        )
                    );

                case "InteractionOpen":

                    $interactionOpen = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionOpen')
                        ->getInteractionOpen($interaction->getId());

                    $editForm = $this->createForm(
                        new InteractionOpenType(
                            $this->container->get('security.context')
                                ->getToken()->getUser(), $catID
                        ), $interactionOpen[0]
                    );

                    if ($exoID != -1) {
                        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
                        $variables['_resource'] = $exercise;
                    }

                    $typeOpen = $services->getTypeOpen();

                    $variables['entity']         = $interactionOpen[0];
                    $variables['edit_form']      = $editForm->createView();
                    $variables['nbResponses']    = $nbResponses;
                    $variables['linkedCategory'] = $linkedCategory;
                    $variables['typeOpen']       = json_encode($typeOpen);
                    $variables['exoID']          = $exoID;

                    if ($exoID != -1) {
                        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
                        $variables['_resource'] = $exercise;
                    }

                    return $this->render('UJMExoBundle:InteractionOpen:edit.html.twig', $variables);

                case "InteractionMatching":

                    $interactionMatching = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionMatching')
                        ->getInteractionMatching($interaction->getId());

                    $correspondence = $services->initTabRightResponse($interactionMatching[0]);
                    foreach ($correspondence as $key => $corresp) {
                        $correspondence[$key] = explode('-', $corresp);
                    }
                    $tableLabel =  array();
                    $tableProposal = array();

                    $ind = 1;

                    foreach($interactionMatching[0]->getLabels() as $label){
                        $tableLabel[$ind] = $label->getId();
                        $ind++;
                    }

                    $ind = 1;
                    foreach($interactionMatching[0]->getProposals() as $proposal){
                        $tableProposal[$proposal->getId()] = $ind;
                        $ind++;
                    }

                    $editForm = $this->createForm(
                        new InteractionMatchingType(
                            $this->container->get('security.context')
                                ->getToken()->getUser(),$catID
                        ), $interactionMatching[0]
                    );

                    if ($exoID != -1) {
                       $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
                       $variable['_resource'] = $exercise;
                    }

                    $typeMatching = $services->getTypeMatching();

                    $variables['entity']         = $interactionMatching[0];
                    $variables['edit_form']      = $editForm->createView();
                    $variables['nbResponses']    = $nbResponses;
                    $variables['linkedCategory'] = $linkedCategory;
                    $variables['typeMatching']       = json_encode($typeMatching);
                    $variables['exoID']          = $exoID;
                    $variables['correspondence']  = json_encode($correspondence);
                    $variables['tableLabel']     = json_encode($tableLabel);
                    $variables['tableProposal']  = json_encode($tableProposal);

                    if ($exoID != -1) {
                        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
                        $variables['_resource'] = $exercise;
                    }

                    return $this->render('UJMExoBundle:InteractionMatching:edit.html.twig', $variables);

                    break;
            }
        } else {
            return $this->redirect($this->generateUrl('ujm_question_index'));
        }
    }

    /**
     * Deletes a Question entity.
     *
     * @access public
     *
     * @param integer $id id Interaction
     * @param integer $pageNow actual page for the pagination
     * @param integer $maxpage number max questions per page
     * @param integer $nbItem number of question
     * @param integer $lastPage number of last page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id, $pageNow, $maxPage, $nbItem, $lastPage)
    {
        $question = $this->controlUserQuestion($id);

        if (count($question) > 0) {
            $em = $this->getDoctrine()->getManager();

            $eq = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:ExerciseQuestion')
                ->getExercises($id);

            foreach ($eq as $e) {
                $em->remove($e);
            }

            $em->flush();

            $interaction = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Interaction')
                ->getInteraction($id);

            $typeInter = $interaction->getType();

             // If delete last item of page, display the previous one
            $rest = $nbItem % $maxPage;

            if ($rest == 1 && $pageNow == $lastPage) {
                $pageNow -= 1;
            }

            switch ($typeInter) {
                case "InteractionQCM":
                    $interactionQCM = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionQCM')
                        ->getInteractionQCM($interaction->getId());

                    return $this->forward(
                        'UJMExoBundle:InteractionQCM:delete', array(
                            'id' => $interactionQCM[0]->getId(),
                            'pageNow' => $pageNow
                        )
                    );

                case "InteractionGraphic":
                    $interactionGraph = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionGraphic')
                        ->getInteractionGraphic($interaction->getId());

                    return $this->forward(
                        'UJMExoBundle:InteractionGraphic:delete', array(
                            'id' => $interactionGraph[0]->getId(),
                            'pageNow' => $pageNow
                        )
                    );

                case "InteractionHole":
                    $interactionHole = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionHole')
                        ->getInteractionHole($interaction->getId());

                    return $this->forward(
                        'UJMExoBundle:InteractionHole:delete', array(
                            'id' => $interactionHole[0]->getId(),
                            'pageNow' => $pageNow
                        )
                    );

                case "InteractionOpen":
                    $interactionOpen = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:InteractionOpen')
                    ->getInteractionOpen($interaction->getId());

                    return $this->forward(
                        'UJMExoBundle:InteractionOpen:delete', array(
                            'id' => $interactionOpen[0]->getId(),
                            'pageNow' => $pageNow
                        )
                    );

                case "InteractionMatching":
                    $interactionMatching = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:InteractionMatching')
                    ->getInteractionMatching($interaction->getId());

                    return $this->forward(
                        'UJMExoBundle:InteractionMatching:delete', array(
                            'id' => $interactionMatching[0]->getId(),
                            'pageNow' => $pageNow
                        )
                    );

                    break;
            }
        }
    }

    /**
     * Displays the rigth form when a teatcher wants to create a new Question (JS)
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function choixFormTypeAction()
    {
        $services = $this->container->get('ujm.exercise_services');
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            $valType = 0;

            $valType = $request->request->get('indice_type');
            $exoID = $request->request->get('exercise');

            if ($valType != 0) {
                //index 1 = Hole Question
                if ($valType == 1) {
                    $entity = new InteractionHole();
                    $form   = $this->createForm(
                        new InteractionHoleType(
                            $this->container->get('security.context')
                                ->getToken()->getUser()
                        ), $entity
                    );

                    return $this->container->get('templating')->renderResponse(
                        'UJMExoBundle:InteractionHole:new.html.twig', array(
                        'exoID'  => $exoID,
                        'entity' => $entity,
                        'form'   => $form->createView()
                        )
                    );
                }

                //index 1 = QCM Question
                if ($valType == 2) {
                    $entity = new InteractionQCM();
                    $form   = $this->createForm(
                        new InteractionQCMType(
                            $this->container->get('security.context')
                                ->getToken()->getUser()
                        ), $entity
                    );

                    $typeQCM = $services->getTypeQCM();

                    return $this->container->get('templating')->renderResponse(
                        'UJMExoBundle:InteractionQCM:new.html.twig', array(
                        'exoID'   => $exoID,
                        'entity'  => $entity,
                        'typeQCM' => json_encode($typeQCM),
                        'form'    => $form->createView()
                        )
                    );
                }

                //index 1 = Graphic Question
                if ($valType == 3) {
                    $entity = new InteractionGraphic();
                    $form   = $this->createForm(
                        new InteractionGraphicType(
                            $this->container->get('security.context')
                                ->getToken()->getUser()
                        ), $entity
                    );

                    return $this->container->get('templating')->renderResponse(
                        'UJMExoBundle:InteractionGraphic:new.html.twig', array(
                        'exoID'  => $exoID,
                        'entity' => $entity,
                        'form'   => $form->createView()
                        )
                    );
                }

                //index 1 = Open Question
                if ($valType == 4) {
                    $entity = new InteractionOpen();
                    $form   = $this->createForm(
                        new InteractionOpenType(
                            $this->container->get('security.context')
                                ->getToken()->getUser()
                        ), $entity
                    );

                    $typeOpen = $services->getTypeOpen();

                    return $this->container->get('templating')->renderResponse(
                        'UJMExoBundle:InteractionOpen:new.html.twig', array(
                        'exoID'    => $exoID,
                        'entity'   => $entity,
                        'typeOpen' => json_encode($typeOpen),
                        'form'     => $form->createView()
                        )
                    );
                }

                if ($valType == 5) {
                    $entity = new InteractionMatching();
                    $form   = $this->createForm(
                        new InteractionMatchingType(
                            $this->container->get('security.context')
                                ->getToken()->getUser()
                        ), $entity
                    );

                    $typeMatching = $services->getTypeMatching();

                    return $this->container->get('templating')->renderResponse(
                        'UJMExoBundle:InteractionMatching:new.html.twig', array(
                        'exoID'    => $exoID,
                        'entity'   => $entity,
                        'typeMatching' => json_encode($typeMatching),
                        'form'     => $form->createView()
                        )
                    );
                }
            }
        }
    }

    /**
     * To share Question
     *
     * @access public
     *
     * @param integer $questionID id of question
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shareAction($questionID)
    {
        return $this->render(
            'UJMExoBundle:Question:share.html.twig', array(
            'questionID' => $questionID
            )
        );
    }

    /**
     * To search Question
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction()
    {
        $request = $this->get('request');

        $max = 10; // Max per page

        $search = $request->query->get('search');
        $page = $request->query->get('page');
        $questionID = $request->query->get('qId');

        if ($search != '') {
            $em = $this->getDoctrine()->getManager();
            $userList = $em->getRepository('ClarolineCoreBundle:User')->findByName($search);

            $pagination = $this->pagination($userList, $max, $page);

            $userListPager = $pagination[0];
            $pagerUserSearch = $pagination[1];

            // Put the result in a twig
            $divResultSearch = $this->render(
                'UJMExoBundle:Question:search.html.twig', array(
                'userList' => $userListPager,
                'pagerUserSearch' => $pagerUserSearch,
                'search' => $search,
                'questionID' => $questionID
                )
            );

            // If request is ajax (first display of the first search result (page = 1))
            if ($request->isXmlHttpRequest()) {
                return $divResultSearch; // Send the twig with the result
            } else {
                // Cut the header of the request to only have the twig with the result
                $divResultSearch = substr($divResultSearch, strrpos($divResultSearch, '<link'));

                // Send the form to search and the result
                return $this->render(
                    'UJMExoBundle:Question:share.html.twig', array(
                    'userList' => $userList,
                    'divResultSearch' => $divResultSearch,
                    'questionID' => $questionID
                    )
                );
            }

        } else {
            return $this->render(
                'UJMExoBundle:Question:search.html.twig', array(
                'userList' => '',
                )
            );
        }
    }

    /**
     * To manage the User's documents
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageDocAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $request = $this->get('request');

        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Document');

        $listDoc = $repository->findBy(array('user' => $user->getId()));

        foreach ($listDoc as $doc) {
            $interGraph = $this->getDoctrine()
                               ->getManager()
                               ->getRepository('UJMExoBundle:InteractionGraphic')
                               ->findOneBy(array('document' => $doc->getId()));
            if ($interGraph) {
                $allowToDel[$doc->getId()] = FALSE;
            } else {
                $allowToDel[$doc->getId()] = TRUE;
            }
        }

        // Pagination of the documents
        $max = 10; // Max questions displayed per page

        $page = $request->query->get('page', 1); // Which page

        $pagination = $this->pagination($listDoc, $max, $page);

        $listDocPager = $pagination[0];
        $pagerDoc= $pagination[1];

        return $this->render(
            'UJMExoBundle:Document:manageImg.html.twig',
            array(
                'listDoc'     => $listDocPager,
                'pagerDoc'    => $pagerDoc,
                'allowToDel' => $allowToDel
            )
        );
    }

    /**
     * To delete a User's document
     *
     * @access public
     *
     * @param integer $idDoc id Document
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteDocAction($idDoc)
    {

        $repositoryDoc = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Document');

        $doc = $repositoryDoc->find($idDoc);

        $em = $this->getDoctrine()->getManager();

        $interGraph = $em->getRepository('UJMExoBundle:InteractionGraphic')->findBy(array('document' => $doc));

        if (count($interGraph) == 0) {

            $em->remove($doc);
            $em->flush();

        }

        return new \Symfony\Component\HttpFoundation\Response('Document delete');
    }

    /**
     * To delete a User's document linked to questions but not to paper
     *
     * @param string $label label of document
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deletelinkedDocAction($label)
    {
        $userId = $this->container->get('security.context')->getToken()->getUser()->getId();

        $repositoryDoc = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Document');

        $listDoc = $repositoryDoc->findByLabel($label, $userId, 0);

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UJMExoBundle:InteractionGraphic')->findBy(array('document' => $listDoc));

        $end = count($entity);

        for ($i = 0; $i < $end; $i++) {

            $coords = $em->getRepository('UJMExoBundle:Coords')->findBy(array('interactionGraphic' => $entity[$i]->getId()));

            if (!$coords) {
                throw $this->createNotFoundException('Unable to find Coords link to interactiongraphic.');
            }

            $stop = count($coords);
            for ($x = 0; $x < $stop; $x++) {
                $em->remove($coords[$x]);
            }

            $em->remove($entity[$i]);
        }

        $em->remove($listDoc[0]);
        $em->flush();

        return $this->redirect($this->generateUrl('ujm_question_manage_doc'));
    }

    /**
     * To display the modal which allow to change the label of a document
     *
     * @access public
     *
     * @param integer $id id of exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeDocumentNameAction()
    {
        $request = $this->get('request'); // Get the request which contains the following parameters :
        $oldDocLabel = $request->request->get('oldDocLabel');
        $i = $request->request->get('i');

        return $this->render('UJMExoBundle:Document:changeName.html.twig', array('oldDocLabel' => $oldDocLabel, 'i' => $i));
    }

    /**
     * To change the label of a document
     *
     * @access public
     *
     * @param integer $id id of exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateNameAction()
    {
        $newlabel = $_POST['newlabel'];
        $oldlabel = $_POST['oldName'];

        $em = $this->getDoctrine()->getManager();

        $alterDoc = $em->getRepository('UJMExoBundle:Document')->findOneBy(array('label' => $oldlabel));

        $alterDoc->setLabel($newlabel);

        $em->persist($alterDoc);
        $em->flush();

        return new \Symfony\Component\HttpFoundation\Response($newlabel);
    }

    /**
     * To sort document by type
     *
     * @access public
     *
     * @param integer $id id of exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sortDocumentsAction()
    {
        $request = $this->container->get('request');
        $user = $this->container->get('security.context')->getToken()->getUser();

        $max = 10; // Max per page

        $type = $request->query->get('doctype');
        $searchLabel = $request->query->get('searchLabel');
        $page = $request->query->get('page');

        if ($type && isset($searchLabel)) {
            $repository = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Document');

            $listDocSort = $repository->findByType($type, $user->getId(), $searchLabel);

            $pagination = $this->pagination($listDocSort, $max, $page);

            $listDocSortPager = $pagination[0];
            $pagerSortDoc = $pagination[1];

            // Put the result in a twig
            $divResultSearch = $this->render(
                'UJMExoBundle:Document:sortDoc.html.twig', array(
                'listFindDoc' => $listDocSortPager,
                'pagerFindDoc' => $pagerSortDoc,
                'labelToFind' => $searchLabel,
                'whichAction' => 'sort',
                'doctype' => $type
                )
            );

            // If request is ajax (first display of the first search result (page = 1))
            if ($request->isXmlHttpRequest()) {
                return $divResultSearch; // Send the twig with the result
            } else {
                // Cut the header of the request to only have the twig with the result
                $divResultSearch = substr($divResultSearch, strrpos($divResultSearch, '<table'));

                // Send the form to search and the result
                return $this->render(
                    'UJMExoBundle:Document:manageImg.html.twig', array(
                    'divResultSearch' => $divResultSearch
                    )
                );
            }
        } else {
            return $this->render(
                'UJMExoBundle:Document:sortDoc.html.twig', array(
                'listFindDoc' => '',
                'whichAction' => 'sort'
                )
            );
        }
    }

    /**
     * To search document with a defined label
     *
     * @access public
     *
     * @param integer $id id of exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchDocAction()
    {
        $userId = $this->container->get('security.context')->getToken()->getUser()->getId();
        $request = $this->get('request');

        $max = 10; // Max per page

        $labelToFind = $request->query->get('labelToFind');
        $page = $request->query->get('page');

        if ($labelToFind) {
            $em = $this->getDoctrine()->getManager();
            $listFindDoc = $em->getRepository('UJMExoBundle:Document')->findByLabel($labelToFind, $userId, 1);

            $pagination = $this->pagination($listFindDoc, $max, $page);

            $listFindDocPager = $pagination[0];
            $pagerFindDoc = $pagination[1];

            // Put the result in a twig
            $divResultSearch = $this->render(
                'UJMExoBundle:Document:sortDoc.html.twig', array(
                'listFindDoc' => $listFindDocPager,
                'pagerFindDoc' => $pagerFindDoc,
                'labelToFind' => $labelToFind,
                'whichAction' => 'search'
                )
            );

            // If request is ajax (first display of the first search result (page = 1))
            if ($request->isXmlHttpRequest()) {
                return $divResultSearch; // Send the twig with the result
            } else {
                // Cut the header of the request to only have the twig with the result
                $divResultSearch = substr($divResultSearch, strrpos($divResultSearch, '<table'));

                // Send the form to search and the result
                return $this->render(
                    'UJMExoBundle:Document:manageImg.html.twig', array(
                    'divResultSearch' => $divResultSearch
                    )
                );
            }
        } else {
            return $this->render(
                'UJMExoBundle:Document:sortDoc.html.twig', array(
                'listFindDoc' => '',
                'whichAction' => 'search'
                )
            );
        }
    }


    /**
     * To share question with other users
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shareQuestionUserAction()
    {

        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            $questionID = $request->request->get('questionID'); // Which question is shared

            $uid = $request->request->get('uid');
            $allowToModify = $request->request->get('allowToModify');

            $em = $this->getDoctrine()->getManager();

            $question = $em->getRepository('UJMExoBundle:Question')->findOneBy(array('id' => $questionID));
            $user     = $em->getRepository('ClarolineCoreBundle:User')->find($uid);

            $share = $em->getRepository('UJMExoBundle:Share')->findOneBy(array('user' => $user, 'question' => $question));

            if (!$share) {
                $share = new Share($user, $question);
            }

            $share->setAllowToModify($allowToModify);

            $em->persist($share);
            $em->flush();

            return new \Symfony\Component\HttpFoundation\Response('no;'.$this->generateUrl('ujm_question_index'));

        }
    }

    /**
     * If question already shared with a given user
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Share $toShare
     * @param Doctrine Entity Manager $em
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function alreadySharedAction($toShare, $em)
    {
        $alreadyShared = $em->getRepository('UJMExoBundle:Share')->findAll();
        $already = false;

        $end = count($alreadyShared);

        for ($i = 0; $i < $end; $i++) {
            if ($alreadyShared[$i]->getUser() == $toShare->getUser() &&
                $alreadyShared[$i]->getQuestion() == $toShare->getQuestion()
            ) {
                $already = true;
                break;
            }
        }

        if ($already == true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Display form to search questions
     *
     * @access public
     *
     * @param integer $exoID id of exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchQuestionAction($exoID)
    {
        return $this->render('UJMExoBundle:Question:searchQuestion.html.twig', array(
            'exoID' => $exoID
            )
        );
    }

    /**
     * Display the questions matching to the research
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchQuestionTypeAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $request = $this->get('request');

        $listInteractions = array();
        $questionWithResponse = array();
        $alreadyShared = array();

        $max = 10; // Max questions displayed per page

        $type = $request->query->get('type'); // In which column
        $whatToFind = $request->query->get('whatToFind'); // Which text to find
        $where = $request->query->get('where'); // In which database
        $page = $request->query->get('page'); // Which page
        $exoID = $request->query->get('exoID'); // If we import or see the questions
        $displayAll = $request->query->get('displayAll', 0); // If we want to have all the questions in one page

//      echo $type . ' | '. $whatToFind . ' | '. $where . ' | '. $page . ' | '. $exoID . ' | '. $displayAll;die();
//      b4 : All | i | all | 1 | 5 | 0


        // If what and where to search is defined
        if ($type && $whatToFind && $where) {
            $em = $this->getDoctrine()->getManager();
            $questionRepository = $em->getRepository('UJMExoBundle:Question');
            $interactionRepository = $em->getRepository('UJMExoBundle:Interaction');

            // Get the matching questions depending on :
            //  * in which database search,
            //  * in witch column
            //  * and what text to search

            // User's database
            if ($where == 'my') {
                switch ($type) {
                    case 'Category':
                        $questions = $questionRepository->findByCategory($user->getId(), $whatToFind);

                        $end = count($questions);

                        for ($i = 0; $i < $end; $i++) {
                            $listInteractions[] = $interactionRepository->findOneBy(array('question' => $questions[$i]->getId()));
                        }
                        break;

                    case 'Type':
                        $listInteractions = $interactionRepository->findByType($user->getId(), $whatToFind);
                        break;

                    case 'Title':
                        $questions = $questionRepository->findByTitle($user->getId(), $whatToFind);

                        $end = count($questions);

                        for ($i = 0; $i < $end; $i++) {
                            $listInteractions[] = $interactionRepository->findOneBy(array('question' => $questions[$i]->getId()));
                        }
                        break;

                    case 'Contain':
                        $listInteractions = $interactionRepository->findByContain($user->getId(), $whatToFind);
                        break;

                    case 'All':
                        $listInteractions = $interactionRepository->findByAll($user->getId(), $whatToFind);
                        break;
                }

                // For all the matching interactions search if ...
                foreach ($listInteractions as $interaction) {
                    // ... the interaction is link to a paper (interaction in the test has already been passed)
                    $response = $em->getRepository('UJMExoBundle:Response')
                        ->findBy(array('interaction' => $interaction->getId()));
                    if (count($response) > 0) {
                        $questionWithResponse[$interaction->getId()] = 1;
                    } else {
                        $questionWithResponse[$interaction->getId()] = 0;
                    }

                    // ...the interaction is shared or not
                    $share = $em->getRepository('UJMExoBundle:Share')
                        ->findBy(array('question' => $interaction->getQuestion()->getId()));
                    if (count($share) > 0) {
                        $alreadyShared[$interaction->getQuestion()->getId()] = 1;
                    } else {
                        $alreadyShared[$interaction->getQuestion()->getId()] = 0;
                    }
                }

                if ($exoID == -1) {

                    if ($displayAll == 1) {
                        $max = count($listInteractions);
                    }

                    $pagination = $this->pagination($listInteractions, $max, $page);
                } else {
                    $exoQuestions = $em->getRepository('UJMExoBundle:ExerciseQuestion')->findBy(array('exercise' => $exoID));

                    $finalList = array();
                    $length = count($listInteractions);
                    $already = false;

                    for ($i = 0; $i < $length; $i++) {
                        foreach ($exoQuestions as $exoQuestion) {
                            if ($exoQuestion->getQuestion()->getId() == $listInteractions[$i]->getQuestion()->getId()) {
                                $already = true;
                                break;
                            }
                        }
                        if ($already == false) {
                            $finalList[] = $listInteractions[$i];
                        }
                        $already = false;
                    }

                    if ($displayAll == 1) {
                        $max = count($finalList);
                    }

                    $pagination = $this->pagination($finalList, $max, $page);
                }

                $listQuestionsPager = $pagination[0];
                $pagerSearch = $pagination[1];

                // Put the result in a twig
                if ($exoID == -1) {
                    $divResultSearch = $this->render(
                        'UJMExoBundle:Question:SearchQuestionType.html.twig', array(
                        'listQuestions' => $listQuestionsPager,
                        'canDisplay' => $where,
                        'pagerSearch' => $pagerSearch,
                        'type'        => $type,
                        'whatToFind'  => $whatToFind,
                        'questionWithResponse' => $questionWithResponse,
                        'alreadyShared' => $alreadyShared,
                        'exoID' => $exoID,
                        'displayAll' => $displayAll
                        )
                    );
                } else {
                    $divResultSearch = $this->render(
                        'UJMExoBundle:Question:searchQuestionImport.html.twig', array(
                            'listQuestions' => $listQuestionsPager,
                            'pagerSearch'   => $pagerSearch,
                            'exoID'         => $exoID,
                            'canDisplay'    => $where,
                            'whatToFind'    => $whatToFind,
                            'type'          => $type,
                            'displayAll'    => $displayAll
                        )
                    );
                }

                // If request is ajax (first display of the first search result (page = 1))
                if ($request->isXmlHttpRequest()) {
                    return $divResultSearch; // Send the twig with the result
                } else {
                    // Cut the header of the request to only have the twig with the result
                    $divResultSearch = substr($divResultSearch, strrpos($divResultSearch, '<link'));

                    // Send the form to search and the result
                    return $this->render(
                        'UJMExoBundle:Question:searchQuestion.html.twig', array(
                        'divResultSearch' => $divResultSearch,
                        'exoID' => $exoID
                        )
                    );
                }
            // Shared with user's database
            } else if ($where == 'shared') {
                switch ($type) {
                    case 'Category':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByCategoryShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listInteractions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;

                    case 'Type':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByTypeShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listInteractions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;

                    case 'Title':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByTitleShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listInteractions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;

                    case 'Contain':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByContainShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listInteractions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;

                    case 'All':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByAllShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listInteractions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;
                }

                if ($exoID == -1) {

                    if ($displayAll == 1) {
                        $max = count($listInteractions);
                    }

                    $pagination = $this->pagination($listInteractions, $max, $page);
                } else {
                    $exoQuestions = $em->getRepository('UJMExoBundle:ExerciseQuestion')->findBy(array('exercise' => $exoID));

                    $finalList = array();
                    $length = count($listInteractions);
                    $already = false;

                    for ($i = 0; $i < $length; $i++) {
                        foreach ($exoQuestions as $exoQuestion) {
                            if ($exoQuestion->getQuestion()->getId() == $listInteractions[$i]->getQuestion()->getId()) {
                                $already = true;
                                break;
                            }
                        }
                        if ($already == false) {
                            $finalList[] = $listInteractions[$i];
                        }
                        $already = false;
                    }

                    if ($displayAll == 1) {
                        $max = count($finalList);
                    }

                    $pagination = $this->pagination($finalList, $max, $page);
                }

                $listQuestionsPager = $pagination[0];
                $pagerSearch = $pagination[1];

                // Put the result in a twig
                if ($exoID == -1) {
                    $divResultSearch = $this->render(
                        'UJMExoBundle:Question:SearchQuestionType.html.twig', array(
                        'listQuestions' => $listQuestionsPager,
                        'canDisplay' => $where,
                        'pagerSearch' => $pagerSearch,
                        'type'        => $type,
                        'whatToFind'  => $whatToFind,
                        'exoID' => $exoID,
                        'displayAll' => $displayAll
                        )
                    );
                } else {
                    $divResultSearch = $this->render(
                        'UJMExoBundle:Question:searchQuestionImport.html.twig', array(
                            'listQuestions' => $listQuestionsPager,
                            'pagerSearch'   => $pagerSearch,
                            'exoID'         => $exoID,
                            'canDisplay'    => $where,
                            'whatToFind'    => $whatToFind,
                            'type'          => $type,
                            'displayAll'    => $displayAll
                        )
                    );
                }

                // If request is ajax (first display of the first search result (page = 1))
                if ($request->isXmlHttpRequest()) {
                    return $divResultSearch; // Send the twig with the result
                } else {
                    // Cut the header of the request to only have the twig with the result
                    $divResultSearch = substr($divResultSearch, strrpos($divResultSearch, '<link'));

                    // Send the form to search and the result
                    return $this->render(
                        'UJMExoBundle:Question:searchQuestion.html.twig', array(
                        'divResultSearch' => $divResultSearch,
                        'exoID' => $exoID
                        )
                    );
                }
            } else if ($where == 'all') {
                switch ($type) {
                    case 'Category':
                        $questions = $questionRepository->findByCategory($user->getId(), $whatToFind);

                        $end = count($questions);

                        for ($i = 0; $i < $end; $i++) {
                            $listInteractions[] = $interactionRepository->findOneBy(array('question' => $questions[$i]->getId()));
                        }

                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByCategoryShared($user->getId(), $whatToFind);

                        $ends = count($sharedQuestion);

                        for ($i = 0; $i < $ends; $i++) {
                            $listInteractions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;

                    case 'Type':
                        $listInteractions = $interactionRepository->findByType($user->getId(), $whatToFind);

                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByTypeShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listInteractions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;

                    case 'Title':
                        $questions = $questionRepository->findByTitle($user->getId(), $whatToFind);

                        $end = count($questions);

                        for ($i = 0; $i < $end; $i++) {
                            $listInteractions[] = $interactionRepository->findOneBy(array('question' => $questions[$i]->getId()));
                        }

                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByTitleShared($user->getId(), $whatToFind);

                        $ends = count($sharedQuestion);

                        for ($i = 0; $i < $ends; $i++) {
                            $listInteractions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;

                    case 'Contain':
                         $listInteractions = $interactionRepository->findByContain($user->getId(), $whatToFind);

                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByContainShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listInteractions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;

                    case 'All':
                        $listInteractions = $interactionRepository->findByAll($user->getId(), $whatToFind);

                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByAllShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listInteractions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;
                }

                // For all the matching interactions search if ...
                foreach ($listInteractions as $interaction) {
                    // ... the interaction is link to a paper (interaction in the test has already been passed)
                    $response = $em->getRepository('UJMExoBundle:Response')
                        ->findBy(array('interaction' => $interaction->getId()));
                    if (count($response) > 0) {
                        $questionWithResponse[$interaction->getId()] = 1;
                    } else {
                        $questionWithResponse[$interaction->getId()] = 0;
                    }

                    // ...the interaction is shared or not
                    $share = $em->getRepository('UJMExoBundle:Share')
                        ->findBy(array('question' => $interaction->getQuestion()->getId()));
                    if (count($share) > 0) {
                        $alreadyShared[$interaction->getQuestion()->getId()] = 1;
                    } else {
                        $alreadyShared[$interaction->getQuestion()->getId()] = 0;
                    }
                }

                if ($exoID == -1) {

                    if ($displayAll == 1) {
                        $max = count($listInteractions);
                    }

                    $pagination = $this->pagination($listInteractions, $max, $page);
                } else {
                    $exoQuestions = $em->getRepository('UJMExoBundle:ExerciseQuestion')->findBy(array('exercise' => $exoID));

                    $finalList = array();
                    $length = count($listInteractions);
                    $already = false;

                    for ($i = 0; $i < $length; $i++) {
                        foreach ($exoQuestions as $exoQuestion) {
                            if ($exoQuestion->getQuestion()->getId() == $listInteractions[$i]->getQuestion()->getId()) {
                                $already = true;
                                break;
                            }
                        }
                        if ($already == false) {
                            $finalList[] = $listInteractions[$i];
                        }
                        $already = false;
                    }

                    if ($displayAll == 1) {
                        $max = count($finalList);
                    }

                    $pagination = $this->pagination($finalList, $max, $page);
                }

                $listQuestionsPager = $pagination[0];
                $pagerSearch = $pagination[1];

                // Put the result in a twig
                if ($exoID == -1) {
                    $divResultSearch = $this->render(
                        'UJMExoBundle:Question:SearchQuestionType.html.twig', array(
                        'listQuestions' => $listQuestionsPager,
                        'canDisplay' => $where,
                        'pagerSearch' => $pagerSearch,
                        'type'        => $type,
                        'whatToFind'  => $whatToFind,
                        'questionWithResponse' => $questionWithResponse,
                        'alreadyShared' => $alreadyShared,
                        'exoID' => $exoID,
                        'displayAll' => $displayAll
                        )
                    );
                } else {
                    $divResultSearch = $this->render(
                        'UJMExoBundle:Question:searchQuestionImport.html.twig', array(
                            'listQuestions' => $listQuestionsPager,
                            'pagerSearch' => $pagerSearch,
                            'exoID' => $exoID,
                            'canDisplay' => $where,
                            'whatToFind'  => $whatToFind,
                            'type'        => $type,
                            'displayAll' => $displayAll
                        )
                    );
                }

                // If request is ajax (first display of the first search result (page = 1))
                if ($request->isXmlHttpRequest()) {
                    return $divResultSearch; // Send the twig with the result
                } else {
                    // Cut the header of the request to only have the twig with the result
                    $divResultSearch = substr($divResultSearch, strrpos($divResultSearch, '<link'));

                    // Send the form to search and the result
                    return $this->render(
                        'UJMExoBundle:Question:searchQuestion.html.twig', array(
                        'divResultSearch' => $divResultSearch,
                        'exoID' => $exoID
                        )
                    );
                }
            }
        } else {
            return $this->render(
                'UJMExoBundle:Question:SearchQuestionType.html.twig', array(
                'listQuestions' => '',
                'canDisplay' => $where,
                'whatToFind'  => $whatToFind,
                'type'        => $type
                )
            );
        }
    }

    /**
     * To delete the shared question of user's questions bank
     *
     * @access public
     *
     * @param integer $qid id Question
     * @param integer $uid id User, user connected
     * @param integer $pageNow actual page for the pagination
     * @param integer $maxpage number max questions per page
     * @param integer $nbItem number of question
     * @param integer $lastPage number of last page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteSharedQuestionAction($qid, $uid, $pageNow, $maxPage, $nbItem, $lastPage)
    {
        $em = $this->getDoctrine()->getManager();
        $sharedToDel = $em->getRepository('UJMExoBundle:Share')->findOneBy(array('question' => $qid, 'user' => $uid));

        if (!$sharedToDel) {
            throw $this->createNotFoundException('Unable to find Share entity.');
        }

        $em->remove($sharedToDel);
        $em->flush();

        // If delete last item of page, display the previous one
        $rest = $nbItem % $maxPage;

        if ($rest == 1 && $pageNow == $lastPage) {
            $pageNow -= 1;
        }

        return $this->redirect($this->generateUrl('ujm_question_index', array('pageNowShared' => $pageNow)));
    }

    /**
     * To see with which person the user has shared his question
     *
     * @access public
     *
     * @param integer $id id of question
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function seeSharedWithAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $questionsharedWith = $em->getRepository('UJMExoBundle:Share')->findBy(array('question' => $id));

        $sharedWith = array();
        $stop = count($questionsharedWith);

        for ($i = 0; $i < $stop; $i++) {
            $sharedWith[] = $em->getRepository('ClarolineCoreBundle:User')->find($questionsharedWith[$i]->getUser()->getId());
        }

        return $this->render(
            'UJMExoBundle:Question:seeSharedWith.html.twig', array(
            'sharedWith' => $sharedWith,
            )
        );
    }

    /**
     * To search questions brief in the question bank
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function briefSearchAction ()
    {
        $em = $this->getDoctrine()->getManager();
        $interactionRepository = $em->getRepository('UJMExoBundle:Interaction');

        $user = $this->container->get('security.context')->getToken()->getUser();
        $request = $this->get('request');

        $services = $this->container->get('ujm.exercise_services');

        $listInteractions = array();
        $actionQ = array();
        $questionWithResponse = array();
        $alreadyShared = array();
        $sharedWithMe = array();
        $shareRight = array();

        $searchToImport = FALSE;

        //if ($request->isXmlHttpRequest()) {
        $userSearch = $request->request->get('userSearch');
        $exoID = $request->request->get('exoID');
        $where = $request->request->get('where');

        if ($where == 'import') {
            $searchToImport = TRUE;
        }

        $listInteractions = $interactionRepository->findByAll($user->getId(), $userSearch, $searchToImport, $exoID);

        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
            ->findByAllShared($user->getId(), $userSearch);

        $end = count($sharedQuestion);

        for ($i = 0; $i < $end; $i++) {
            $listInteractions[] = $em->getRepository('UJMExoBundle:Interaction')
                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
        }

        $allActions = $services->getActionsAllQuestions($listInteractions, $user->getId(), $em);

        $actionQ = $allActions[0];
        $questionWithResponse = $allActions[1];
        $alreadyShared = $allActions[2];
        $sharedWithMe = $allActions[3];
        $shareRight = $allActions[4];

        $listExo = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Exercise')
                    ->getExerciseAdmin($user->getId());

        $vars['interactions']         = $listInteractions;
        $vars['actionQ']              = $actionQ;
        $vars['questionWithResponse'] = $questionWithResponse;
        $vars['alreadyShared']        = $alreadyShared;
        $vars['shareRight']           = $shareRight;
        $vars['listExo']              = $listExo;
        $vars['idExo']                = -1;
        $vars['displayAll']           = 0;
        $vars['QuestionsExo']         = 'true';

        if ($where == 'index') {

            return $this->render('UJMExoBundle:Question:index.html.twig', $vars);
        } else {
            $em = $this->getDoctrine()->getManager();
            $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);

            $workspace = $exercise->getResourceNode()->getWorkspace();

            $vars['workspace'] = $workspace;
            $vars['_resource'] = $exercise;
            $vars['exoID'] = $exoID;
            $vars['pageToGo'] = 1;

            return $this->render('UJMExoBundle:Question:import.html.twig', $vars);
        }
        //}
    }

    /**
     * To duplicate a question
     *
     * @access public
     *
     * @param integer $interID id Interaction
     * @param integer $exoID id Exercise if the user is in an exercise, -1 if the user is in the question bank
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function duplicateAction ($interID, $exoID)
    {
        $question = $this->controlUserQuestion($interID);
        $sharedQuestion = $this->container->get('ujm.exercise_services')->controlUserSharedQuestion($interID);

        $allowToAccess = FALSE;

        if ($exoID != -1) {
            $exercise = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Exercise')->find($exoID);

            if ($this->container->get('ujm.exercise_services')
                     ->isExerciseAdmin($exercise)) {
                $allowToAccess = TRUE;
            }
        }

        if (count($question) > 0 || count($sharedQuestion) > 0 || $allowToAccess === TRUE) {
            $interaction = $this->getDoctrine()
                                ->getManager()
                                ->getRepository('UJMExoBundle:Interaction')
                                ->getInteraction($interID);

            $typeInter = $interaction->getType();

            switch ($typeInter) {
                case "InteractionQCM":
                    $interactionX = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionQCM')
                        ->getInteractionQCM($interaction->getId());

                    $interXHandler = new \UJM\ExoBundle\Form\InteractionQCMHandler(
                        NULL , NULL, $this->getDoctrine()->getManager(),
                        $this->container->get('ujm.exercise_services'),
                        $this->container->get('security.context')->getToken()->getUser(), $exoID
                    );

                    break;


                case "InteractionGraphic":
                    $interactionX = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionGraphic')
                        ->getInteractionGraphic($interaction->getId());

                    $interXHandler = new \UJM\ExoBundle\Form\InteractionGraphicHandler(
                        NULL , NULL, $this->getDoctrine()->getManager(),
                        $this->container->get('ujm.exercise_services'),
                        $this->container->get('security.context')->getToken()->getUser(), $exoID
                    );

                    break;


                case "InteractionHole":
                    $interactionX = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionHole')
                        ->getInteractionHole($interaction->getId());

                    $interXHandler = new \UJM\ExoBundle\Form\InteractionHoleHandler(
                        NULL , NULL, $this->getDoctrine()->getManager(),
                        $this->container->get('ujm.exercise_services'),
                        $this->container->get('security.context')->getToken()->getUser(), $exoID
                    );

                    break;


                case "InteractionOpen":
                    $interactionX = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:InteractionOpen')
                    ->getInteractionOpen($interaction->getId());

                    $interXHandler = new \UJM\ExoBundle\Form\InteractionOpenHandler(
                        NULL , NULL, $this->getDoctrine()->getManager(),
                        $this->container->get('ujm.exercise_services'),
                        $this->container->get('security.context')->getToken()->getUser(), $exoID
                    );

                    break;

                case "InteractionMatching":
                    $interactionX = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:InteractionMatching')
                    ->getInteractionMatching($interaction->getId());

                    $interXHandler = new \UJM\ExoBundle\Form\InteractionMatchingHandler(
                        NULL , NULL, $this->getDoctrine()->getManager(),
                        $this->container->get('ujm.exercise_services'),
                        $this->container->get('security.context')->getToken()->getUser(), $exoID
                    );

                    break;
            }

            $interXHandler->singleDuplicateInter($interactionX[0]);

            $categoryToFind = $interactionX[0]->getInteraction()->getQuestion()->getCategory();
            $titleToFind = $interactionX[0]->getInteraction()->getQuestion()->getTitle();

            if ($exoID == -1) {
                return $this->redirect(
                    $this->generateUrl('ujm_question_index', array(
                        'categoryToFind' => base64_encode($categoryToFind), 'titleToFind' => base64_encode($titleToFind))
                    )
                );
            } else {
                return $this->redirect(
                    $this->generateUrl('ujm_exercise_questions', array(
                        'id' => $exoID, 'categoryToFind' => $categoryToFind, 'titleToFind' => $titleToFind)
                    )
                );
            }
        } else {
            return $this->redirect($this->generateUrl('ujm_question_index'));
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
    private function controlUserQuestion($questionID)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $question = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Question')
            ->getControlOwnerQuestion($user->getId(), $questionID);

        return $question;
    }

    /**
     * To paginate table
     *
     * @access private
     *
     * @param Doctrine Collection $entityToPaginate
     * @param integer $max number max items by page
     * @param integer $page number of actual page
     *
     * @return array
     */
    private function pagination($entityToPaginate, $max, $page)
    {
        $adapter = new ArrayAdapter($entityToPaginate);
        $pager = new Pagerfanta($adapter);

        try {
            $entityPaginated = $pager
                ->setMaxPerPage($max)
                ->setCurrentPage($page)
                ->getCurrentPageResults();
        } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
            throw $this->createNotFoundException("Cette page n'existe pas.");
        }

        $pagination[0] = $entityPaginated;
        $pagination[1] = $pager;

        return $pagination;
    }

    /**
     * To paginate two tables on one page
     *
     * @access public
     *
     * @param Doctrine Collection of \UJM\ExoBundle\Entity\Interaction $entityToPaginateOne
     * @param Doctrine Collection of \UJM\ExoBundle\Entity\Interaction $entityToPaginateTwo
     * @param integer $max number max items per page
     * @param integer $pageOne set new page for the first pagination
     * @param integer $pageTwo set new page for the second pagination
     * @param integer $pageNowOne set current page for the first pagination
     * @param integer $pageNowTwo set current page for the second pagination
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function doublePaginationWithIf($entityToPaginateOne, $entityToPaginateTwo, $max, $pageOne, $pageTwo, $pageNowOne, $pageNowTwo)
    {
        $adapterOne = new ArrayAdapter($entityToPaginateOne);
        $pagerOne = new Pagerfanta($adapterOne);

        $adapterTwo = new ArrayAdapter($entityToPaginateTwo);
        $pagerTwo = new Pagerfanta($adapterTwo);

        try {
            if ($pageNowOne == 0) {
                $entityPaginatedOne = $pagerOne
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pageOne)
                    ->getCurrentPageResults();
            } else {
                $entityPaginatedOne = $pagerOne
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pageNowOne)
                    ->getCurrentPageResults();
            }

            if ($pageNowTwo == 0) {
                $entityPaginatedTwo = $pagerTwo
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pageTwo)
                    ->getCurrentPageResults();
            } else {
                $entityPaginatedTwo = $pagerTwo
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pageNowTwo)
                    ->getCurrentPageResults();
            }
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
     * Edited by Hamza
     * Export an existing Question.
     *
     *
     * @access public
     *
     * @param integer $id : id of question
     *
     */
    public function ExportAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $question = $this->controlUserQuestion($id);

        if (count($question) > 0) {
            $interaction = $em->getRepository('UJMExoBundle:Interaction')
                              ->getInteraction($id);
            $typeInter = $interaction->getType();
            switch ($typeInter) {
                case "InteractionQCM":
                    $services = $this->container->get('ujm.qti_qcm_export');

                    return $services->export($interaction);

            }
        }

        return new \Symfony\Component\HttpFoundation\Response;
    }

    /**
     *
     * Edited by :Hamza
     * ListQuestions
     *
     */
    public function importAction()
    {
        $userDir = './uploads/ujmexo/qti/'.$this->container->get('security.context')
                        ->getToken()->getUser()->getUsername().'/';

      if (!is_dir('./uploads/ujmexo/')) {
        mkdir('./uploads/ujmexo/');
      }
      if (!is_dir('./uploads/ujmexo/qti/')) {
        mkdir('./uploads/ujmexo/qti/');
      }
      if (!is_dir($userDir)) {
        mkdir($userDir);
      }

                  $allowedExts = array("xml");
                  $temp = explode(".", $_FILES["f1"]["name"]);
                  $source = $_FILES["f1"]["tmp_name"];
                  $extension = end($temp);
                  $rst= "src tmp_name : ".$source;
                  $rst= $rst."test rst";
                  if ((($_FILES["f1"]["type"] == "text/xml")) && ($_FILES["f1"]["size"] < 20000000) && in_array($extension, $allowedExts)) {


                                if ($_FILES["f1"]["error"] > 0) {
                                  $rst =$rst . "Return Code: " . $_FILES["f1"]["error"] . "<br/>";
                                } else {
                                  $rst =$rst . "File: " . $_FILES["f1"]["name"] . "\n";
                                  $rst =$rst . "Type: " . $_FILES["f1"]["type"] . "\n";
                                  $rst =$rst . "Size: " . ($_FILES["f1"]["size"] / 1024) . " kB\n";
                                  if (file_exists("upload/" . $_FILES["f1"]["name"])) {
                                    $rst =$rst . $_FILES["f1"]["name"] . " already exists. ";
                                  } else {
                                    move_uploaded_file($_FILES["f1"]["tmp_name"],
                                    $userDir . $_FILES["f1"]["name"]);
                                    $rst =$rst . "Stored in: " . "uploadfiles/" . $_FILES["f1"]["name"];
                                  }
                                }

                                //import xml file
                                $file = $userDir.$_FILES["f1"]["name"];
                                $document_xml = new \DomDocument();
                                $document_xml->load($file);
                                $elements = $document_xml->getElementsByTagName('assessmentItem');
                                $element = $elements->item(0); // On obtient le nœud assessmentItem
                                //$childs = $element->childNodes;
                                if ($element->hasAttribute("title")) {
                                    $title = $element->getAttribute("title");
                                }
                                //get the type of the QCM choiceMultiple or choice
                                $typeqcm = $element->getAttribute("identifier");

                                //Import for Question QCM
                                if($typeqcm=="choiceMultiple" || $typeqcm=="choice" ){
                                            $nodeList=$element->getElementsByTagName("responseDeclaration");
                                            $responseDeclaration=($nodeList->item(0));
                                            $nodeList2=$responseDeclaration->getElementsByTagName("correctResponse");
                                            $correctResponse=$nodeList2->item(0);
                                            $nodelist3 = $correctResponse->getElementsByTagName("value");
                                            $lstmapping = $responseDeclaration->getElementsByTagName('mapping');
                                            $mapEntrys=null;
                                            $baseValue =null;
                                            $baseValue2=null;
                                            if($responseDeclaration->getElementsByTagName("mapping")->item(0)){
                                                $mapping   = $responseDeclaration->getElementsByTagName("mapping")->item(0);
                                                $mapEntrys = $mapping->getElementsByTagName("mapEntry");
                                            }
                                            else
                                                {
                                                $responseProcessing   = $element->getElementsByTagName("responseProcessing")->item(0);
                                                $responseCondition = $responseProcessing->getElementsByTagName("responseCondition")->item(0);
                                                $responseIf = $responseProcessing->getElementsByTagName("responseIf")->item(0);
                                                $setOutcomeValue = $responseIf->getElementsByTagName("setOutcomeValue")->item(0);
                                                $baseValue = $setOutcomeValue->getElementsByTagName("baseValue")->item(0)->nodeValue;
                                                //echo $baseValue;
                                                $responseElse = $responseProcessing->getElementsByTagName("responseElse")->item(0);
                                                $setOutcomeValue = $responseElse->getElementsByTagName("setOutcomeValue")->item(0);
                                                $baseValue2 = $setOutcomeValue->getElementsByTagName("baseValue")->item(0)->nodeValue;
                                                //echo $baseValue2;

                                            }


                                            $modalfeedback=null;
                                            if($element->getElementsByTagName("modalFeedback")->item(0)){

                                                $modalfeedback=$element->getElementsByTagName("modalFeedback");
                                            }

                                            //array correct choices
                                            $correctchoices = new \Doctrine\Common\Collections\ArrayCollection;

                                            foreach($nodelist3 as $value)
                                            {
                                                $valeur = $value->nodeValue."\n";
                                                $correctchoices->add($valeur);
                                                //$rst =$rst."--------value : ".$valeur."\n";
                                            }
                                            if($element->getElementsByTagName("outcomeDeclaration")->item(0)){
                                                $nodeList=$element->getElementsByTagName("outcomeDeclaration");
                                                $responseDeclaration=($nodeList->item(0));
                                                $nodeList2=$responseDeclaration->getElementsByTagName("defaultValue");
                                                $correctResponse=$nodeList2->item(0);
                                                $nodelist3 = $correctResponse->getElementsByTagName("value");

                                                foreach($nodelist3 as $score)
                                                {
                                                    $valeur = $score->nodeValue."\n";
                                                    $rst =$rst."--------score : ".$valeur."\n";
                                                }
                                            }


                                            $nodeList=$element->getElementsByTagName("itemBody");
                                            $itemBody=($nodeList->item(0));
                                            $nodeList2=$itemBody->getElementsByTagName("choiceInteraction");
                                            $choiceInteraction=$nodeList2->item(0);
                                            //question
                                            $prompt=null;
                                            if($choiceInteraction->getElementsByTagName("prompt")->item(0)){
                                                $prompt = $choiceInteraction->getElementsByTagName("prompt")->item(0)->nodeValue;
                                            }else{
                                                $prompt= $title;
                                            }
                                            //$rst =$rst."--------prompt : ".$prompt."\n";



                                            //array correct choices
                                            $choices = new \Doctrine\Common\Collections\ArrayCollection;
                                            $commentsperline = new \Doctrine\Common\Collections\ArrayCollection;

                                            $identifier_choices = new \Doctrine\Common\Collections\ArrayCollection;

                                            $nodeList3=$choiceInteraction->getElementsByTagName("simpleChoice");
                                            $rst="";
                                            foreach($nodeList3 as $simpleChoice)
                                            {

                                                if($simpleChoice->getElementsByTagName("feedbackInline")->item(0)){
                                                     $feedbackInline = $simpleChoice->getElementsByTagName("feedbackInline")->item(0)->nodeValue;
                                                     $feedback = $simpleChoice->getElementsByTagName("feedbackInline")->item(0);
                                                     $choicestest= $simpleChoice->removeChild($feedback);
                                                     $commentsperline->add($feedbackInline);
                                                }
                                                $choices->add($simpleChoice->nodeValue);
                                                $identifier_choices->add($simpleChoice->getAttribute("identifier"));

                                                //test
                                                //$feedback= $simpleChoice->getElementsByTagName("feedbackInline")->item(0);
                                                $rst =$rst."--_-_-_-_---removetst----Choice : ".$simpleChoice->nodeValue ."\n";
                                                //$rst =$rst."-_-_-removetst-end_-_-";
                                                // $feedbackInline = $feedback->nodeValue;
                                                //$rst =$rst."--_-_-_-_---removetst----feedback : ".$feedbackInline."\n";
                                                //$rst =$rst."--------identifier ".$identifier."\n";
                                            }


                                            //add the question o the database :

                                            $question  = new Question();
                                            $Category = new Category();
                                            $interaction =new Interaction();
                                            $interactionqcm =new InteractionQCM();




                                            //question & category
                                            $question->setTitle($title);
                                            //check if the Category "Import" exist --else-- will create it
                                            $Category_import = $this->getDoctrine()
                                                                 ->getManager()
                                                                 ->getRepository('UJMExoBundle:Category')->findBy(array('value' => "import"));
                                            if(count($Category_import)==0){
                                                $Category->setValue("import");
                                                $Category->setUser($this->container->get('security.context')->getToken()->getUser());
                                                $question->setCategory($Category);
                                            }else{
                                                $question->setCategory($Category_import[0]);
                                            }


                                            $question->setUser($this->container->get('security.context')->getToken()->getUser());
                                            $date = new \Datetime();
                                            $question->setDateCreate(new \Datetime());



                                            //Interaction

                                            $interaction->setType('InteractionQCM');
                                            if($prompt!=null){
                                                 $interaction->setInvite($prompt);
                                            }
                                            $interaction->setQuestion($question);
                                            if($modalfeedback!=null){
                                                if(($modalfeedback->item(0)->nodeValue != null) && ($modalfeedback->item(0)->nodeValue != "")){
                                                $interaction->setFeedBack($modalfeedback->item(0)->nodeValue);
                                                }
                                            }







                                            $em = $this->getDoctrine()->getManager();


                                            $ord=1;
                                            $index =0;
                                            foreach ($choices as $choix) {
                                                //choices
                                                $choice1 = new Choice();
                                                $choice1->setLabel($choix);
                                                $choice1->setOrdre($ord);

                                                //add Mappentry
                                                $weight =False;
                                                if($mapEntrys!=null){
                                                    if(count($mapEntrys)>0){
                                                    $mapEntry=$mapEntrys->item($index);
                                                    $mappedValue=$mapEntry->getAttribute("mappedValue");
                                                    //$mapKey=$mapEntry->getAttribute("mappedValue");
                                                    $choice1->setWeight($mappedValue);
                                                    $interactionqcm->setWeightResponse(1);
                                                    $weight =True;
                                                    }
                                                     $interactionqcm->setScoreRightResponse(0);
                                                     $interactionqcm->setScoreFalseResponse(0);
                                                }else{

                                                    $interactionqcm->setScoreFalseResponse($baseValue2);
                                                    $interactionqcm->setScoreRightResponse($baseValue);
                                                    $interactionqcm->setWeightResponse(0);
                                                }


                                                //
                                                //add comment
                                                if(count($commentsperline)>0){
                                                    $choice1->setFeedback($commentsperline[$index]);
                                                }
                                                //
                                                foreach ($correctchoices as $corrvalue) {
                                                    $rst= $rst."------------".$identifier_choices[$index]."*--------------------".$corrvalue;
                                                    if(strtolower(trim($identifier_choices[$index])) == strtolower(trim($corrvalue))){
                                                        $rst= $rst."***********".$identifier_choices[$index]."***********".$corrvalue;
                                                        $choice1->setRightResponse(TRUE);
                                                    }
                                                }
                                                $interactionqcm->addChoice($choice1);
                                                $em->persist($choice1);
                                                $ord=$ord+1;
                                                $index=$index+1;
                                            }
                                            //InteractionQCM
                                            $type_qcm = $this->getDoctrine()
                                                        ->getManager()
                                                        ->getRepository('UJMExoBundle:TypeQCM')->findAll();
                                            if($typeqcm=="choice"){
                                                $interactionqcm->setTypeQCM($type_qcm[1]);
                                            }else{
                                                $interactionqcm->setTypeQCM($type_qcm[0]);
                                            }
                                            $interactionqcm->setInteraction($interaction);




                                            $em->persist($interactionqcm);
                                            $em->persist($interactionqcm->getInteraction()->getQuestion());
                                            $em->persist($interactionqcm->getInteraction());

                                            if(count($Category_import)==0){
                                            $em->persist($Category);
                                            }
                                                //echo($choice->getRightResponse());


                                            $em->flush();
                                }

                  } else {
                    $rst =$rst . "Invalid file";
                  }
                    $rst = $rst . dirname(__FILE__).'/'."\n";

                   //if it's QTI zip file  --> unzip the file into this path "/var/www/Claroline/web/uploadfiles/" --> add to the database the resources (images)
                  if(($_FILES["f1"]["type"] == "application/zip") && ($_FILES["f1"]["size"] < 20000000)){

                      $rst = 'its a zip file';
                      move_uploaded_file($_FILES["f1"]["tmp_name"],
                                $userDir . $_FILES["f1"]["name"]);
                      $zip = new \ZipArchive;
                      $zip->open($userDir . $_FILES["f1"]["name"]);
                      $res= zip_open($userDir . $_FILES["f1"]["name"]);

                      $zip->extractTo($userDir);
                      $tab_liste_fichiers = array();
                      while ($zip_entry = zip_read($res)) //Pour chaque fichier contenu dans le fichier zip
                        {
                            if(zip_entry_filesize($zip_entry) > 0)
                            {
                                $nom_fichier = zip_entry_name($zip_entry);
                                $rst =$rst . '-_-_-_'.$nom_fichier;
                                array_push($tab_liste_fichiers,$nom_fichier);

                            }
                        }

                      $zip->close();



                        //Import for Question QCM --> from unZip File --> Type choiceMultiple Or  choice
                        //import xml file
                                $file = "$userDir/SchemaQTI.xml";
                                $document_xml = new \DomDocument();
                                $document_xml->load($file);
                                $elements = $document_xml->getElementsByTagName('assessmentItem');
                                $element = $elements->item(0); // On obtient le nœud assessmentItem
                                //$childs = $element->childNodes;
                                if ($element->hasAttribute("title")) {
                                    $title = $element->getAttribute("title");
                                }
                                //get the type of the QCM choiceMultiple or choice
                                $typeqcm = $element->getAttribute("identifier");
                                //echo $typeqcm;
                                if(($typeqcm=="choiceMultiple") || ($typeqcm=="choice") ){

                                                                           //début : récupération des fichiers et stocker les images dans les tables File
                                                                                //creation of the ResourceNode & File for the images...
                                                                                $user= $this->container->get('security.context')->getToken()->getUser();
                                                                                //createur du workspace
                                                                                $workspace = $this->getDoctrine()->getManager()->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findBy(array('creator' => $user->getId()));
                                                                                //$directory = $this->getReference("directory/{$this->directory}");
                                                                                //$directory = $this->get('claroline.manager.resource_manager');
                                                                                $resourceManager = $this->container->get('claroline.manager.resource_manager');
                                                                                $filesDirectory = $this->container->getParameter('claroline.param.files_directory');
                                                                                $ut = $this->container->get('claroline.utilities.misc');
                                                                                $fileType = $this->getDoctrine()->getManager()->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('file');
                                                                                $rst =$rst .'---wrkspace----'.$workspace[0]->getName().'-------------';

                                                                                $liste_resource_idnode = array();
                                                                                foreach ($tab_liste_fichiers as $filename) {

                                                                                    //filepath contain the path of the files in the extraction palce "uploadfile"
                                                                                    $filePath = $userDir.$filename;
                                                                                    $filePathParts = explode(DIRECTORY_SEPARATOR, $filePath);
                                                                                    //file name of the file
                                                                                    $fileName = array_pop($filePathParts);
                                                                                    //extension of the file
                                                                                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                                                                                    $hashName = "{$ut->generateGuid()}.{$extension}";

                                                                                    $targetFilePath = $filesDirectory . DIRECTORY_SEPARATOR . $hashName;
                                                                                    //$directory = $this->getReference($filesDirectory);

                                                                                    $file = new \Claroline\CoreBundle\Entity\Resource\File();
                                                                                    $file->setName($fileName);
                                                                                    $file->setHashName($hashName);

                                                                                    $rst =$rst . '-_-hashname_-_'.$hashName.'--extention---'.$extension.'--targetFilePath---'.$targetFilePath;
                                                                                    if(($extension=='jpg')||($extension=='jpeg')||($extension=='gif')){
                                                                                        if (file_exists($filePath)) {
                                                                                            copy($filePath, $targetFilePath);
                                                                                            $file->setSize(filesize($filePath));
                                                                                        } else {
                                                                                            touch($targetFilePath);
                                                                                            $file->setSize(0);
                                                                                        }
                                                                                        $mimeType = MimeTypeGuesser::getInstance()->guess($targetFilePath);
                                                                                        $rst =$rst . '-_-MimeTypeGuesser-_'.$mimeType;
                                                                                        $file->setMimeType($mimeType);

                                                                                        //creation ressourcenode
                                                            //                            $node = new ResourceNode();
                                                            //                            $node->setResourceType($fileType);
                                                            //                            $node->setCreator($user);
                                                            //                            $node->setWorkspace($workspace[0]);
                                                            //                            $node->setCreationDate(new \Datetime());
                                                            //                            $node->setClass('Claroline\CoreBundle\Entity\Resource\File');
                                                            //                            $node->setName($workspace[0]->getName());
                                                            //                            $node->setMimeType($mimeType);

                                                                                       // $file->setResourceNode($node);

                                                                                        //$this->getDoctrine()->getManager()->persist($node);
                                                                                        $role = $this
                                                                                                    ->getDoctrine()
                                                                                                    ->getRepository('ClarolineCoreBundle:Role')
                                                                                                    ->findManagerRole($workspace[0]);
                                                                                        $rigths = array(
                                                                                             'ROLE_WS_MANAGER' => array('open' => true, 'export' => true, 'create' => array(),
                                                                                                                        'role' => $role
                                                                                                                       )
                                                                                        );
                                                                                        //echo 'ws : '.$user->getPersonalWorkspace()->getName();die();
                                                                                        $parent = $this
                                                                                                    ->getDoctrine()
                                                                                                    ->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                                                                                                    ->findWorkspaceRoot($user->getPersonalWorkspace());

                                                                                        $resourceManager->create($file, $fileType, $user, $user->getPersonalWorkspace(), $parent, NULL, $rigths);// ,$node);
                                                                                            //list of the Resource ID Node that already craeted

                                                                                             array_push($liste_resource_idnode,$file->getResourceNode()->getId());

                                                                                    }
                                                                                }
                                                                                 //$file->getResourceNode()->getId()  ;die();
                                                                                $this->getDoctrine()->getManager()->flush();
                                                                      //Fin récupération & stockage
                                            $nodeList=$element->getElementsByTagName("responseDeclaration");
                                            $responseDeclaration=($nodeList->item(0));
                                            $nodeList2=$responseDeclaration->getElementsByTagName("correctResponse");
                                            $correctResponse=$nodeList2->item(0);
                                            $nodelist3 = $correctResponse->getElementsByTagName("value");

                                            //array correct choices
                                            $correctchoices = new \Doctrine\Common\Collections\ArrayCollection;

                                            foreach($nodelist3 as $value)
                                            {
                                                $valeur = $value->nodeValue."\n";
                                                $correctchoices->add($valeur);
                                                //$rst =$rst."--------value : ".$valeur."\n";
                                            }

                                            $nodeList=$element->getElementsByTagName("outcomeDeclaration");
                                            $responseDeclaration=($nodeList->item(0));
                                            $nodeList2=$responseDeclaration->getElementsByTagName("defaultValue");
                                            $correctResponse=$nodeList2->item(0);
                                            $nodelist3 = $correctResponse->getElementsByTagName("value");

                                            foreach($nodelist3 as $score)
                                            {
                                                $valeur = $score->nodeValue."\n";
                                                $rst =$rst."--------score : ".$valeur."\n";
                                            }

                                            $nodeList=$element->getElementsByTagName("itemBody");
                                            $itemBody=($nodeList->item(0));
                                            $nodeList2=$itemBody->getElementsByTagName("choiceInteraction");
                                            $choiceInteraction=$nodeList2->item(0);
                                            //question
                                            if($choiceInteraction->getElementsByTagName("prompt")->item(0)){
                                                $prompt = $choiceInteraction->getElementsByTagName("prompt")->item(0)->nodeValue;
                                                //change the src of the image :by using this path with integrating the resourceIdNode "/Claroline/web/app_dev.php/file/resource/media/5"
                                                            $dom2 = new \DOMDocument();
                                                            $dom2->loadHTML(html_entity_decode($prompt));
                                                            $listeimgs = $dom2->getElementsByTagName("img");
                                                            $index = 0;
                                                            foreach($listeimgs as $img)
                                                            {
                                                              if ($img->hasAttribute("src")) {
                                                                 $img->setAttribute("src","/Claroline/web/app_dev.php/file/resource/media/".$liste_resource_idnode[$index]);
                                                              }
                                                             $index= $index +1;
                                                            }
                                                            $res_prompt = $dom2->saveHTML();
                                                           // echo htmlentities($res);
                                            //$rst =$rst."--------prompt : ".$prompt."\n";
                                            }else{
                                                $res_prompt= $title;
                                            }



                                            //array correct choices
                                            $choices = new \Doctrine\Common\Collections\ArrayCollection;
                                            $identifier_choices = new \Doctrine\Common\Collections\ArrayCollection;

                                            $nodeList3=$choiceInteraction->getElementsByTagName("simpleChoice");
                                            foreach($nodeList3 as $simpleChoice)
                                            {
                                                $choices->add($simpleChoice->nodeValue);
                                                $identifier_choices->add($simpleChoice->getAttribute("identifier"));
                                                //$rst =$rst."--------Choice : ".$valeur."\n";
                                                //$identifier =
                                                //$rst =$rst."--------identifier ".$identifier."\n";
                                            }


                                            //add the question o the database :

                                            $question  = new Question();
                                            $Category = new Category();
                                            $interaction =new Interaction();
                                            $interactionqcm =new InteractionQCM();




                                            //question & category
                                            $question->setTitle($title);
                                            //check if the Category "Import" exist --else-- will create it
                                            $Category_import = $this->getDoctrine()
                                                                 ->getManager()
                                                                 ->getRepository('UJMExoBundle:Category')->findBy(array('value' => "import"));

                                            if(count($Category_import)==0){
                                                $Category->setValue("import");
                                                $Category->setUser($this->container->get('security.context')->getToken()->getUser());
                                                $question->setCategory($Category);
                                            }else{
                                                $question->setCategory($Category_import[0]);
                                            }


                                            $question->setUser($this->container->get('security.context')->getToken()->getUser());
                                            $date = new \Datetime();
                                            $question->setDateCreate(new \Datetime());



                                            //Interaction

                                            $interaction->setType('InteractionQCM');
                                            //strip_tags($res_prompt,'<img><a><p><table>')
                                            $interaction->setInvite(($res_prompt));
                                            $interaction->setQuestion($question);







                                            $em = $this->getDoctrine()->getManager();


                                            $ord=1;
                                            $index =0;
                                            foreach ($choices as $choix) {
                                                //choices
                                                $choice1 = new Choice();
                                                $choice1->setLabel($choix);
                                                $choice1->setOrdre($ord);
                                                foreach ($correctchoices as $corrvalue) {
                                                    $rst= $rst."------------".$identifier_choices[$index]."*--------------------".$corrvalue;
                                                    if(strtolower(trim($identifier_choices[$index])) == strtolower(trim($corrvalue))){
                                                        $rst= $rst."***********".$identifier_choices[$index]."***********".$corrvalue;
                                                        $choice1->setRightResponse(TRUE);
                                                    }
                                                }
                                                $interactionqcm->addChoice($choice1);
                                                $em->persist($choice1);
                                                $ord=$ord+1;
                                                $index=$index+1;
                                            }
                                            //InteractionQCM
                                            $type_qcm = $this->getDoctrine()
                                                        ->getManager()
                                                        ->getRepository('UJMExoBundle:TypeQCM')->findAll();
                                            if($typeqcm=="choice"){
                                                $interactionqcm->setTypeQCM($type_qcm[1]);
                                            }else{
                                                $interactionqcm->setTypeQCM($type_qcm[0]);
                                            }
                                            $interactionqcm->setInteraction($interaction);


                                            $em->persist($interactionqcm);
                                            $em->persist($interactionqcm->getInteraction()->getQuestion());
                                            $em->persist($interactionqcm->getInteraction());

                                            if(count($Category_import)==0){
                                            $em->persist($Category);
                                            }
                                                //echo($choice->getRightResponse());


                                            $em->flush();
                                }else if($typeqcm=="SelectPoint"){
                                            $rst= $rst. "enter";
                                        $responsedaclr = $element->getElementsByTagName("responseDeclaration");
                                        //$responsedaclr = $elements->item(0);
                                        $nodelist = $responsedaclr->item(0);
                                        $correctresponse = $nodelist->getElementsByTagName("correctResponse");

                                        //echo $correctresponse->nodeValue;
                                        //$valeur = $nodelist->getElementByTagName("value");
                                        $areaMapping=$responsedaclr->item(0)->getElementsByTagName("areaMapping");
                                        $areaMapEntry =$areaMapping->item(0)->getElementsByTagName("areaMapEntry");
                                        $shape =$areaMapEntry->item(0)->getAttribute("shape");
                                        $coordstxt=$areaMapEntry->item(0)->getAttribute("coords");
                                        $mappedValue=$areaMapEntry->item(0)->getAttribute("mappedValue");

                                        $itemBody= $element->getElementsByTagName("itemBody");
                                        $selectPointInteraction = $itemBody->item(0)->getElementsByTagName("selectPointInteraction");
                                        $prompt =  $selectPointInteraction->item(0)->getElementsByTagName("prompt");
                                        $object = $selectPointInteraction->item(0)->getElementsByTagName("object");


                                        $type=$object->item(0)->getAttribute("type");
                                        $width=$object->item(0)->getAttribute("width");
                                        $height=$object->item(0)->getAttribute("height");
                                        $data=$object->item(0)->getAttribute("data");

                                        $modalfeedback=null;
                                        if($element->getElementsByTagName("modalFeedback")->item(0)){

                                            $modalfeedback=$element->getElementsByTagName("modalFeedback");
                                        }



                                        $question  = new Question();
                                        $Category = new Category();
                                        $interaction =new Interaction();
                                        $interactiongraphic =new InteractionGraphic();
                                        $coords = new Coords();
                                        $ujmdocument = new Document();



                                        $question->setTitle($title);
                                        $Category_import = $this->getDoctrine()
                                                                 ->getManager()
                                                                 ->getRepository('UJMExoBundle:Category')->findBy(array('value' => "import"));

                                        if(count($Category_import)==0){
                                            $Category->setValue("import");
                                            $Category->setUser($this->container->get('security.context')->getToken()->getUser());
                                            $question->setCategory($Category);
                                        }else{
                                            $question->setCategory($Category_import[0]);
                                        }

                                        $question->setUser($this->container->get('security.context')->getToken()->getUser());
                                        $date = new \Datetime();
                                        $question->setDateCreate(new \Datetime());



                                        $interaction->setType('InteractionGraphic');
                                        //strip_tags($res_prompt,'<img><a><p><table>')
                                        var_dump($modalfeedback->item(0)->nodeValue);
                                        $interaction->setInvite(($prompt->item(0)->nodeValue));
                                        $interaction->setQuestion($question);
                                        $interaction->setFeedBack($modalfeedback->item(0)->nodeValue);

                                        $interactiongraphic->setWidth($width);
                                        $interactiongraphic->setHeight($height);



                                        //list($x,$y,$z) = split('[,]', $coords);
                                        $parts = explode(",", $coordstxt);
                                        $x = $parts[0];
                                        $y = $parts[1];
                                        $z = $parts[2];
                                        $radius = $z * 2;
                                        $x_center=$x - ($radius);
                                        $y_center=$y - ($radius);

                                        $coords->setShape($shape);var_dump($shape);
                                        $coords->setValue($x_center.",".$y_center);
                                        $coords->setSize($radius);
                                        $coords->setColor('white');
                                        $coords->setInteractionGraphic($interactiongraphic);
                                        $coords->setScoreCoords($mappedValue);var_dump($mappedValue);

                                        $user= $this->container->get('security.context')->getToken()->getUser();
                                        $ujmdocument->setUser($user);
                                        $ujmdocument->setLabel($object->item(0)->nodeValue);var_dump($object->item(0)->nodeValue);
                                            //file name of the file
                                            $listpath = explode("/", $data);
                                            $fileName =  $listpath[count($listpath)-1];
                                            $rst=$rst."$fileName=". $fileName;

                                            //extension of the file
                                            $extension = pathinfo($data, PATHINFO_EXTENSION);
                                        //il faut changer le nom de l'image
                                        $ujmdocument->setUrl("./uploads/ujmexo/users_documents/".$user->getUsername()."/images/".$fileName);
                                        $ujmdocument->setType($extension);



                                        $interactiongraphic->setInteraction($interaction);
                                        $interactiongraphic->setDocument($ujmdocument);


                                        $em = $this->getDoctrine()->getManager();

                                        $em->persist($coords->getInteractionGraphic());
                                        $em->persist($ujmdocument);
                                        $em->persist($coords);
                                        $em->persist($coords->getInteractionGraphic()->getInteraction()->getQuestion());
                                        $em->persist($coords->getInteractionGraphic()->getInteraction());

                                        if(count($Category_import)==0){
                                            $em->persist($Category);
                                        }

                                        $em->flush();


                                }else if($typeqcm=="extendedText"){

                                        $responsedaclr = $element->getElementsByTagName("responseDeclaration");
                                        //$responsedaclr = $elements->item(0);
                                        $nodelist = $responsedaclr->item(0);
                                        //$correctresponse = $nodelist->getElementsByTagName("correctResponse");

                                        //echo $correctresponse->nodeValue;
                                        //$valeur = $nodelist->getElementByTagName("value");
                                        $outcomeDeclaration=$element->getElementsByTagName("outcomeDeclaration");
                                        $defaultValue =$outcomeDeclaration->item(0)->getElementsByTagName("defaultValue");
                                        $value =$defaultValue->item(0)->getAttribute("value");



                                        $itemBody= $element->getElementsByTagName("itemBody");

                                        $modalfeedback=null;
                                        if($element->getElementsByTagName("modalFeedback")->item(0)){

                                            $modalfeedback=$element->getElementsByTagName("modalFeedback");
                                        }



                                        $question  = new Question();
                                        $Category = new Category();
                                        $interaction =new Interaction();
                                        $InteractionOpen =new InteractionOpen();



                                        $InteractionOpen->setOrthographyCorrect(0);

                                        $question->setTitle($title);
                                        $Category_import = $this->getDoctrine()
                                                                 ->getManager()
                                                                 ->getRepository('UJMExoBundle:Category')->findBy(array('value' => "import"));

                                        if(count($Category_import)==0){
                                            $Category->setValue("import");
                                            $Category->setUser($this->container->get('security.context')->getToken()->getUser());
                                            $question->setCategory($Category);
                                        }else{
                                            $question->setCategory($Category_import[0]);
                                        }

                                        $question->setUser($this->container->get('security.context')->getToken()->getUser());
                                        $date = new \Datetime();
                                        $question->setDateCreate(new \Datetime());



                                        $interaction->setType('InteractionOpen');
                                        //strip_tags($res_prompt,'<img><a><p><table>')

                                        $interaction->setInvite(($itemBody->item(0)->nodeValue));
                                        $interaction->setQuestion($question);
                                        $interaction->setFeedBack($modalfeedback->item(0)->nodeValue);


                                        $user= $this->container->get('security.context')->getToken()->getUser();


                                        $InteractionOpen->setInteraction($interaction);


                                        $em = $this->getDoctrine()->getManager();

                                        $em->persist($InteractionOpen);
                                        $em->persist($question);
                                        $em->persist($interaction);

                                        if(count($Category_import)==0){
                                            $em->persist($Category);
                                        }

                                        $em->flush();


                                }else if($typeqcm=="entrytext"){




                                                //import entrytext question "Question à trou"


                                }


                  }


               /*
                foreach($childs as $enfant) // On prend chaque nœud enfant séparément
                {

                    /*   //$value = $enfant->nodeValue;
                      $nom = $enfant->nodeName; // On prend le nom de chaque nœud
                      $rst =$rst . $nom."<br/>".$value."</br>";
                    if($enfant->hasChildNodes() == true){
                        $childs_level2 = $enfant->childNodes;
                        foreach($childs_level2 as $enfant_l2) // On prend chaque nœud enfant séparément
                        {
                            $enfant_l2->
                            $value = $enfant_l2->nodeValue;
                            $nom = $enfant_l2->nodeName; // On prend le nom de chaque nœud
                            $rst =$rst . $nom."<br/>".$value."</br>";
                        }
                    }


                }   return $this->render('UJMExoBundle:Question:index.html.twig');

                */

                $this->removeDirectory($userDir);
                $response = $this->forward('UJMExoBundle:Question:index', array());

                return $response;
//                return $this->render(
//                      'UJMExoBundle:Question:index.html.twig', array(
//                      'rst' => $rst,
//                      )
//                );


    }
        //suppression les dossiers uploader
        //All Files	/var/www/Claroline/web/uploadfiles
        public function removeDirectory($directory){
                if(!is_dir($directory)){
                    throw new $this->createNotFoundException($directory.' is not directory '.__LINE__.', file '.__FILE__);
                }
                    $iterator = new \DirectoryIterator($directory);
                        foreach ($iterator as $fileinfo) {

                            if (!$fileinfo->isDot()) {
                                if($fileinfo->isFile()) {
                                    unlink($directory."/".$fileinfo->getFileName());

                                }
                            }
                        }//end foreach
                    //rmdir($directory);
        }
}
