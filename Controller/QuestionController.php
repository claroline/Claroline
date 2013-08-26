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
    public function indexAction($pageNow = 0, $pageNowShared = 0, $categoryToFind = '', $titleToFind = '')
    {
        // To paginate the result :
        $request = $this->get('request'); // Get the request which contains the following parameters :
        $page = $request->query->get('page', 1); // Get the choosen page (default 1)
        $click = $request->query->get('click', 'my'); // Get which array to fchange page (default 'my question')
        $pagerMy = $request->query->get('pagerMy', 1); // Get the page of the array my question (default 1)
        $pagerShared = $request->query->get('pagerShared', 1); // Get the pager of the array my shared question (default 1)
        $max = 5; // Max of questions per page

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

        $questionWithResponse = array();
        $alreadyShared = array();
        $em = $this->getDoctrine()->getManager();

        foreach ($interactions as $interaction) {
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

        $sharedWithMe = array();

        $end = count($shared);

        for ($i = 0; $i < $end; $i++) {
            $sharedWithMe[] = $em->getRepository('UJMExoBundle:Interaction')
                ->findOneBy(array('question' => $shared[$i]->getQuestion()->getId()));
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

        $doublePagination = $this->doublePaginationWithIf($interactions, $sharedWithMe, $max, $pagerMy, $pagerShared, $pageNow, $pageNowShared);

        $interactionsPager = $doublePagination[0];
        $pagerfantaMy = $doublePagination[1];

        $sharedWithMePager = $doublePagination[2];
        $pagerfantaShared = $doublePagination[3];

        return $this->render(
            'UJMExoBundle:Question:index.html.twig', array(
            'interactions'         => $interactionsPager,
            'questionWithResponse' => $questionWithResponse,
            'alreadyShared'       => $alreadyShared,
            'sharedWithMe'       => $sharedWithMePager,
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
    public function editAction($id, $form = null)
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

                    if ($form == null) {
                        $editForm = $this->createForm(
                            new InteractionQCMType(
                                $this->container->get('security.context')
                                    ->getToken()->getUser()
                            ), $interactionQCM[0]
                        );
                    } else {
                        $editForm = $form;
                    }
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

                    $position = $em->getRepository('UJMExoBundle:Coords')->findBy(
                        array('interactionGraphic' => $interactionGraph[0]->getId()
                        )
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

        $editForm->handleRequest($request);

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
                            'pageNow' => $pageNow
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
                            'pageNow' => $pageNow
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
                            'pageNow' => $pageNow
                        )
                    );

                case "InteractionOpen":

                    break;
            }
        }
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
                )
            );
        }
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

        $listDoc = $repository->findBy(array('user' => $user->getId()));

        // Pagination of the documents
        $max = 5; // Max questions displayed per page

        $page = $request->query->get('page', 1); // Which page

        $pagination = $this->pagination($listDoc, $max, $page);

        $listDocPager = $pagination[0];
        $pagerDoc= $pagination[1];

        return $this->render(
            'UJMExoBundle:Question:manageImg.html.twig',
            array(
                'listDoc' => $listDocPager,
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

            $listDoc = $repository->findBy(array('user' => $userId));

            // If delete last item of page, display the previous one
            $rest = $nbItem % $maxPage;

            if ($rest == 1 && $pageNow == $lastPage) {
                $pageNow -= 1;
            }

            $pagination = $this->pagination($listDoc, $maxPage, $pageNow);

            $listDocPager = $pagination[0];
            $pagerDoc = $pagination[1];

            return $this->render(
                'UJMExoBundle:Question:manageImg.html.twig',
                array(
                    'listDoc' => $listDocPager,
                    'pagerDoc' => $pagerDoc,
                )
            );

        } else {

            $questionWithResponse = array();
            $linkPaper = array();

            $request = $this->container->get('request');
            $max = 5;
            $page = $request->query->get('page', 1);
            $show = $request->query->get('show', 0);

            $end = count($entity);

            for ($i = 0; $i < $end; $i++) {

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

            $pagination = $this->pagination($entity, $max, $page);

            $entities = $pagination[0];
            $pagerDelDoc = $pagination[1];

            return $this->render(
                'UJMExoBundle:Question:safeDelete.html.twig',
                array(
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

        $end = count($entity);

        for ($i = 0; $i < $end; $i++) {
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

            $listDocSort = $repository->findByType($type, $user->getId(), $searchLabel);

            $pagination = $this->pagination($listDocSort, $max, $page);

            $listDocSortPager = $pagination[0];
            $pagerSortDoc = $pagination[1];

            // Put the result in a twig
            $divResultSearch = $this->render(
                'UJMExoBundle:Question:sortDoc.html.twig', array(
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
                    'UJMExoBundle:Question:manageImg.html.twig', array(
                    'divResultSearch' => $divResultSearch
                    )
                );
            }
        } else {
            return $this->render(
                'UJMExoBundle:Question:sortDoc.html.twig', array(
                'listFindDoc' => '',
                'whichAction' => 'sort'
                )
            );
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
            $listFindDoc = $em->getRepository('UJMExoBundle:Document')->findByLabel($labelToFind, $userId, 1);

            $pagination = $this->pagination($listFindDoc, $max, $page);

            $listFindDocPager = $pagination[0];
            $pagerFindDoc = $pagination[1];

            // Put the result in a twig
            $divResultSearch = $this->render(
                'UJMExoBundle:Question:sortDoc.html.twig', array(
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
                    'UJMExoBundle:Question:manageImg.html.twig', array(
                    'divResultSearch' => $divResultSearch
                    )
                );
            }
        } else {
            return $this->render(
                'UJMExoBundle:Question:sortDoc.html.twig', array(
                'listFindDoc' => '',
                'whichAction' => 'search'
                )
            );
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
            $questionID = $request->request->get('questionID'); // Which question is shared
            // With which user
            $userName = $request->request->get('Uname');
            $userFname = $request->request->get('Ufname');

            $em = $this->getDoctrine()->getManager();
            $matchingName = $em->getRepository('ClarolineCoreBundle:User')->findByName($userName);
            $question = $em->getRepository('UJMExoBundle:Question')->findOneBy(array('id' => $questionID));

            $end = count($matchingName);

            for ($i = 0; $i < $end; $i++) {
                if ($matchingName[$i]->getFirstName() == $userFname) {
                    $user = $matchingName[$i];
                    break;
                }
            }

            // Share the question
            $share = new Share($user, $question);
            $share->setAllowToModify(0); // False

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

        $listQuestions = array();
        $questionWithResponse = array();
        $alreadyShared = array();

        $max = 5; // Max questions displayed per page

        $type = $request->query->get('type'); // In which column
        $whatToFind = $request->query->get('whatToFind'); // Which text to find
        $where = $request->query->get('where'); // In which database
        $page = $request->query->get('page'); // Which page

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
                            $listQuestions[] = $interactionRepository->findOneBy(array('question' => $questions[$i]->getId()));
                        }
                        break;

                    case 'Type':
                        $listQuestions = $interactionRepository->findByType($user->getId(), $whatToFind);
                        break;

                    case 'Title':
                        $questions = $questionRepository->findByTitle($user->getId(), $whatToFind);

                        $end = count($questions);

                        for ($i = 0; $i < $end; $i++) {
                            $listQuestions[] = $interactionRepository->findOneBy(array('question' => $questions[$i]->getId()));
                        }
                        break;

                    case 'Contain':
                        $listQuestions = $interactionRepository->findByContain($user->getId(), $whatToFind);
                        break;
                }

                // For all the matching questions search if ...
                foreach ($listQuestions as $list) {
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

                $pagination = $this->pagination($listQuestions, $max, $page);

                $listQuestionsPager = $pagination[0];
                $pagerSearch = $pagination[1];

                // Put the result in a twig
                $divResultSearch = $this->render(
                    'UJMExoBundle:Question:SearchQuestionType.html.twig', array(
                    'listQuestions' => $listQuestionsPager,
                    'canDisplay' => $where,
                    'pagerSearch' => $pagerSearch,
                    'type'        => $type,
                    'whatToFind'  => $whatToFind,
                    'questionWithResponse' => $questionWithResponse,
                    'alreadyShared' => $alreadyShared
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
                        'UJMExoBundle:Question:searchQuestion.html.twig', array(
                        'divResultSearch' => $divResultSearch
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
                            $listQuestions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;

                    case 'Type':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByTypeShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listQuestions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;

                    case 'Title':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByTitleShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listQuestions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;

                    case 'Contain':
                        $sharedQuestion = $em->getRepository('UJMExoBundle:Share')
                            ->findByContainShared($user->getId(), $whatToFind);

                        $end = count($sharedQuestion);

                        for ($i = 0; $i < $end; $i++) {
                            $listQuestions[] = $em->getRepository('UJMExoBundle:Interaction')
                                ->findOneBy(array('question' => $sharedQuestion[$i]->getQuestion()->getId()));
                        }
                        break;
                }

                $pagination = $this->pagination($listQuestions, $max, $page);

                $listQuestionsPager = $pagination[0];
                $pagerSearch = $pagination[1];

                // Put the result in a twig
                $divResultSearch = $this->render(
                    'UJMExoBundle:Question:SearchQuestionType.html.twig', array(
                    'listQuestions' => $listQuestionsPager,
                    'canDisplay' => $where,
                    'pagerSearch' => $pagerSearch,
                    'type'        => $type,
                    'whatToFind'  => $whatToFind
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
                        'UJMExoBundle:Question:searchQuestion.html.twig', array(
                        'divResultSearch' => $divResultSearch
                        )
                    );
                }
            }
        } else {
            return $this->render(
                'UJMExoBundle:Question:SearchQuestionType.html.twig', array(
                'listQuestions' => '',
                )
            );
        }
    }

    /**
     * To delete the shared question of user's questions bank
     */
    public function deleteSharedQuestionAction($id, $pageNow, $maxPage, $nbItem, $lastPage)
    {
        $em = $this->getDoctrine()->getManager();
        $sharedToDel = $em->getRepository('UJMExoBundle:Share')->findOneBy(array('question' => $id));

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
     */
    public function seeSharedWithAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $questionsharedWith = $em->getRepository('UJMExoBundle:Share')->findBy(array('question' => $id));

        $sharedWith = array();
        $stop = count($questionsharedWith);

        for ($i = 0 ; $i < $stop ; $i++) {
            $sharedWith[] = $em->getRepository('ClarolineCoreBundle:User')->find($questionsharedWith[$i]->getUser()->getId());
        }

        return $this->render(
            'UJMExoBundle:Question:seeSharedWith.html.twig', array(
            'sharedWith' => $sharedWith,
            )
        );
    }
    
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }
    
    /**
     * To control the User's rights to this question
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

    /**
     * To control the User's rights to this shared question
     *
     */
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
     * To paginate table
     *
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
     */
    private function doublePaginationWithIf($entityToPaginate1, $entityToPaginate2, $max, $page1, $page2, $pageNow1, $pageNow2)
    {
        $adapter1 = new ArrayAdapter($entityToPaginate1);
        $pager1 = new Pagerfanta($adapter1);

        $adapter2 = new ArrayAdapter($entityToPaginate2);
        $pager2 = new Pagerfanta($adapter2);

        try {
            if ($pageNow1 == 0) {
                $entityPaginated1 = $pager1
                    ->setMaxPerPage($max)
                    ->setCurrentPage($page1)
                    ->getCurrentPageResults();
            } else {
                $entityPaginated1 = $pager1
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pageNow1)
                    ->getCurrentPageResults();
            }

            if ($pageNow2 == 0) {
                $entityPaginated2 = $pager2
                    ->setMaxPerPage($max)
                    ->setCurrentPage($page2)
                    ->getCurrentPageResults();
            } else {
                $entityPaginated2 = $pager2
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pageNow2)
                    ->getCurrentPageResults();
            }
        } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
            throw $this->createNotFoundException("Cette page n'existe pas.");
        }

        $doublePagination[0] = $entityPaginated1;
        $doublePagination[1] = $pager1;

        $doublePagination[2] = $entityPaginated2;
        $doublePagination[3] = $pager2;

        return $doublePagination;
    }
}