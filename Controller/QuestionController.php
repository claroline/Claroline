<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use UJM\ExoBundle\Entity\InteractionGraphic;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Entity\Share;
use UJM\ExoBundle\Form\QuestionType;

/**
 * Question controller.
 */
class QuestionController extends Controller
{
    /**
     * Lists the User's Question entities.
     *
     *
     * @param int    $pageNow        for the pagination : actual page of my questions list
     * @param int    $pageNowShared  for the pagination : actual page of my shared questions list
     * @param string $categoryToFind used for pagination (for example after creating a question, go back to page contaning this question)
     * @param string $titleToFind    used for pagination (for example after creating a question, go back to page contaning this question)
     * @param int    $id             resource id if the bank has acceded by an exercise
     * @param bool   $displayAll     to use pagination or not
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($pageNow = 0, $pageNowShared = 0, $categoryToFind = '', $titleToFind = '', $resourceId = -1, $displayAll = 0)
    {
        if (base64_decode($categoryToFind)) {
            $categoryToFind = base64_decode($categoryToFind);
            $titleToFind = base64_decode($titleToFind);
        }
        $vars = array();
        $sharedWithMe = array();
        $shareRight = array();
        $questionWithResponse = array();
        $alreadyShared = array();

        $em = $this->getDoctrine()->getManager();

        $services = $this->container->get('ujm.exo_question');
        $paginationSer = $this->container->get('ujm.exo_pagination');

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
        } elseif ($click == 'shared') {
            // The choosen new page is for my shared questions array
            $pagerShared = $page;
        }

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $uid = $user->getId();

        $questions = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Question')
            ->findByUser($user);

        foreach ($questions as $question) {
            $actions = $services->getActionQuestion($question);
            $questionWithResponse += $actions[0];
            $alreadyShared += $actions[1];
        }

        $shared = $em->getRepository('UJMExoBundle:Share')
            ->findBy(array('user' => $uid));

        foreach ($shared as $s) {
            $actionsS = $services->getActionShared($s);
            $sharedWithMe += $actionsS[0];
            $shareRight += $actionsS[1];
            $questionWithResponse += $actionsS[2];
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

        if ($displayAll == 1) {
            if (count($questions) > count($shared)) {
                $max = count($questions);
            } else {
                $max = count($shared);
            }
        }

        $doublePagination = $paginationSer->doublePaginationWithIf($questions, $sharedWithMe, $max, $pagerMy, $pagerShared, $pageNow, $pageNowShared);

        $interactionsPager = $doublePagination[0];
        $pagerfantaMy = $doublePagination[1];

        $sharedWithMePager = $doublePagination[2];
        $pagerfantaShared = $doublePagination[3];

        $listExo = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:Exercise')
                        ->getExerciseAdmin($uid);

        $interactionType = $this->container->get('ujm.exo_question')->getTypes();

        $vars['pagerMy'] = $pagerfantaMy;
        $vars['pagerShared'] = $pagerfantaShared;
        $vars['interactions'] = $interactionsPager;
        $vars['sharedWithMe'] = $sharedWithMePager;
        $vars['questionWithResponse'] = $questionWithResponse;
        $vars['alreadyShared'] = $alreadyShared;
        $vars['shareRight'] = $shareRight;
        $vars['displayAll'] = $displayAll;
        $vars['listExo'] = $listExo;
        $vars['idExo'] = -1;
        $vars['QuestionsExo'] = 'false';
        $vars['interactionType'] = $interactionType;

        if ($request->get('qtiError')) {
            $vars['qtiError'] = $request->get('qtiError');
        }

        return $this->render('UJMExoBundle:Question:index.html.twig', $vars);
    }

    /**
     * To filter question by exercise.
     *
     *
     * @param int $idExo id of exercise selected in the list to filter questions
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bankFilterAction($idExo = -1)
    {
        $vars = array();
        $shareRight = array();
        $questionWithResponse = array();
        $alreadyShared = array();

        $em = $this->getDoctrine()->getManager();

        $services = $this->container->get('ujm.exo_question');

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $uid = $user->getId();

        $actionQ = array();

        if ($idExo == -2) {
            $listQExo = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Question')
                ->findByUser($user, true);
        } else {
            $exercise = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Exercise')
                ->find($idExo);
            $listQExo = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Question')
                ->findByExercise($exercise);
        }

        $allActions = $services->getActionsAllQuestions($listQExo, $uid);

        $actionQ = $allActions[0];
        $questionWithResponse = $allActions[1];
        $alreadyShared = $allActions[2];
        $shareRight = $allActions[4];
        $interactionType = $this->container->get('ujm.exo_question')->getTypes();

        $listExo = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:Exercise')
                        ->getExerciseAdmin($uid);

        $vars['interactions'] = $listQExo;
        $vars['actionQ'] = $actionQ;
        $vars['questionWithResponse'] = $questionWithResponse;
        $vars['alreadyShared'] = $alreadyShared;
        $vars['shareRight'] = $shareRight;
        $vars['displayAll'] = 0;
        $vars['listExo'] = $listExo;
        $vars['idExo'] = $idExo;
        $vars['QuestionsExo'] = 'true';
        $vars['interactionType'] = $interactionType;

        return $this->render('UJMExoBundle:Question:index.html.twig', $vars);
    }

    /**
     * Finds and displays a Question entity.
     *
     *
     * @param int $id    id Question
     * @param int $exoID id Exercise if the user is in an exercise, -1 if the user is in the question bank
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($id, $exoID)
    {
        $vars = array();
        $allowToAccess = 0;
        $questionSer = $this->container->get('ujm.exo_question');
        $em = $this->getDoctrine()->getManager();

        if ($exoID != -1) {
            $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
            $vars['_resource'] = $exercise;

            if ($this->container->get('ujm.exo_exercise')
                     ->isExerciseAdmin($exercise)) {
                $allowToAccess = 1;
            }
        }

        $question = $questionSer->controlUserQuestion($id);
        $sharedQuestion = $questionSer->controlUserSharedQuestion($id);

        if ($question || count($sharedQuestion) > 0 || $allowToAccess == 1) {
            $interaction = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Question')
                ->find($id);

            return $this->forward(
                'UJMExoBundle:'.$interaction->getType().':show', array('interaction' => $interaction, 'exoID' => $exoID, 'vars' => $vars)
            );
        } else {
            return $this->redirect($this->generateUrl('ujm_question_index'));
        }
    }

    /**
     * Displays a form to create a new Question entity with interaction.
     *
     *
     * @param int $exoID id Exercise if the user is in an exercise, -1 if the user is in the question bank
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction($exoID)
    {
        $catSer = $this->container->get('ujm.exo_category');
        $variables = array(
            'exoID' => $exoID,
            'linkedCategory' => $catSer->getLinkedCategories(),
            'locker' => $catSer->getLockCategory(),
        );

        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);

        $interactionType = $this->container->get('ujm.exo_question')->getTypes();
        $variables['interactionType'] = $interactionType;

        if ($exercise) {
            $variables['_resource'] = $exercise;
        }

        return $this->render('UJMExoBundle:Question:new.html.twig', $variables);
    }

    /**
     * Creates a new Question entity.
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
//    public function createAction()
//    {
//        $entity  = new Question();
//        $request = $this->getRequest();
//        $form    = $this->createForm(new QuestionType(), $entity);
//        $form->handleRequest($request);
//
//        if ($form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($entity);
//            $em->flush();
//
//            return $this->redirect($this->generateUrl('question_show', array('id' => $entity->getId())));
//        }
//
//        return $this->render(
//            'UJMExoBundle:Question:new.html.twig', array(
//            'entity' => $entity,
//            'form'   => $form->createView(),
//            'linkedCategory' =>  $this->container->get('ujm.exo_question')->getLinkedCategories(),
//            'locker' => $this->getLockCategory(),
//            )
//        );
//    }

    /**
     * Displays a form to edit an existing Question entity.
     *
     *
     * @param int $id    id Question
     * @param int $exoID id Exercise if the user is in an exercise, -1 if the user is in the question bank
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id, $exoID)
    {
        $services = $this->container->get('ujm.exo_question');
        $question = $services->controlUserQuestion($id);
        $share = $services->controlUserSharedQuestion($id);
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $catID = -1;

        if (count($share) > 0) {
            $shareAllowEdit = $share[0]->getAllowToModify();
        }

        if ($question || $shareAllowEdit) {
            if ($user->getId() != $question->getUser()->getId()) {
                $catID = $question->getCategory()->getId();
            }

            return $this->forward(
                'UJMExoBundle:'.$question->getType().':edit',
                array('interaction' => $question, 'exoID' => $exoID, 'catID' => $catID, 'user' => $user)
            );
        } else {
            return $this->redirect($this->generateUrl('ujm_question_index'));
        }
    }

    /**
     * Deletes a Question entity.
     *
     *
     * @param int $id       id Question
     * @param int $pageNow  actual page for the pagination
     * @param int $maxpage  number max questions per page
     * @param int $nbItem   number of question
     * @param int $lastPage number of last page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id, $pageNow, $maxPage, $nbItem, $lastPage)
    {
        $em = $this->getDoctrine()->getManager();
        $question = $this->container->get('ujm.exo_question')->controlUserQuestion($id);

        if ($question) {
            $eq = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:ExerciseQuestion')
                ->getExercises($id);

            foreach ($eq as $e) {
                $em->remove($e);
            }

            $em->flush();

             // If delete last item of page, display the previous one
            $rest = $nbItem % $maxPage;

            if ($rest == 1 && $pageNow == $lastPage) {
                $pageNow -= 1;
            }

            $interSer = $this->container->get('ujm.exo_'.$question->getType());
            $interX = $interSer->getInteractionX($question->getId());

            return $this->forward(
                'UJMExoBundle:'.$question->getType().':delete',
                array('id' => $interX->getId(), 'pageNow' => $pageNow)
            );
        }
    }

    /**
     * Displays the rigth form when a teatcher wants to create a new Question (JS).
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formNewAction()
    {
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            $valType = $request->request->get('indice_type');
            $exoID = $request->request->get('exercise');

            return $this->forward(
                       'UJMExoBundle:'.$valType.':new', array('exoID' => $exoID)
                    );
        }
    }

    /**
     * To share Question.
     *
     *
     * @param int $questionID id of question
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shareAction($questionID)
    {
        return $this->render(
            'UJMExoBundle:Question:share.html.twig', array(
            'questionID' => $questionID,
            )
        );
    }

    /**
     * To search Question.
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction()
    {
        $request = $this->get('request');
        $paginationSer = $this->container->get('ujm.exo_pagination');

        $max = 10; // Max per page

        $search = $request->query->get('search');
        $page = $request->query->get('page');
        $questionID = $request->query->get('qId');

        if ($search != '') {
            $em = $this->getDoctrine()->getManager();
            $userList = $em->getRepository('ClarolineCoreBundle:User')->findByName($search);

            $pagination = $paginationSer->pagination($userList, $max, $page);

            $userListPager = $pagination[0];
            $pagerUserSearch = $pagination[1];

            // Put the result in a twig
            $divResultSearch = $this->render(
                'UJMExoBundle:Question:search.html.twig', array(
                'userList' => $userListPager,
                'pagerUserSearch' => $pagerUserSearch,
                'search' => $search,
                'questionID' => $questionID,
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
                    'questionID' => $questionID,
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
     * To manage the User's documents.
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageDocAction()
    {
        $allowToDel = array();
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $request = $this->get('request');
        $paginationSer = $this->container->get('ujm.exo_pagination');

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
                $allowToDel[$doc->getId()] = false;
            } else {
                $allowToDel[$doc->getId()] = true;
            }
        }

        // Pagination of the documents
        $max = 10; // Max questions displayed per page

        $page = $request->query->get('page', 1); // Which page

        $pagination = $paginationSer->pagination($listDoc, $max, $page);

        $listDocPager = $pagination[0];
        $pagerDoc = $pagination[1];

        return $this->render(
            'UJMExoBundle:Document:manageImg.html.twig',
            array(
                'listDoc' => $listDocPager,
                'pagerDoc' => $pagerDoc,
                'allowToDel' => $allowToDel,
            )
        );
    }

    /**
     * To delete a User's document.
     *
     *
     * @param int $idDoc id Document
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
     * To delete a User's document linked to questions but not to paper.
     *
     * @param string $label label of document
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deletelinkedDocAction($label)
    {
        $userId = $this->container->get('security.token_storage')->getToken()->getUser()->getId();

        $repositoryDoc = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Document');

        $listDoc = $repositoryDoc->findByLabel($label, $userId, 0);

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UJMExoBundle:InteractionGraphic')->findBy(array('document' => $listDoc));

        $end = count($entity);

        for ($i = 0; $i < $end; ++$i) {
            $coords = $em->getRepository('UJMExoBundle:Coords')->findBy(array('interactionGraphic' => $entity[$i]->getId()));

            if (!$coords) {
                throw $this->createNotFoundException('Unable to find Coords link to interactiongraphic.');
            }

            $stop = count($coords);
            for ($x = 0; $x < $stop; ++$x) {
                $em->remove($coords[$x]);
            }

            $em->remove($entity[$i]);
        }

        $em->remove($listDoc[0]);
        $em->flush();

        return $this->redirect($this->generateUrl('ujm_question_manage_doc'));
    }

    /**
     * To display the modal which allow to change the label of a document.
     *
     *
     * @param int $id id of exercise
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
     * To change the label of a document.
     *
     *
     * @param int $id id of exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateNameAction()
    {
        $request = $this->container->get('request');
        $newlabel = $request->get('newlabel');
        $oldlabel = $request->get('oldName');

        $em = $this->getDoctrine()->getManager();

        $alterDoc = $em->getRepository('UJMExoBundle:Document')->findOneBy(array('label' => $oldlabel));

        $alterDoc->setLabel($newlabel);

        $em->persist($alterDoc);
        $em->flush();

        return new \Symfony\Component\HttpFoundation\Response($newlabel);
    }

    /**
     * To sort document by type.
     *
     *
     * @param int $id id of exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sortDocumentsAction()
    {
        $request = $this->container->get('request');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $paginationSer = $this->container->get('ujm.exo_pagination');

        $max = 10; // Max per page

        $type = $request->query->get('doctype');
        $searchLabel = $request->query->get('searchLabel');
        $page = $request->query->get('page');

        if ($type && isset($searchLabel)) {
            $repository = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Document');

            $listDocSort = $repository->findByType($type, $user->getId(), $searchLabel);

            $pagination = $paginationSer->pagination($listDocSort, $max, $page);

            $listDocSortPager = $pagination[0];
            $pagerSortDoc = $pagination[1];

            // Put the result in a twig
            $divResultSearch = $this->render(
                'UJMExoBundle:Document:sortDoc.html.twig', array(
                'listFindDoc' => $listDocSortPager,
                'pagerFindDoc' => $pagerSortDoc,
                'labelToFind' => $searchLabel,
                'whichAction' => 'sort',
                'doctype' => $type,
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
                    'divResultSearch' => $divResultSearch,
                    )
                );
            }
        } else {
            return $this->render(
                'UJMExoBundle:Document:sortDoc.html.twig', array(
                'listFindDoc' => '',
                'whichAction' => 'sort',
                )
            );
        }
    }

    /**
     * To search document with a defined label.
     *
     *
     * @param int $id id of exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchDocAction()
    {
        $userId = $this->container->get('security.token_storage')->getToken()->getUser()->getId();
        $request = $this->get('request');
        $paginationSer = $this->container->get('ujm.exo_pagination');

        $max = 10; // Max per page

        $labelToFind = $request->query->get('labelToFind');
        $page = $request->query->get('page');

        if ($labelToFind) {
            $em = $this->getDoctrine()->getManager();
            $listFindDoc = $em->getRepository('UJMExoBundle:Document')->findByLabel($labelToFind, $userId, 1);

            $pagination = $paginationSer->pagination($listFindDoc, $max, $page);

            $listFindDocPager = $pagination[0];
            $pagerFindDoc = $pagination[1];

            // Put the result in a twig
            $divResultSearch = $this->render(
                'UJMExoBundle:Document:sortDoc.html.twig', array(
                'listFindDoc' => $listFindDocPager,
                'pagerFindDoc' => $pagerFindDoc,
                'labelToFind' => $labelToFind,
                'whichAction' => 'search',
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
                    'divResultSearch' => $divResultSearch,
                    )
                );
            }
        } else {
            return $this->render(
                'UJMExoBundle:Document:sortDoc.html.twig', array(
                'listFindDoc' => '',
                'whichAction' => 'search',
                )
            );
        }
    }

    /**
     * To share question with other users.
     *
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
            $user = $em->getRepository('ClarolineCoreBundle:User')->find($uid);

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
     * If question already shared with a given user.
     *
     *
     * @param \UJM\ExoBundle\Entity\Share $toShare
     * @param Doctrine Entity Manager     $em
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function alreadySharedAction($toShare, $em)
    {
        $alreadyShared = $em->getRepository('UJMExoBundle:Share')->findAll();
        $already = false;

        $end = count($alreadyShared);

        for ($i = 0; $i < $end; ++$i) {
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
     * Display form to search questions.
     *
     *
     * @param int $exoID id of exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchQuestionAction($exoID)
    {
        return $this->render('UJMExoBundle:Question:searchQuestion.html.twig', array(
            'exoID' => $exoID,
            )
        );
    }

    /**
     * Display the questions matching to the research.
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchQuestionTypeAction()
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $request = $this->get('request');

        $paginationSer = $this->container->get('ujm.exo_pagination');
        
        $listQuestions = array();
        $questionWithResponse = array();
        $alreadyShared = array();

        $max = 10; // Max questions displayed per page

        $type = $request->query->get('type'); // In which column
        $whatToFind = $request->query->get('whatToFind'); // Which text to find
        $where = $request->query->get('where'); // In which database
        $page = $request->query->get('page'); // Which page
        $exoID = $request->query->get('exoID'); // If we import or see the questions
        $displayAll = $request->query->get('displayAll', 0); // If we want to have all the questions in one page

        // If what and where to search is defined
        if ($type && $whatToFind && $where) {
            $em = $this->getDoctrine()->getManager();
            $questionRepository = $em->getRepository('UJMExoBundle:Question');

            // Get the matching questions depending on :
            //  * in which database search,
            //  * in witch column
            //  * and what text to search

            // User's database
            if ($where == 'my') {
                switch ($type) {
                    case 'Category':
                        $listQuestions = $questionRepository->findByUserAndCategoryName($user, $whatToFind);
                        break;
                    case 'Type':
                        $listQuestions = $questionRepository->findByUserAndType($user, $whatToFind);
                        break;
                    case 'Title':
                        $listQuestions = $questionRepository->findByUserAndTitle($user, $whatToFind);
                        break;
                    case 'Contain':
                        $listQuestions = $questionRepository->findByUserAndInvite($user, $whatToFind);
                        break;
                    case 'All':
                        $listQuestions = $questionRepository->findByUserAndContent($user, $whatToFind);
                        break;
                }

                // For all the matching questions search if ...
                foreach ($listQuestions as $question) {
                    // ... the question is link to a paper (question in the test has already been passed)
                    $response = $em->getRepository('UJMExoBundle:Response')
                        ->findOneByQuestion($question);

                    if ($response) {
                        $questionWithResponse[$question->getId()] = 1;
                    } else {
                        $questionWithResponse[$question->getId()] = 0;
                    }

                    // ...the question is shared or not
                    $share = $em->getRepository('UJMExoBundle:Share')
                        ->findOneByQuestion($question);

                    if ($share) {
                        $alreadyShared[$question->getId()] = 1;
                    } else {
                        $alreadyShared[$question->getId()] = 0;
                    }
                }

                if ($exoID == -1) {
                    if ($displayAll == 1) {
                        $max = count($listQuestions);
                    }

                    $pagination = $paginationSer->pagination($listQuestions, $max, $page);
                } else {
                    $exoQuestions = $em->getRepository('UJMExoBundle:ExerciseQuestion')->findBy(array('exercise' => $exoID));

                    $finalList = array();
                    $length = count($listQuestions);
                    $already = false;

                    for ($i = 0; $i < $length; ++$i) {
                        foreach ($exoQuestions as $exoQuestion) {
                            if ($exoQuestion->getQuestion()->getId() == $listQuestions[$i]->getId()) {
                                $already = true;
                                break;
                            }
                        }
                        if ($already == false) {
                            $finalList[] = $listQuestions[$i];
                        }
                        $already = false;
                    }

                    if ($displayAll == 1) {
                        $max = count($finalList);
                    }

                    $pagination = $paginationSer->pagination($finalList, $max, $page);
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
                        'type' => $type,
                        'whatToFind' => $whatToFind,
                        'questionWithResponse' => $questionWithResponse,
                        'alreadyShared' => $alreadyShared,
                        'exoID' => $exoID,
                        'displayAll' => $displayAll,
                        )
                    );
                } else {
                    $divResultSearch = $this->render(
                        'UJMExoBundle:Question:searchQuestionImport.html.twig', array(
                            'listQuestions' => $listQuestionsPager,
                            'pagerSearch' => $pagerSearch,
                            'exoID' => $exoID,
                            'canDisplay' => $where,
                            'whatToFind' => $whatToFind,
                            'type' => $type,
                            'displayAll' => $displayAll,
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
                        'exoID' => $exoID,
                        )
                    );
                }
            // Shared with user's database
            } elseif ($where == 'shared') {
                switch ($type) {
                    case 'Category':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByCategoryShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listQuestions[] = $sharedQuestion[$i]->getQuestion();
                        }

                        break;
                    case 'Type':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByTypeShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listQuestions[] = $sharedQuestion[$i]->getQuestion();
                        }

                        break;
                    case 'Title':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByTitleShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listQuestions[] = $sharedQuestion[$i]->getQuestion();
                        }

                        break;
                    case 'Contain':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByContainShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listQuestions[] = $sharedQuestion[$i]->getQuestion();
                        }

                        break;
                    case 'All':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByAllShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listQuestions[] = $sharedQuestion[$i]->getQuestion();
                        }

                        break;
                }

                if ($exoID == -1) {
                    if ($displayAll == 1) {
                        $max = count($listQuestions);
                    }

                    $pagination = $paginationSer->pagination($listQuestions, $max, $page);
                } else {
                    $exoQuestions = $em->getRepository('UJMExoBundle:ExerciseQuestion')->findBy(array('exercise' => $exoID));

                    $finalList = array();
                    $length = count($listQuestions);
                    $already = false;

                    for ($i = 0; $i < $length; ++$i) {
                        foreach ($exoQuestions as $exoQuestion) {
                            if ($exoQuestion->getQuestion()->getId() == $listQuestions[$i]->getId()) {
                                $already = true;
                                break;
                            }
                        }
                        if ($already == false) {
                            $finalList[] = $listQuestions[$i];
                        }
                        $already = false;
                    }

                    if ($displayAll == 1) {
                        $max = count($finalList);
                    }

                    $pagination = $paginationSer->pagination($finalList, $max, $page);
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
                        'type' => $type,
                        'whatToFind' => $whatToFind,
                        'exoID' => $exoID,
                        'displayAll' => $displayAll,
                        )
                    );
                } else {
                    $divResultSearch = $this->render(
                        'UJMExoBundle:Question:searchQuestionImport.html.twig', array(
                            'listQuestions' => $listQuestionsPager,
                            'pagerSearch' => $pagerSearch,
                            'exoID' => $exoID,
                            'canDisplay' => $where,
                            'whatToFind' => $whatToFind,
                            'type' => $type,
                            'displayAll' => $displayAll,
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
                        'exoID' => $exoID,
                        )
                    );
                }
            } elseif ($where == 'all') {
                switch ($type) {
                    case 'Category':
                        $listQuestions = $questionRepository->findByUserAndCategoryName($user, $whatToFind);
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByCategoryShared($user->getId(), $whatToFind);

                        $ends = count($sharedQuestion);

                        for ($i = 0; $i < $ends; $i++) {
                            $listQuestions[] = $sharedQuestion[$i]->getQuestion();
                        }

                        break;
                    case 'Type':
                        $listQuestions = $questionRepository->findByUserAndType($user, $whatToFind);
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByTypeShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listQuestions[] = $sharedQuestion[$i]->getQuestion();
                        }

                        break;
                    case 'Title':
                        $listQuestions = $questionRepository->findByUserAndTitle($user, $whatToFind);
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByTitleShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listQuestions[] = $sharedQuestion[$i]->getQuestion();
                        }

                        break;
                    case 'Contain':
                        $listQuestions = $questionRepository->findByUserAndInvite($user, $whatToFind);
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByContainShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listQuestions[] = $sharedQuestion[$i]->getQuestion();
                        }

                        break;
                    case 'All':
                        $listQuestions = $questionRepository->findByUserAndContent($user, $whatToFind);
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByAllShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listQuestions[] = $sharedQuestion[$i]->getQuestion();
                        }

                        break;
                }

                // For all the matching interactions search if ...
                foreach ($listQuestions as $question) {
                    // ... the interaction is link to a paper (interaction in the test has already been passed)
                    $response = $em->getRepository('UJMExoBundle:Response')
                        ->findOneByQuestion($question);

                    if ($response) {
                        $questionWithResponse[$question->getId()] = 1;
                    } else {
                        $questionWithResponse[$question->getId()] = 0;
                    }

                    // ...the interaction is shared or not
                    $share = $em->getRepository('UJMExoBundle:Share')
                        ->findOneByQuestion($question);

                    if ($share) {
                        $alreadyShared[$question->getId()] = 1;
                    } else {
                        $alreadyShared[$question->getId()] = 0;
                    }
                }

                if ($exoID == -1) {
                    if ($displayAll == 1) {
                        $max = count($listQuestions);
                    }

                    $pagination = $paginationSer->pagination($listQuestions, $max, $page);
                } else {
                    $exoQuestions = $em->getRepository('UJMExoBundle:ExerciseQuestion')->findBy(array('exercise' => $exoID));

                    $finalList = array();
                    $length = count($listQuestions);
                    $already = false;

                    for ($i = 0; $i < $length; ++$i) {
                        foreach ($exoQuestions as $exoQuestion) {
                            if ($exoQuestion->getQuestion()->getId() == $listQuestions[$i]->getId()) {
                                $already = true;
                                break;
                            }
                        }
                        if ($already == false) {
                            $finalList[] = $listQuestions[$i];
                        }
                        $already = false;
                    }

                    if ($displayAll == 1) {
                        $max = count($finalList);
                    }

                    $pagination = $paginationSer->pagination($finalList, $max, $page);
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
                        'type' => $type,
                        'whatToFind' => $whatToFind,
                        'questionWithResponse' => $questionWithResponse,
                        'alreadyShared' => $alreadyShared,
                        'exoID' => $exoID,
                        'displayAll' => $displayAll,
                        )
                    );
                } else {
                    $divResultSearch = $this->render(
                        'UJMExoBundle:Question:searchQuestionImport.html.twig', array(
                            'listQuestions' => $listQuestionsPager,
                            'pagerSearch' => $pagerSearch,
                            'exoID' => $exoID,
                            'canDisplay' => $where,
                            'whatToFind' => $whatToFind,
                            'type' => $type,
                            'displayAll' => $displayAll,
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
                        'exoID' => $exoID,
                        )
                    );
                }
            }
        } else {
            return $this->render(
                'UJMExoBundle:Question:SearchQuestionType.html.twig', array(
                'listQuestions' => '',
                'canDisplay' => $where,
                'whatToFind' => $whatToFind,
                'type' => $type,
                )
            );
        }
    }

    /**
     * To delete the shared question of user's questions bank.
     *
     *
     * @param int $qid      id Question
     * @param int $uid      id User, user connected
     * @param int $pageNow  actual page for the pagination
     * @param int $maxpage  number max questions per page
     * @param int $nbItem   number of question
     * @param int $lastPage number of last page
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
     * To see with which person the user has shared his question.
     *
     *
     * @param int $id id of question
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function seeSharedWithAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $questionsharedWith = $em->getRepository('UJMExoBundle:Share')->findBy(array('question' => $id));

        $sharedWith = array();
        $stop = count($questionsharedWith);

        for ($i = 0; $i < $stop; ++$i) {
            $sharedWith[] = $em->getRepository('ClarolineCoreBundle:User')->find($questionsharedWith[$i]->getUser()->getId());
        }

        return $this->render(
            'UJMExoBundle:Question:seeSharedWith.html.twig', array(
            'sharedWith' => $sharedWith,
            )
        );
    }

    /**
     * To search questions brief in the question bank.
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function briefSearchAction()
    {
        $em = $this->getDoctrine()->getManager();
        $questionRepository = $em->getRepository('UJMExoBundle:Question');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $request = $this->get('request');
        $services = $this->container->get('ujm.exo_question');

        $userSearch = $request->request->get('userSearch');
        $exoID = $request->request->get('exoID');
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
        $where = $request->request->get('where');

        $listQuestions = $questionRepository->findByUserAndContent($user, $userSearch, $exercise);
        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
            ->findByAllShared($user->getId(), $userSearch);

        $end = count($sharedQuestion);

        for ($i = 0; $i < $end; $i++) {
            $listQuestions[] = $sharedQuestion[$i]->getQuestion();
        }

        $allActions = $services->getActionsAllQuestions($listQuestions, $user->getId());

        $actionQ = $allActions[0];
        $questionWithResponse = $allActions[1];
        $alreadyShared = $allActions[2];
        $shareRight = $allActions[4];
        $interactionType = $this->container->get('ujm.exo_question')->getTypes();

        $listExo = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Exercise')
                    ->getExerciseAdmin($user->getId());

        $vars['interactions'] = $listQuestions;
        $vars['actionQ'] = $actionQ;
        $vars['questionWithResponse'] = $questionWithResponse;
        $vars['alreadyShared'] = $alreadyShared;
        $vars['shareRight'] = $shareRight;
        $vars['listExo'] = $listExo;
        $vars['idExo'] = -1;
        $vars['displayAll'] = 0;
        $vars['QuestionsExo'] = 'true';
        $vars['interactionType'] = $interactionType;

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
    }

    /**
     * To duplicate a question.
     *
     * @param int $questionId   id Question
     * @param int $exoID        id Exercise if the user is in an exercise, -1 if the user is in the question bank
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function duplicateAction($questionId, $exoID)
    {
        $exercise = null;
        $service = $this->container->get('ujm.exo_question');

        $question = $service->controlUserQuestion($questionId);
        $sharedQuestions = $service->controlUserSharedQuestion($questionId);
        $allowToAccess = false;

        if ($exoID != -1) {
            $exercise = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Exercise')->find($exoID);

            if ($this->container->get('ujm.exo_exercise')
                     ->isExerciseAdmin($exercise) === true) {
                $allowToAccess = true;
            }
        }

        if ($question && (count($sharedQuestions) > 0 || $allowToAccess)) {
            $type = $question->getType();
            $handlerType = '\UJM\ExoBundle\Form\\'.$type.'Handler';

            $interactionX = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:'.$type)
                ->findOneByQuestion($question);

            $interXHandler = new $handlerType(
                        null, null, $this->getDoctrine()->getManager(),
                        $this->container->get('ujm.exo_exercise'),
                        $this->container->get('security.token_storage')->getToken()->getUser(), $exercise,
                        $this->get('translator')
                    );

            $interXHandler->singleDuplicateInter($interactionX);

            $categoryToFind = $interactionX->getQuestion()->getCategory();
            $titleToFind = $interactionX->getQuestion()->getTitle();

            if ($exoID == -1) {
                return $this->redirect(
                    $this->generateUrl('ujm_question_index', array(
                        'categoryToFind' => base64_encode($categoryToFind), 'titleToFind' => base64_encode($titleToFind), )
                    )
                );
            } else {
                return $this->redirect(
                    $this->generateUrl('ujm_exercise_questions', array(
                        'id' => $exoID, 'categoryToFind' => $categoryToFind, 'titleToFind' => $titleToFind, )
                    )
                );
            }
        } else {
            return $this->redirect($this->generateUrl('ujm_question_index'));
        }
    }
}
