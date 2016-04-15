<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Component\Form\FormError;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use UJM\ExoBundle\Entity\InteractionOpen;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Form\InteractionOpenType;
use UJM\ExoBundle\Form\InteractionOpenHandler;
use UJM\ExoBundle\Form\ResponseType;

/**
 * InteractionOpen controller.
 */
class InteractionOpenController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction()
    {
        $attr = $this->get('request')->attributes;
        $em = $this->get('doctrine')->getEntityManager();
        $vars = $attr->get('vars');

        $response = new Response();
        $interactionOpen = $em->getRepository('UJMExoBundle:InteractionOpen')
            ->findOneByQuestion($attr->get('interaction')->getId());

        $form = $this->createForm(new ResponseType(), $response);

        $vars['interactionToDisplayed'] = $interactionOpen;
        $vars['form'] = $form->createView();
        $vars['exoID'] = $attr->get('exoID');

        return $this->render('UJMExoBundle:InteractionOpen:paper.html.twig', $vars);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        $attr = $this->get('request')->attributes;
        $entity = new InteractionOpen();
        $form = $this->createForm(
           new InteractionOpenType(
               $this->container->get('security.token_storage')
                   ->getToken()->getUser()
           ), $entity
       );

        $interOpenSer = $this->container->get('ujm.exo_InteractionOpen');
        $typeOpen = $interOpenSer->getTypeOpen();

        return $this->container->get('templating')->renderResponse(
           'UJMExoBundle:InteractionOpen:new.html.twig', array(
               'exoID' => $attr->get('exoID'),
               'stepID' => $attr->get('stepID'),
               'entity' => $entity,
               'typeOpen' => json_encode($typeOpen),
               'form' => $form->createView(),
           )
       );
    }

    /**
     * Creates a new InteractionOpen entity.
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $interOpenSer = $this->container->get('ujm.exo_InteractionOpen');
        $interOpen = new InteractionOpen();
        $form = $this->createForm(
            new InteractionOpenType(
                $this->container->get('security.token_storage')->getToken()->getUser()
            ), $interOpen
        );

        $exoID = $this->container->get('request')->request->get('exercise');
        $stepID = $this->container->get('request')->request->get('step');

        //Get the lock category
        $catSer = $this->container->get('ujm.exo_category');

        $exercise = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Exercise')->find($exoID);
        $step = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Step')->find($stepID);
        $formHandler = new InteractionOpenHandler(
            $form, $this->get('request'), $this->getDoctrine()->getManager(),
            $this->container->get('ujm.exo_exercise'), $catSer,
            $this->container->get('security.token_storage')->getToken()->getUser(), $exercise, $step,
            $this->get('translator')
        );
        $openHandler = $formHandler->processAdd();
        if ($openHandler === true) {
            $categoryToFind = $interOpen->getQuestion()->getCategory();
            $titleToFind = $interOpen->getQuestion()->getTitle();

            if ($exoID == -1) {
                return $this->redirect(
                    $this->generateUrl('ujm_question_index', array(
                        'categoryToFind' => base64_encode($categoryToFind), 'titleToFind' => base64_encode($titleToFind), )
                    )
                );
            } else {
                 return $this->redirect(
                     $this->generateUrl('ujm_exercise_open', [ 'id' => $exoID ]) . '#/steps'
                );
            }
        }

        if ($openHandler == 'infoDuplicateQuestion') {
            $form->addError(new FormError(
                    $this->get('translator')->trans('info_duplicate_question', array(), 'ujm_exo')
                    ));
        }

        $typeOpen = $interOpenSer->getTypeOpen();
        $formWithError = $this->render(
            'UJMExoBundle:InteractionOpen:new.html.twig', array(
            'entity' => $interOpen,
            'form' => $form->createView(),
            'exoID' => $exoID,
            'stepID' => $stepID,
            'error' => true,
            'typeOpen' => json_encode($typeOpen),
            )
        );
        $interactionType = $this->container->get('ujm.exo_question')->getTypes();
        $formWithError = substr($formWithError, strrpos($formWithError, 'GMT') + 3);

        return $this->render(
            'UJMExoBundle:Question:new.html.twig', array(
            'formWithError' => $formWithError,
            'exoID' => $exoID,
            'stepID' => $stepID,
            'linkedCategory' => $catSer->getLinkedCategories(),
            'locker' => $catSer->getLockCategory(),
            'interactionType' => $interactionType,
            )
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction()
    {
        $attr = $this->get('request')->attributes;
        $openSer = $this->container->get('ujm.exo_InteractionOpen');
        $catSer = $this->container->get('ujm.exo_category');
        $em = $this->get('doctrine')->getEntityManager();

        $interactionOpen = $em->getRepository('UJMExoBundle:InteractionOpen')
            ->findOneByQuestion($attr->get('interaction')->getId());

        $catSer->ctrlCategory($interactionOpen->getQuestion());

        $editForm = $this->createForm(
            new InteractionOpenType($attr->get('user'), $attr->get('catID')), $interactionOpen
        );

        if ($attr->get('exoID') != -1) {
            $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($attr->get('exoID'));
            $variables['_resource'] = $exercise;
        }

        $typeOpen = $openSer->getTypeOpen();
        $linkedCategory = $catSer->getLinkedCategories();

        $variables['entity'] = $interactionOpen;
        $variables['edit_form'] = $editForm->createView();
        $variables['nbResponses'] = $openSer->getNbReponses($attr->get('interaction'));
        $variables['linkedCategory'] = $linkedCategory;
        $variables['typeOpen'] = json_encode($typeOpen);
        $variables['exoID'] = $attr->get('exoID');
        $variables['locker'] = $catSer->getLockCategory();

        if ($attr->get('exoID') != -1) {
            $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($attr->get('exoID'));
            $variables['_resource'] = $exercise;
        }

        return $this->render('UJMExoBundle:InteractionOpen:edit.html.twig', $variables);
    }

    /**
     * Edits an existing InteractionOpen entity.
     *
     *
     * @param int $id id of InteractionOpen
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction($id)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $exoID = $this->container->get('request')->request->get('exercise');
        $catID = -1;

        $em = $this->getDoctrine()->getManager();

        $interOpen = $em->getRepository('UJMExoBundle:InteractionOpen')->find($id);

        if (!$interOpen) {
            throw $this->createNotFoundException('Unable to find InteractionOpen entity.');
        }

        if ($user->getId() != $interOpen->getQuestion()->getUser()->getId()) {
            $catID = $interOpen->getQuestion()->getCategory()->getId();
        }

        $editForm = $this->createForm(
            new InteractionOpenType(
                $this->container->get('security.token_storage')->getToken()->getUser(),
                $catID
            ), $interOpen
        );

        $formHandler = new InteractionOpenHandler(
            $editForm, $this->get('request'), $this->getDoctrine()->getManager(),
            $this->container->get('ujm.exo_exercise'), $this->container->get('ujm.exo_category'),
            $this->container->get('security.token_storage')->getToken()->getUser(), -1,
            $this->get('translator')
        );

        if ($formHandler->processUpdate($interOpen)) {
            if ($exoID == -1) {
                return $this->redirect($this->generateUrl('ujm_question_index'));
            } else {
                return $this->redirect(
                    $this->generateUrl('ujm_exercise_open', [ 'id' => $exoID ]) . '#/steps'
                );
            }
        }

        return $this->forward(
            'UJMExoBundle:Question:edit', array(
                'exoID' => $exoID,
                'id' => $interOpen->getQuestion()->getId(),
                'form' => $editForm,
            )
        );
    }

    /**
     * Deletes a InteractionOpen entity.
     *
     *
     * @param int    $id      id of InteractionOpen
     * @param intger $pageNow for pagination, actual page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id, $pageNow)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('UJMExoBundle:InteractionOpen')->find($id);
        //Deleting of relations, if there the question is shared
        $sharesQuestion = $em->getRepository('UJMExoBundle:Share')->findBy(array('question' => $entity->getQuestion()->getId()));
        foreach ($sharesQuestion as $share){
            $em->remove($share);
        }
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InteractionOpen entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('ujm_question_index', array('pageNow' => $pageNow)));
    }

    /**
     * To test the open question by the teacher.
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function responseOpenAction()
    {
        $vars = array();
        $request = $this->get('request');
        $postVal = $req = $request->request->all();

        if ($postVal['exoID'] != -1) {
            $exercise = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Exercise')->find($postVal['exoID']);
            $vars['_resource'] = $exercise;
        }

        $interSer = $this->container->get('ujm.exo_InteractionOpen');
        $res = $interSer->response($request);

        $vars['interOpen'] = $res['interOpen'];
        $vars['penalty'] = $res['penalty'];
        $vars['response'] = $res['response'];
        $vars['score'] = $res['score'];
        $vars['tempMark'] = $res['tempMark'];
        $vars['exoID'] = $postVal['exoID'];

        return $this->render('UJMExoBundle:InteractionOpen:openOverview.html.twig', $vars);
    }
}
