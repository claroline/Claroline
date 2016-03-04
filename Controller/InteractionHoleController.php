<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use UJM\ExoBundle\Entity\InteractionHole;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Form\InteractionHoleType;
use UJM\ExoBundle\Form\ResponseType;
use UJM\ExoBundle\Form\InteractionHoleHandler;

/**
 * InteractionHole controller.
 */
class InteractionHoleController extends Controller {

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction() {
        $attr = $this->get('request')->attributes;
        $em = $this->get('doctrine')->getEntityManager();
        $vars = $attr->get('vars');

        $response = new Response();
        $interactionHole = $em->getRepository('UJMExoBundle:InteractionHole')
                ->findOneByQuestion($attr->get('interaction')->getId());

        $form = $this->createForm(new ResponseType(), $response);

        $vars['interactionToDisplayed'] = $interactionHole;
        $vars['form'] = $form->createView();
        $vars['exoID'] = $attr->get('exoID');

        return $this->render('UJMExoBundle:InteractionHole:paper.html.twig', $vars);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction() {
        $attr = $this->get('request')->attributes;
        $entity = new InteractionHole();
        $form = $this->createForm(
                new InteractionHoleType(
                $this->container->get('security.token_storage')
                        ->getToken()->getUser()
                ), $entity
        );

        return $this->container->get('templating')->renderResponse(
                        'UJMExoBundle:InteractionHole:new.html.twig', array(
                    'exoID' => $attr->get('exoID'),
                    'entity' => $entity,
                    'form' => $form->createView(),
                        )
        );
    }

    /**
     * Creates a new InteractionHole entity.
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction() {
        $interHole = new InteractionHole();
        $form = $this->createForm(
                new InteractionHoleType(
                $this->container->get('security.token_storage')->getToken()->getUser()
                ), $interHole
        );

        $exoID = $this->container->get('request')->request->get('exercise');

        //Get the lock category
        $catSer = $this->container->get('ujm.exo_category');

        $exercise = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Exercise')->find($exoID);
        $formHandler = new InteractionHoleHandler(
                $form, $this->get('request'), $this->getDoctrine()->getManager(),
                $this->container->get('ujm.exo_exercise'), $catSer,
                $this->container->get('security.token_storage')->getToken()->getUser(),
                $exercise, $this->get('translator')
        );

        $formHandler->setValidator($this->get('validator'));

        $holeHandler = $formHandler->processAdd();
        if ($holeHandler === true) {
            $categoryToFind = $interHole->getQuestion()->getCategory();
            $titleToFind = $interHole->getQuestion()->getTitle();

            if ($exoID == -1) {
                return $this->redirect(
                                $this->generateUrl('ujm_question_index', array(
                                    'categoryToFind' => base64_encode($categoryToFind), 'titleToFind' => base64_encode($titleToFind),)
                                )
                );
            } else {
                return $this->redirect(
                                $this->generateUrl('ujm_exercise_questions', array(
                                    'id' => $exoID, 'categoryToFind' => $categoryToFind, 'titleToFind' => $titleToFind,)
                                )
                );
            }
        }

        if ($holeHandler != false) {
            if ($holeHandler == 'infoDuplicateQuestion') {
                $form->addError(new FormError(
                        $this->get('translator')->trans('info_duplicate_question', array(), 'ujm_exo')
                ));
            } else {
                $form->addError(new FormError($holeHandler));
            }
        }

        $formWithError = $this->render(
                'UJMExoBundle:InteractionHole:new.html.twig', array(
            'entity' => $interHole,
            'form' => $form->createView(),
            'error' => true,
            'exoID' => $exoID,
                )
        );
        $interactionType = $this->container->get('ujm.exo_question')->getTypes();
        $formWithError = substr($formWithError, strrpos($formWithError, 'GMT') + 3);

        return $this->render(
                     'UJMExoBundle:Question:new.html.twig', array(
                    'formWithError' => $formWithError,
                    'exoID' => $exoID,
                    'linkedCategory' => $catSer->getLinkedCategories(),
                    'locker' => $catSer->getLockCategory(),
                    'interactionType' => $interactionType,
                        )
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction() {
        $attr = $this->get('request')->attributes;
        $holeSer = $this->container->get('ujm.exo_InteractionHole');
        $catSer = $this->container->get('ujm.exo_category');
        $em = $this->get('doctrine')->getEntityManager();

        $interactionHole = $em->getRepository('UJMExoBundle:InteractionHole')
                ->findOneByQuestion($attr->get('interaction')->getId());

        $catSer->ctrlCategory($interactionHole->getQuestion());

        $editForm = $this->createForm(
                new InteractionHoleType($attr->get('user'), $attr->get('catID')), $interactionHole
        );

        $linkedCategory = $catSer->getLinkedCategories();

        return $this->render(
                        'UJMExoBundle:InteractionHole:edit.html.twig', array(
                    'entity' => $interactionHole,
                    'edit_form' => $editForm->createView(),
                    'nbResponses' => $holeSer->getNbReponses($attr->get('interaction')),
                    'linkedCategory' => $linkedCategory,
                    'exoID' => $attr->get('exoID'),
                    'locker' => $catSer->getLockCategory(),
                        )
        );
    }

    /**
     * Edits an existing InteractionHole entity.
     *
     *
     * @param int $id id of InteractionHole
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction($id) {
        $exoID = $this->container->get('request')->request->get('exercise');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $catID = -1;

        $em = $this->getDoctrine()->getManager();

        $interHole = $em->getRepository('UJMExoBundle:InteractionHole')->find($id);

        if (!$interHole) {
            throw $this->createNotFoundException('Unable to find InteractionHole entity.');
        }

        if ($user->getId() != $interHole->getQuestion()->getUser()->getId()) {
            $catID = $interHole->getQuestion()->getCategory()->getId();
        }

        $editForm = $this->createForm(
                new InteractionHoleType(
                $this->container->get('security.token_storage')->getToken()->getUser(), $catID
                ), $interHole
        );
        $formHandler = new InteractionHoleHandler(
                $editForm, $this->get('request'), $this->getDoctrine()->getManager(),
                $this->container->get('ujm.exo_exercise'), $this->container->get('ujm.exo_category'),
                $this->container->get('security.token_storage')->getToken()->getUser(),
                $exoID, $this->get('translator')
        );

        $formHandler->setValidator($this->get('validator'));

        $holeHandler = $formHandler->processUpdate($interHole);
        if ($holeHandler === true) {
            if ($exoID == -1) {
                return $this->redirect($this->generateUrl('ujm_question_index'));
            } else {
                return $this->redirect(
                                $this->generateUrl(
                                        'ujm_exercise_questions', array(
                                    'id' => $exoID,
                                        )
                                )
                );
            }
        }

        if ($holeHandler != false) {
            $editForm->addError(new FormError($holeHandler));
        }

        return $this->forward(
                        'UJMExoBundle:Question:edit', array(
                    'exoID' => $exoID,
                    'id' => $interHole->getQuestion()->getId(),
                    'form' => $editForm,
                        )
        );
    }

    /**
     * Deletes a InteractionHole entity.
     *
     *
     * @param int    $id      id of InteractionHole
     * @param intger $pageNow for pagination, actual page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id, $pageNow) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('UJMExoBundle:InteractionHole')->find($id);
        //Deleting of relations, if there the question is shared
        $sharesQuestion = $em->getRepository('UJMExoBundle:Share')->findBy(array('question' => $entity->getQuestion()->getId()));
        foreach ($sharesQuestion as $share) {
            $em->remove($share);
        }
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InteractionQCM entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('ujm_question_index', array('pageNow' => $pageNow)));
    }

    /**
     * To test the question with holes by the teacher.
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function responseHoleAction() {
        $vars = array();
        $request = $this->get('request');
        $postVal = $req = $request->request->all();

        if ($postVal['exoID'] != -1) {
            $exercise = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Exercise')->find($postVal['exoID']);
            $vars['_resource'] = $exercise;
        }

        $interSer = $this->container->get('ujm.exo_InteractionHole');
        $res = $interSer->response($request);

        $vars['score'] = $res['score'];
        $vars['penalty'] = $res['penalty'];
        $vars['interHole'] = $res['interHole'];
        $vars['response'] = $res['response'];
        $vars['exoID'] = $postVal['exoID'];

        return $this->render('UJMExoBundle:InteractionHole:holeOverview.html.twig', $vars);
    }

}
