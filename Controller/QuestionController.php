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
use Symfony\Component\Form\FormBuilder;
//use Symfony\Component\HttpFoundation\Response;


use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Form\QuestionType;

use UJM\ExoBundle\Entity\InteractionQCM;
use UJM\ExoBundle\Form\InteractionQCMType;

use UJM\ExoBundle\Entity\InteractionGraphic;
use UJM\ExoBundle\Form\InteractionGraphicType;

use UJM\ExoBundle\Entity\InteractionOpen;
use UJM\ExoBundle\Form\InteractionOpenType;

use UJM\ExoBundle\Entity\InteractionHole;
use UJM\ExoBundle\Form\InteractionHoleType;

use UJM\ExoBundle\Entity\Interaction;
use UJM\ExoBundle\Entity\Share;

use UJM\ExoBundle\Entity\Response;
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
     */
    public function indexAction($pageNow = 0, $category2Find = '', $title2Find = '')
    {
        // To paginate the result :
        $request = $this->get('request'); // Get the request which contains the following parameters :
        $page = $request->query->get('page', 1); // Get the choosen page (default 1)
        $click = $request->query->get('click', 'my'); // Get which array to fchange page (default 'my question')
        $pagerMy = $request->query->get('pagerMy', 1); // Get the page of the array my question (default 1)
        $pagerShared = $request->query->get('pagerShared', 1); // Get the pager of the array my shared question (default 1)
        $max = 3; // Max of questions per page

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
        $Interactions = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Interaction')
            ->getUserInteraction($uid);

        $questionWithResponse = array();
        $alreadyShared = array();
        $em = $this->getDoctrine()->getManager();

        foreach ($Interactions as $interaction) {
            $response = $em->getRepository('UJMExoBundle:Response')
                ->findBy(array('interaction' => $interaction->getId()));
            if (count($response) > 0) {
                $questionWithResponse[] = 1;
            } else {
                $questionWithResponse[] = 0;
            }

            $share = $em->getRepository('UJMExoBundle:Share')
                ->findBy(array('question' => $interaction->getQuestion()->getId()));
            if (count($share) > 0) {
                $alreadyShared[] = 1;
            } else {
                $alreadyShared[] = 0;
            }
        }

        $shared = $em->getRepository('UJMExoBundle:Share')
            ->findBy(array('user' => $uid));

        $SharedWithMe = array();

        for ($i = 0; $i < count($shared); $i++) {
            $SharedWithMe[] = $em->getRepository('UJMExoBundle:Interaction')
                ->findOneBy(array('question' => $shared[$i]->getQuestion()->getId()));
        }

        if ($category2Find != '' && $title2Find != '' && $category2Find != 'z' && $title2Find != 'z') {
            $i = 1 ; $pos = 0 ; $temp = 0;
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

        // Do the pagination of the result depending on which page of which array was changed
        // (My questions array)
        $adapterMy = new ArrayAdapter($Interactions);
        $pagerfantaMy = new Pagerfanta($adapterMy);

        // (My shared questions array)
        $adapterShared = new ArrayAdapter($SharedWithMe);
        $pagerfantaShared = new Pagerfanta($adapterShared);

        try {
            if ($pageNow == 0) {
                // Test if my questions array exists (try) and affects the matching results (which page, how many per page ...)
                $interactions = $pagerfantaMy
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pagerMy)
                    ->getCurrentPageResults()
                ;
            } else {
                $interactions = $pagerfantaMy
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pageNow)
                    ->getCurrentPageResults()
                ;
            }

            // Test if my shared questions array exists (try) and affects the matching results (which page, how many per page ...)
            $sharedWithMe = $pagerfantaShared
                ->setMaxPerPage($max)
                ->setCurrentPage($pagerShared)
                ->getCurrentPageResults()
            ;
        } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
            // If page don't exist
            throw $this->createNotFoundException("Cette page n'existe pas.");
        }

        return $this->render(
            'UJMExoBundle:Question:index.html.twig', array(
            'interactions'         => $interactions,
            'questionWithResponse' => $questionWithResponse,
            'alreadyShared'       => $alreadyShared,
            'sharedWithMe'       => $sharedWithMe,
            'pagerMy' => $pagerfantaMy,
            'pagerShared' => $pagerfantaShared
            )
        );
    }

    /**
     * Finds and displays a Question entity.
     *
     */
    public function showAction($id)
    {
        $question = $this->controlUserQuestion($id);
        $sharedQuestion = $this->controlUserSharedQuestion($id);

        if (count($question) > 0 || count($sharedQuestion) > 0) {
            $interaction = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Interaction')
                ->getInteraction($id);

            $typeInter = $interaction[0]->getType();

            switch ($typeInter) {
                case "InteractionQCM":

                    $response = new Response();
                    $interactionQCM = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionQCM')
                        ->getInteractionQCM($interaction[0]->getId());

                    if ($interactionQCM[0]->getShuffle()) {
                        $interactionQCM[0]->shuffleChoices();
                    } else {
                        $interactionQCM[0]->sortChoices();
                    }

                    $form   = $this->createForm(new ResponseType(), $response);

                    return $this->render(
                        'UJMExoBundle:InteractionQCM:paper.html.twig', array(
                        'interactionQCM' => $interactionQCM[0],
                        'form'   => $form->createView()
                        )
                    );

                case "InteractionGraphic":

                    $interactionGraph = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionGraphic')
                        ->getInteractionGraphic($interaction[0]->getId());

                    $repository = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:Coords');

                    $listeCoords = $repository->findBy(array('interactionGraphic' => $interactionGraph[0]));

                    return $this->render(
                        'UJMExoBundle:InteractionGraphic:paper.html.twig',
                        array(
                            'interactionGraphic' => $interactionGraph[0],
                            'listeCoords' => $listeCoords)
                    );

                case "InteractionHole":

                    $response = new Response();
                    $interactionHole = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionHole')
                        ->getInteractionHole($interaction[0]->getId());

                    $form   = $this->createForm(new ResponseType(), $response);

                    return $this->render(
                        'UJMExoBundle:InteractionHole:paper.html.twig', array(
                        'interactionHole' => $interactionHole[0],
                        'form'   => $form->createView()
                        )
                    );

                case "InteractionOpen":

                    break;
            }
        } else {
            return $this->redirect($this->generateUrl('ujm_question_index'));
        }
    }

    /**
     * Displays a form to create a new Question entity with interaction.
     *
     */
    public function newAction($exoID)
    {
        return $this->render('UJMExoBundle:Question:new.html.twig', array('exoID' => $exoID));
    }

    /**
     * Creates a new Question entity.
     *
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
            'form'   => $form->createView()
            )
        );
    }

    /**
     * Displays a form to edit an existing Question entity.
     *
     */
    public function editAction($id)
    {
        $question = $this->controlUserQuestion($id);

        if (count($question) > 0) {
            $interaction = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Interaction')
                ->getInteraction($id);

            $typeInter = $interaction[0]->getType();

            $nbResponses = 0;
            $em = $this->getDoctrine()->getManager();
            $response = $em->getRepository('UJMExoBundle:Response')
                ->findBy(array('interaction' => $interaction[0]->getId()));
            $nbResponses = count($response);

            switch ($typeInter) {
                case "InteractionQCM":

                    $interactionQCM = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionQCM')
                        ->getInteractionQCM($interaction[0]->getId());
                    //fired a sort function
                    $interactionQCM[0]->sortChoices();

                    $editForm = $this->createForm(
                        new InteractionQCMType(
                            $this->container->get('security.context')
                                ->getToken()->getUser()
                        ), $interactionQCM[0]
                    );
                    $deleteForm = $this->createDeleteForm($interactionQCM[0]->getId());

                    return $this->render(
                        'UJMExoBundle:InteractionQCM:edit.html.twig', array(
                        'entity'      => $interactionQCM[0],
                        'edit_form'   => $editForm->createView(),
                        'delete_form' => $deleteForm->createView(),
                        'nbResponses' => $nbResponses
                        )
                    );

                case "InteractionGraphic":
                    $interactionGraph = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionGraphic')
                        ->getInteractionGraphic($interaction[0]->getId());

                    $position = $em->getRepository(
                        'UJMExoBundle:Coords')->findBy(array(
                        'interactionGraphic' => $interactionGraph[0]->getId())
                    );

                    $editForm = $this->createForm(
                        new InteractionGraphicType(
                            $this->container->get('security.context')
                                ->getToken()->getUser()
                        ), $interactionGraph[0]
                    );

                    $deleteForm = $this->createDeleteForm($interactionGraph[0]->getId());

                    return $this->render(
                        'UJMExoBundle:InteractionGraphic:edit.html.twig', array(
                        'entity'      => $interactionGraph[0],
                        'edit_form'   => $editForm->createView(),
                        'delete_form' => $deleteForm->createView(),
                        'nbResponses' => $nbResponses,
                        'position'    => $position
                        )
                    );

                case "InteractionHole":
                    $interactionHole = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionHole')
                        ->getInteractionHole($interaction[0]->getId());

                    $editForm = $this->createForm(
                        new InteractionHoleType(
                            $this->container->get('security.context')
                                ->getToken()->getUser()
                        ), $interactionHole[0]
                    );
                    $deleteForm = $this->createDeleteForm($interactionHole[0]->getId());

                    return $this->render(
                        'UJMExoBundle:InteractionHole:edit.html.twig', array(
                        'entity'      => $interactionHole[0],
                        'edit_form'   => $editForm->createView(),
                        'delete_form' => $deleteForm->createView(),
                        'nbResponses' => $nbResponses
                        )
                    );

                case "InteractionOpen":

                    break;
            }
        } else {
            return $this->redirect($this->generateUrl('question'));
        }
    }

    /**
     * Edits an existing Question entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UJMExoBundle:Question')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Question entity.');
        }

        $editForm   = $this->createForm(new QuestionType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('question_edit', array('id' => $id)));
        }

        return $this->render(
            'UJMExoBundle:Question:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            )
        );
    }

    /**
     * Deletes a Question entity.
     *
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

            $typeInter = $interaction[0]->getType();

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
                        ->getInteractionQCM($interaction[0]->getId());

                    return $this->forward(
                        'UJMExoBundle:InteractionQCM:delete', array(
                            'id' => $interactionQCM[0]->getId(),
                            'pageNow'=> $pageNow
                        )
                    );

                case "InteractionGraphic":
                    $interactionGraph = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionGraphic')
                        ->getInteractionGraphic($interaction[0]->getId());

                    return $this->forward(
                        'UJMExoBundle:InteractionGraphic:delete', array(
                            'id' => $interactionGraph[0]->getId(),
                            'pageNow'=> $pageNow
                        )
                    );

                case "InteractionHole":
                    $interactionHole = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('UJMExoBundle:InteractionHole')
                        ->getInteractionHole($interaction[0]->getId());

                    return $this->forward(
                        'UJMExoBundle:InteractionHole:delete', array(
                            'id' => $interactionHole[0]->getId(),
                            'pageNow'=> $pageNow
                        )
                    );

                case "InteractionOpen":

                    break;
            }
        }
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }

    /**
     * Displays the rigth form when a teatcher wants to create a new Question (JS)
     *
     */
    public function choixFormTypeAction()
    {

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

                    return $this->container->get('templating')->renderResponse(
                        'UJMExoBundle:InteractionQCM:new.html.twig', array(
                        'exoID'  => $exoID,
                        'entity' => $entity,
                        'form'   => $form->createView()
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

                    return $this->container->get('templating')->renderResponse(
                        'UJMExoBundle:InteractionOpen:new.html.twig', array(
                        'exoID'  => $exoID,
                        'entity' => $entity,
                        'form'   => $form->createView()
                        )
                    );
                }
            }
        }
    }

    /**
     * To share Question
     *
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
     */
    public function searchAction()
    {
        $request = $this->get('request');

        $max = 5; // Max per page

        $search = $request->query->get('search');
        $page = $request->query->get('page');
        $questionID = $request->query->get('qId');

        if ($search != '') {
            $em = $this->getDoctrine()->getManager();
            $UserList = $em->getRepository('ClarolineCoreBundle:User')->findByName($search);

            // Pagination users for share question
            $adapterUserSearch = new ArrayAdapter($UserList);
            $pagerUserSearch = new Pagerfanta($adapterUserSearch);

            try {
                $userList = $pagerUserSearch
                    ->setMaxPerPage($max)
                    ->setCurrentPage($page)
                    ->getCurrentPageResults()
                ;
            } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
                throw $this->createNotFoundException("Cette page n'existe pas.");
            }

            // Put the result in a twig
            $divResultSearch = $this->render(
                'UJMExoBundle:Question:search.html.twig', array(
                'userList' => $userList,
                'pagerUserSearch' => $pagerUserSearch,
                'search' => $search,
                'questionID' => $questionID
            ));

            // If request is ajax (first display of the first search result (page = 1))
            if ($request->isXmlHttpRequest()) {
                return $divResultSearch; // Send the twig with the result
            } else {
                // Cut the header of the request to only have the twig with the result
                $divResultSearch = substr($divResultSearch, strrpos($divResultSearch, '<table'));

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
            ));
        }
    }

    /**
     * To control the User's rights to this Question
     *
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

    private function controlUserSharedQuestion($questionID)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $questions = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Share')
            ->getControlSharedQuestion($user->getId(), $questionID);

        return $questions;
    }

    /**
     * To manage the User's documents
     *
     */
    public function manageDocAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $request = $this->get('request');

        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Document');

        $ListDoc = $repository->findBy(array('user' => $user->getId()));

        // Pagination of the documents
        $max = 5; // Max questions displayed per page

        $page = $request->query->get('page', 1); // Which page

         // Make the pagination of the result with pagerfanta bundle
        $adapterDoc = new ArrayAdapter($ListDoc);
        $pagerDoc = new Pagerfanta($adapterDoc);

        try {
            $listDoc = $pagerDoc
                ->setMaxPerPage($max)
                ->setCurrentPage($page)
                ->getCurrentPageResults()
            ;
        } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
            throw $this->createNotFoundException("Cette page n'existe pas.");
        }

        return $this->render('UJMExoBundle:Question:manageImg.html.twig', array(
            'listDoc' => $listDoc,
            'pagerDoc' => $pagerDoc
            )
        );
    }

    /**
     * To delete a User's document
     *
     */
    public function deleteDocAction($label, $pageNow, $maxPage, $nbItem, $lastPage)
    {
        $dontdisplay = 0;

        $userId = $this->container->get('security.context')->getToken()->getUser()->getId();

        $repositoryDoc = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Document');

        $listDoc = $repositoryDoc->findByLabel($label, $userId, 0);

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UJMExoBundle:InteractionGraphic')->findBy(array('document' => $listDoc));

        if (!$entity) {

            $em->remove($listDoc[0]);
            $em->flush();

            $repository = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Document');

            $ListDoc = $repository->findBy(array('user' => $userId));

            // Pagination to see questions link to paper (and cannot be deleted)
            $adapterDoc = new ArrayAdapter($ListDoc);
            $pagerDoc = new Pagerfanta($adapterDoc);

            // If delete last item of page, display the previous one
            $rest = $nbItem % $maxPage;

            if ($rest == 1 && $pageNow == $lastPage) {
                $pageNow -= 1;
            }

            try {
                $listDoc = $pagerDoc
                    ->setMaxPerPage($maxPage)
                    ->setCurrentPage($pageNow)
                    ->getCurrentPageResults()
                ;
            } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
                throw $this->createNotFoundException("Cette page n'existe pas.");
            }

            return $this->render('UJMExoBundle:Question:manageImg.html.twig', array(
                'listDoc' => $listDoc,
                'pagerDoc' => $pagerDoc,
                )
            );

        } else {

            $questionWithResponse = array();
            $linkPaper = array();

            $request = $this->container->get('request');
            $max = 3;
            $page = $request->query->get('page', 1);
            $show = $request->query->get('show', 0);

            for ($i = 0; $i < count($entity); $i++) {

                $response = $em->getRepository('UJMExoBundle:Response')->findBy(
                    array('interaction' => $entity[$i]->getInteraction()->getId())
                );
                $paper = $em->getRepository('UJMExoBundle:ExerciseQuestion')->findBy(
                    array('question' => $entity[$i]->getInteraction()->getQuestion()->getId())
                );
            }

            if (count($response) > 0) {
                $questionWithResponse[] = 1;
                $dontdisplay = 1;
            } else {
                $questionWithResponse[] = 0;
            }

            if (count($paper) > 0) {
                $linkPaper[] = 1;
            } else {
                $linkPaper[] = 0;
            }

            $adapterDelDoc = new ArrayAdapter($entity);
            $pagerDelDoc = new Pagerfanta($adapterDelDoc);

            try {
                $entities = $pagerDelDoc
                    ->setMaxPerPage($max)
                    ->setCurrentPage($page)
                    ->getCurrentPageResults()
                ;
            } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
                throw $this->createNotFoundException("Cette page n'existe pas.");
            }

            return $this->render('UJMExoBundle:Question:safeDelete.html.twig', array(
                'listGraph' => $entities,
                'label' => $label,
                'questionWithResponse' => $questionWithResponse,
                'linkpaper' => $linkPaper,
                'dontdisplay' => $dontdisplay,
                'pagerDelDoc' => $pagerDelDoc,
                'pageNow' => $pageNow,
                'maxPage' => $maxPage,
                'nbItem' => $nbItem,
                'show' => $show
                )
            );
        }
    }

    /**
     * To delete a User's document linked to questions but not to paper
     *
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

        for ($i = 0; $i < count($entity); $i++) {
            $em->remove($entity[$i]);
        }

        $em->remove($listDoc[0]);
        $em->flush();

        return $this->redirect($this->generateUrl('ujm_question_manage_doc'));
    }

    /**
     * To change the label of a document
     *
     */
    public function updateNameAction()
    {
        $newlabel = $this->container->get('request')->request->get('newlabel');
        $oldlabel = $this->get('request')->get('oldName');

        $em = $this->getDoctrine()->getManager();

        $alterDoc = $em->getRepository('UJMExoBundle:Document')->findOneBy(array('label' => $oldlabel));

        $alterDoc->setLabel($newlabel);

        $em->persist($alterDoc);
        $em->flush();

        return $this->redirect($this->generateUrl('ujm_question_manage_doc'));
    }

    /**
     * To sort document by type
     *
     */
    public function sortDocumentsAction()
    {
        $request = $this->container->get('request');
        $user = $this->container->get('security.context')->getToken()->getUser();

        $max = 5; // Max per page

        $type = $request->query->get('doctype');
        $searchLabel = $request->query->get('searchLabel');
        $page = $request->query->get('page');

        if ($type && isset($searchLabel)) {
            $repository = $this->getDoctrine()
                ->getManager()
                ->getRepository('UJMExoBundle:Document');

            $ListDocSort = $repository->findByType($type, $user->getId(), $searchLabel);

            // Pagination of sorted documents
            $adapterSortDoc = new ArrayAdapter($ListDocSort);
            $pagerSortDoc = new Pagerfanta($adapterSortDoc);

            try {
                $listDocSort = $pagerSortDoc
                    ->setMaxPerPage($max)
                    ->setCurrentPage($page)
                    ->getCurrentPageResults()
                ;
            } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
                throw $this->createNotFoundException("Cette page n'existe pas.");
            }

            // Put the result in a twig
            $divResultSearch = $this->render(
                'UJMExoBundle:Question:sortDoc.html.twig', array(
                'listFindDoc' => $listDocSort,
                'pagerFindDoc' => $pagerSortDoc,
                'labelToFind' => $searchLabel,
                'whichAction' => 'sort',
                'doctype' => $type
            ));

            // If request is ajax (first display of the first search result (page = 1))
            if ($request->isXmlHttpRequest()) {
                return $divResultSearch; // Send the twig with the result
            } else {
                // Cut the header of the request to only have the twig with the result
                $divResultSearch = substr($divResultSearch, strrpos($divResultSearch, '<table'));

                // Send the form to search and the result
                return $this->render(
                    'UJMExoBundle:Question:manageImg.html.twig', array(
                    'divResultSearch' => $divResultSearch
                ));
            }
        } else {
            return $this->render(
                'UJMExoBundle:Question:sortDoc.html.twig', array(
                'listFindDoc' => '',
                'whichAction' => 'sort'
            ));
        }
    }

    /**
     * To search document with a defined label
     *
     */
    public function searchDocAction()
    {
        $userId = $this->container->get('security.context')->getToken()->getUser()->getId();
        $request = $this->get('request');

        $max = 5; // Max per page

        $labelToFind = $request->query->get('labelToFind');
        $page = $request->query->get('page');

        if ($labelToFind) {
            $em = $this->getDoctrine()->getManager();
            $ListFindDoc = $em->getRepository('UJMExoBundle:Document')->findByLabel($labelToFind, $userId, 1);

            // Pagination finded documents
            $adapterFindDoc = new ArrayAdapter($ListFindDoc);
            $pagerFindDoc = new Pagerfanta($adapterFindDoc);

            try {
                $listFindDoc = $pagerFindDoc
                    ->setMaxPerPage($max)
                    ->setCurrentPage($page)
                    ->getCurrentPageResults()
                ;
            } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
                throw $this->createNotFoundException("Cette page n'existe pas.");
            }

            // Put the result in a twig
            $divResultSearch = $this->render(
                'UJMExoBundle:Question:sortDoc.html.twig', array(
                'listFindDoc' => $listFindDoc,
                'pagerFindDoc' => $pagerFindDoc,
                'labelToFind' => $labelToFind,
                'whichAction' => 'search'
            ));

            // If request is ajax (first display of the first search result (page = 1))
            if ($request->isXmlHttpRequest()) {
                return $divResultSearch; // Send the twig with the result
            } else {
                // Cut the header of the request to only have the twig with the result
                $divResultSearch = substr($divResultSearch, strrpos($divResultSearch, '<table'));

                // Send the form to search and the result
                return $this->render(
                    'UJMExoBundle:Question:manageImg.html.twig', array(
                    'divResultSearch' => $divResultSearch
                ));
            }
        } else {
            return $this->render(
                'UJMExoBundle:Question:sortDoc.html.twig', array(
                'listFindDoc' => '',
                'whichAction' => 'search'
            ));
        }
    }


    /**
     * To share question with other users
     *
     */
    public function shareQuestionUserAction()
    {

        $request = $this->container->get('request');
        $creator = $this->container->get('security.context')->getToken()->getUser(); // User who share his question

        if ($request->isXmlHttpRequest()) {
            $QuestionID = $request->request->get('questionID'); // Which question is shared
            // With which user
            $UserName = $request->request->get('Uname');
            $UserFname = $request->request->get('Ufname');

            $em = $this->getDoctrine()->getManager();
            $MatchingName = $em->getRepository('ClarolineCoreBundle:User')->findByName($UserName);
            $question = $em->getRepository('UJMExoBundle:Question')->findOneBy(array('id' => $QuestionID));

            for ($i = 0; $i < count($MatchingName); $i++) {
                if ($MatchingName[$i]->getFirstName() == $UserFname) {
                    $user = $MatchingName[$i];
                    break;
                }
            }

            // Share the question
            $share = new Share($user, $question);
            $share->setAllowToModify(0); // false

            if ($creator->getId() == $user->getId()) {
                $self = true;
                $message = 'self;';
            } else {
                $self = false;
                $message = 'yes;';
            }

            // If not shared with him-self or already shared, can persist the sharing else display message
            if ($this->alreadySharedAction($share, $em) == false && $self == false) {
                $em->persist($share);
                $em->flush();
            return new \Symfony\Component\HttpFoundation\Response('no;'.$this->generateUrl('ujm_question_index'));
            } else {
                return new \Symfony\Component\HttpFoundation\Response($message);
            }
        }
    }

    /**
     * If question already shared with a given user
     *
     */
    public function alreadySharedAction($ToShare, $em)
    {
        $alreadyShared = $em->getRepository('UJMExoBundle:Share')->findAll();
        $already = false;

        for ($i = 0; $i < count($alreadyShared); $i++) {
            if ($alreadyShared[$i]->getUser() == $ToShare->getUser() && $alreadyShared[$i]->getQuestion() == $ToShare->getQuestion()) {
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
     */
    public function searchQuestionAction()
    {
        return $this->render('UJMExoBundle:Question:searchQuestion.html.twig');
    }

    /**
     * Display the questions matching to the research
     *
     */
    public function searchQuestionTypeAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $request = $this->get('request');

        $ListQuestions = array();
        $questionWithResponse = array();
        $alreadyShared = array();

        $max = 3; // Max questions displayed per page

        $type = $request->query->get('type'); // In which column
        $whatToFind = $request->query->get('whatToFind'); // Which text to find
        $where = $request->query->get('where'); // In which database
        $page = $request->query->get('page'); // Which page

        // If what and where to search is defined
        if ($type && $whatToFind && $where) {
            $em = $this->getDoctrine()->getEntityManager();
            $QuestionRepository = $em->getRepository('UJMExoBundle:Question');
            $InteractionRepository = $em->getRepository('UJMExoBundle:Interaction');

            // Get the matching questions depending on :
            //  * in which database search,
            //  * in witch column
            //  * and what text to search

            // User's database
            if ($where == 'my') {
                switch ($type) {
                    case 'Category':
                        $Questions = $QuestionRepository->findByCategory($user->getId(), $whatToFind);

                        for ($i = 0; $i < count($Questions); $i++) {
                            $ListQuestions[] = $InteractionRepository->findOneBy(array('question' => $Questions[$i]->getId()));
                        }
                        break;

                    case 'Type':
                        $ListQuestions = $InteractionRepository->findByType($user->getId(), $whatToFind);
                        break;

                    case 'Title':
                         $Questions = $QuestionRepository->findByTitle($user->getId(), $whatToFind);

                        for ($i = 0; $i < count($Questions); $i++) {
                            $ListQuestions[] = $InteractionRepository->findOneBy(array('question' => $Questions[$i]->getId()));
                        }
                        break;

                    case 'Contain':
                        $ListQuestions = $InteractionRepository->findByContain($user->getId(), $whatToFind);
                        break;
                }

                // For all the matching questions search if ...
                foreach ($ListQuestions as $list) {
                    // ... the question is link to a paper (question in the test has already been passed)
                    $response = $em->getRepository('UJMExoBundle:Response')
                        ->findBy(array('interaction' => $list->getId()));
                    if (count($response) > 0) {
                        $questionWithResponse[] = 1;
                    } else {
                        $questionWithResponse[] = 0;
                    }

                    // ...the question is shared or not
                    $share = $em->getRepository('UJMExoBundle:Share')
                        ->findBy(array('question' => $list->getQuestion()->getId()));
                    if (count($share) > 0) {
                        $alreadyShared[] = 1;
                    } else {
                        $alreadyShared[] = 0;
                    }
                }

                // Make the pagination of the result with pagerfanta bundle
                $adapterSearch = new ArrayAdapter($ListQuestions);
                $pagerSearch = new Pagerfanta($adapterSearch);

                try {
                    $listQuestions = $pagerSearch
                        ->setMaxPerPage($max)
                        ->setCurrentPage($page)
                        ->getCurrentPageResults()
                    ;
                } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
                    throw $this->createNotFoundException("Cette page n'existe pas.");
                }

                // Put the result in a twig
                $divResultSearch = $this->render(
                    'UJMExoBundle:Question:SearchQuestionType.html.twig', array(
                    'listQuestions' => $listQuestions,
                    'canDisplay' => $where,
                    'pagerSearch' => $pagerSearch,
                    'type'        => $type,
                    'whatToFind'  => $whatToFind,
                    'questionWithResponse' => $questionWithResponse,
                    'alreadyShared' => $alreadyShared
                ));

                // If request is ajax (first display of the first search result (page = 1))
                if ($request->isXmlHttpRequest()) {
                    return $divResultSearch; // Send the twig with the result
                } else {
                    // Cut the header of the request to only have the twig with the result
                    $divResultSearch = substr($divResultSearch, strrpos($divResultSearch, '<table'));

                    // Send the form to search and the result
                    return $this->render(
                        'UJMExoBundle:Question:searchQuestion.html.twig', array(
                        'divResultSearch' => $divResultSearch
                    ));
                }
            // Shared with user's database
            } else if ($where == 'shared') {
                switch ($type) {
                    case 'Category':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByCategoryShared($user->getId(), $whatToFind);

                        for ($i = 0; $i < count($sharedQuestion); $i++) {
                            $ListQuestions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;

                    case 'Type':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByTypeShared($user->getId(), $whatToFind);

                        for ($i = 0; $i < count($sharedQuestion); $i++) {
                            $ListQuestions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;

                    case 'Title':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByTitleShared($user->getId(), $whatToFind);

                        for ($i = 0; $i < count($sharedQuestion); $i++) {
                            $ListQuestions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;

                     case 'Contain':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByContainShared($user->getId(), $whatToFind);

                        for ($i = 0; $i < count($sharedQuestion); $i++) {
                            $ListQuestions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;
                }

                // Make the pagination of the result with pagerfanta bundle
                $adapterSearch = new ArrayAdapter($ListQuestions);
                $pagerSearch = new Pagerfanta($adapterSearch);

                try {
                    $listQuestions = $pagerSearch
                        ->setMaxPerPage($max)
                        ->setCurrentPage($page)
                        ->getCurrentPageResults()
                    ;
                } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
                    throw $this->createNotFoundException("Cette page n'existe pas.");
                }

                // Put the result in a twig
                $divResultSearch = $this->render(
                    'UJMExoBundle:Question:SearchQuestionType.html.twig', array(
                    'listQuestions' => $listQuestions,
                    'canDisplay' => $where,
                    'pagerSearch' => $pagerSearch,
                    'type'        => $type,
                    'whatToFind'  => $whatToFind
                ));

                // If request is ajax (first display of the first search result (page = 1))
                if ($request->isXmlHttpRequest()) {
                    return $divResultSearch; // Send the twig with the result
                } else {
                    // Cut the header of the request to only have the twig with the result
                    $divResultSearch = substr($divResultSearch, strrpos($divResultSearch, '<table'));

                    // Send the form to search and the result
                    return $this->render(
                        'UJMExoBundle:Question:searchQuestion.html.twig', array(
                        'divResultSearch' => $divResultSearch
                    ));
                }
            }
        } else {
            return $this->render(
                'UJMExoBundle:Question:SearchQuestionType.html.twig', array(
                'listQuestions' => '',
            ));
        }
    }
}