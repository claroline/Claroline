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

use Pagerfanta\Adapter\DoctrineORMAdapter;
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
    public function indexAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $uid = $user->getId();
        $interactions = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('UJMExoBundle:Interaction')
            ->getUserInteraction($uid);

        $questionWithResponse = array();
        $alreadyShared = array();
        $em = $this->getDoctrine()->getEntityManager();

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

        for ($i = 0; $i < count($shared); $i++) {
            $sharedWithMe[] = $em->getRepository('UJMExoBundle:Interaction')
                ->findOneBy(array('question' => $shared[$i]->getQuestion()->getId()));
        }

        return $this->render(
            'UJMExoBundle:Question:index.html.twig', array(
            'interactions'         => $interactions,
            'questionWithResponse' => $questionWithResponse,
            'alreadyShared'       => $alreadyShared,
            'sharedWithMe'       => $sharedWithMe
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

        if (count($question) > 0) {
            $interaction = $this->getDoctrine()
                ->getEntityManager()
                ->getRepository('UJMExoBundle:Interaction')
                ->getInteraction($id);

            $typeInter = $interaction[0]->getType();

            switch ($typeInter) {
                case "InteractionQCM":

                    $response = new Response();
                    $interactionQCM = $this->getDoctrine()
                        ->getEntityManager()
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
                        ->getEntityManager()
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
                        ->getEntityManager()
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
            return $this->redirect($this->generateUrl('question'));
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
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
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
                ->getEntityManager()
                ->getRepository('UJMExoBundle:Interaction')
                ->getInteraction($id);

            $typeInter = $interaction[0]->getType();

            $nbResponses = 0;
            $em = $this->getDoctrine()->getEntityManager();
            $response = $em->getRepository('UJMExoBundle:Response')
                ->findBy(array('interaction' => $interaction[0]->getId()));
            $nbResponses = count($response);

            switch ($typeInter) {
                case "InteractionQCM":

                    $interactionQCM = $this->getDoctrine()
                        ->getEntityManager()
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
                        ->getEntityManager()
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
                        ->getEntityManager()
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
        $em = $this->getDoctrine()->getEntityManager();

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
    public function deleteAction($id)
    {
        $question = $this->controlUserQuestion($id);

        if (count($question) > 0) {
            $em = $this->getDoctrine()->getEntityManager();

            $eq = $this->getDoctrine()
                ->getEntityManager()
                ->getRepository('UJMExoBundle:ExerciseQuestion')
                ->getExercises($id);

            foreach ($eq as $e) {
                $em->remove($e);
            }

            $em->flush();

            $interaction = $this->getDoctrine()
                ->getEntityManager()
                ->getRepository('UJMExoBundle:Interaction')
                ->getInteraction($id);

            $typeInter = $interaction[0]->getType();

            switch ($typeInter) {
                case "InteractionQCM":
                    $interactionQCM = $this->getDoctrine()
                        ->getEntityManager()
                        ->getRepository('UJMExoBundle:InteractionQCM')
                        ->getInteractionQCM($interaction[0]->getId());

                    return $this->forward(
                        'UJMExoBundle:InteractionQCM:delete', array(
                            'id' => $interactionQCM[0]->getId()
                        )
                    );

                case "InteractionGraphic":
                    $interactionGraph = $this->getDoctrine()
                        ->getEntityManager()
                        ->getRepository('UJMExoBundle:InteractionGraphic')
                        ->getInteractionGraphic($interaction[0]->getId());

                    return $this->forward(
                        'UJMExoBundle:InteractionGraphic:delete', array(
                            'id' => $interactionGraph[0]->getId()
                        )
                    );

                case "InteractionHole":
                    $interactionHole = $this->getDoctrine()
                        ->getEntityManager()
                        ->getRepository('UJMExoBundle:InteractionHole')
                        ->getInteractionHole($interaction[0]->getId());

                    return $this->forward(
                        'UJMExoBundle:InteractionHole:delete', array(
                            'id' => $interactionHole[0]->getId()
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
        $request = $this->container->get('request');
        $search = $request->request->get('search');

        if ($search != '') {
            $em = $this->getDoctrine()->getEntityManager();
            $userList = $em->getRepository('ClarolineCoreBundle:User')->findByName($search);
        }

        return $this->render(
            'UJMExoBundle:Question:search.html.twig', array(
            'userList' => $userList
            )
        );
    }

    /**
     * To control the User's rights to this Question
     *
     */
    private function controlUserQuestion($questionID)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $question = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('UJMExoBundle:Question')
            ->getControlOwnerQuestion($user->getId(), $questionID);

        return $question;
    }

    /**
     * To manage the User's documents
     *
     */
    public function manageDocAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Document');

        $listDoc = $repository->findBy(array('user' => $user->getId()));

        return $this->render('UJMExoBundle:Question:manageImg.html.twig', array(
            'listDoc' => $listDoc
            )
        );
    }

    /**
     * To delete a User's document
     *
     */
    public function deleteDocAction($label)
    {
        $dontdisplay = 0;
        $repositoryDoc = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Document');

        $listDoc = $repositoryDoc->findOneBy(array('label' => $label));

        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('UJMExoBundle:InteractionGraphic')->findBy(array('document' => $listDoc));

        if (!$entity) {

            $em->remove($listDoc);
            $em->flush();

            return $this->redirect($this->generateUrl('ujm_question_manage_doc'));

        } else {

            $questionWithResponse = array();

            for ($i = 0; $i < count($entity); $i++) {

                $response = $em->getRepository('UJMExoBundle:Response')->findBy(array('interaction' => $entity[$i]->getInteraction()->getId()));

                if (count($response) > 0) {
                    $questionWithResponse[] = 1;
                    $dontdisplay = 1;
                } else {
                    $questionWithResponse[] = 0;
                }
            }

            return $this->render('UJMExoBundle:Question:safeDelete.html.twig', array(
                'listGraph' => $entity,
                'label' => $label,
                'questionWithResponse' => $questionWithResponse,
                'dontdisplay' => $dontdisplay
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
        $repositoryDoc = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:Document');

        $listDoc = $repositoryDoc->findOneBy(array('label' => $label));

        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('UJMExoBundle:InteractionGraphic')->findBy(array('document' => $listDoc));

        for ($i = 0; $i < count($entity); $i++) {
            $em->remove($entity[$i]);
        }

        $em->remove($listDoc);
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

        $em = $this->getDoctrine()->getEntityManager();

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

        if ($request->isXmlHttpRequest()) {
            $type = $request->request->get('doctype');

            if ($type) {
                $repository = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Document');

                if ($type == 'all') {
                    $listDocSort = $repository->findBy(array('user' => $user->getId()));
                } else {
                    $listDocSort = $repository->findByType($type);
                }
            }
        }

        return $this->render('UJMExoBundle:Question:sortDoc.html.twig', array(
            'listDoc' => $listDocSort
            )
        );
    }

    /**
     * To search document with a defined label
     *
     */
    public function searchDocAction()
    {
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            $labelToFind = $request->request->get('labelToFind');

            if ($labelToFind) {
                $em = $this->getDoctrine()->getEntityManager();
                $listFindDoc = $em->getRepository('UJMExoBundle:Document')->findByLabel($labelToFind);
            }
        }

        return $this->render('UJMExoBundle:Question:sortDoc.html.twig', array(
            'listDoc' => $listFindDoc
            )
        );
    }

    public function shareQuestionUserAction()
    {

        $request = $this->container->get('request');
        $creator = $this->container->get('security.context')->getToken()->getUser();

        if ($request->isXmlHttpRequest()) {
            $QuestionID = $request->request->get('questionID');
            $UserName = $request->request->get('Uname');
            $UserFname = $request->request->get('Ufname');

            $em = $this->getDoctrine()->getEntityManager();
            $MatchingName = $em->getRepository('ClarolineCoreBundle:User')->findByName($UserName);
            $question = $em->getRepository('UJMExoBundle:Question')->findOneBy(array('id' => $QuestionID));

            for ($i = 0; $i < count($MatchingName); $i++) {
                if($MatchingName[$i]->getFirstName() == $UserFname) {
                    $user = $MatchingName[$i];
                    break;
                }
            }

            $share = new Share($user, $question);
            $share->setAllowToModify(0); // false

            if($creator->getId() == $user->getId()){
                $self = true;
                $message = 'self;';
            } else {
                $self = false;
                $message = 'yes;';
            }

            if ($this->alreadySharedAction($share, $em) == false && $self == false) {
                $em->persist($share);
                $em->flush();
            return new \Symfony\Component\HttpFoundation\Response('no;'.$this->generateUrl('ujm_question_index'));
            } else {
                return new \Symfony\Component\HttpFoundation\Response($message);
            }
        }
    }

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
}