<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use UJM\ExoBundle\Entity\InteractionMatching;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Form\InteractionMatchingType;
use UJM\ExoBundle\Form\ResponseType;
use UJM\ExoBundle\Form\InteractionMatchingHandler;

/**
 * InteractionMatching Controller.
 */
class InteractionMatchingController extends Controller
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
        $interactionMatching = $em->getRepository('UJMExoBundle:InteractionMatching')
            ->findOneByQuestion($attr->get('interaction')->getId());

        if ($interactionMatching->getShuffle()) {
            $interactionMatching->shuffleProposals();
            $interactionMatching->shuffleLabels();
        } else {
            $interactionMatching->sortProposals();
            $interactionMatching->sortLabels();
        }

        $form = $this->createForm(new ResponseType(), $response);

        $vars['interactionToDisplayed'] = $interactionMatching;
        $vars['form'] = $form->createView();
        $vars['exoID'] = $attr->get('exoID');

        return $this->render('UJMExoBundle:InteractionMatching:paper.html.twig', $vars);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        $attr = $this->get('request')->attributes;
        $entity = new InteractionMatching();
        $form = $this->createForm(
           new InteractionMatchingType(
               $this->container->get('security.token_storage')
                   ->getToken()->getUser()
           ), $entity
       );

       $interMatchSer = $this->container->get('ujm.exo_UJM\ExoBundle\Entity\InteractionMatching');
       $typeMatching = $interMatchSer->getTypeMatching();

        return $this->container->get('templating')->renderResponse(
           'UJMExoBundle:InteractionMatching:new.html.twig', array(
           'exoID' => $attr->get('exoID'),
           'entity' => $entity,
           'typeMatching' => json_encode($typeMatching),
           'form' => $form->createView(),
           )
       );
    }

    /**
     * Creates a new InteractionMatching entity.
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $interMatchSer = $this->container->get('ujm.exo_UJM\ExoBundle\Entity\InteractionMatching');
        $interMatching = new InteractionMatching();
        $form = $this->createForm(
            new InteractionMatchingType(
                $this->container->get('security.token_storage')
                                ->getToken()->getUser()
            ), $interMatching
        );

        $exoID = $this->container->get('request')->request->get('exercise');

        //Get the lock category
        $catSer = $this->container->get('ujm.exo_category');

        $exercise = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Exercise')->find($exoID);
        $formHandler = new InteractionMatchingHandler(
                $form, $this->get('request'), $this->getDoctrine()->getManager(),
                $this->container->get('ujm.exo_exercise'),
                $this->container->get('security.token_storage')->getToken()->getUser(), $exercise,
                $this->get('translator')
         );
        $matchingHandler = $formHandler->processAdd();

        if ($matchingHandler === true) {
            $categoryToFind = $interMatching->getQuestion()->getCategory();
            $titleToFind = $interMatching->getQuestion()->getTitle();

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
        }

        if ($matchingHandler == 'infoDuplicateQuestion') {
            $form->addError(new FormError(
                    $this->get('translator')->trans('info_duplicate_question', array(), 'ujm_exo')
                    ));
        }

        $typeMatching = $interMatchSer->getTypeMatching();
        $formWithError = $this->render(
            'UJMExoBundle:InteractionMatching:new.html.twig', array(
            'entity' => $interMatching,
            'form' => $form->createView(),
            'error' => true,
            'exoID' => $exoID,
            'typeMatching' => json_encode($typeMatching),
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
    public function editAction()
    {
        $attr = $this->get('request')->attributes;
        $matchSer = $this->container->get('ujm.exo_UJM\ExoBundle\Entity\InteractionMatching');
        $catSer = $this->container->get('ujm.exo_category');
        $em = $this->get('doctrine')->getEntityManager();

        $interactionMatching = $em->getRepository('UJMExoBundle:InteractionMatching')
                                  ->getInteractionMatching($attr->get('interaction')->getId());

        $correspondence = $matchSer->initTabRightResponse($interactionMatching);
        foreach ($correspondence as $key => $corresp) {
            $correspondence[$key] = explode('-', $corresp);
        }
        $tableLabel = array();
        $tableProposal = array();

        $ind = 1;

        foreach ($interactionMatching->getLabels() as $label) {
            $tableLabel[$ind] = $label->getId();
            ++$ind;
        }

        $ind = 1;
        foreach ($interactionMatching->getProposals() as $proposal) {
            $tableProposal[$proposal->getId()] = $ind;
            ++$ind;
        }

        $editForm = $this->createForm(
            new InteractionMatchingType($attr->get('user'), $attr->get('catID')), $interactionMatching
        );

        $typeMatching = $matchSer->getTypeMatching();
        $linkedCategory = $catSer->getLinkedCategories();

        $variables['entity'] = $interactionMatching;
        $variables['edit_form'] = $editForm->createView();
        $variables['nbResponses'] = $matchSer->getNbReponses($attr->get('interaction'));
        $variables['linkedCategory'] = $linkedCategory;
        $variables['typeMatching'] = json_encode($typeMatching);
        $variables['exoID'] = $attr->get('exoID');
        $variables['correspondence'] = json_encode($correspondence);
        $variables['tableLabel'] = json_encode($tableLabel);
        $variables['tableProposal'] = json_encode($tableProposal);
        $variables['locker'] = $catSer->getLockCategory();

        if ($attr->get('exoID') != -1) {
            $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($attr->get('exoID'));
            $variables['_resource'] = $exercise;
        }

        return $this->render('UJMExoBundle:InteractionMatching:edit.html.twig', $variables);
    }

    /**
     * Edits an existing InteractionMatching entity.
     *
     *
     * @param int $id id of InteractionMatching
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction($id)
    {
        $exoID = $this->container->get('request')->request->get('exercise');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $catID = -1;

        $em = $this->getdoctrine()->getManager();

        $interMatching = $em->getRepository('UJMExoBundle:InteractionMatching')->find($id);

        if (!$interMatching) {
            throw $this->createNotFoundException('Enable to find InteractionMatching entity.');
        }

        if ( $user->getId() != $interMatching->getQuestion()->getUser()->getId() ) {
            $catID = $interMatching->getQuestion()->getUser()->getId();
        }

        $editForm = $this->createForm(
            new InteractionMatchingType(
                $this->container->get('security.token_storage')->getToken()->getUser(),
                $catID
            ), $interMatching
        );
        $formHandler = new InteractionMatchingHandler(
            $editForm, $this->get('request'), $this->getDoctrine()->getManager(),
            $this->container->get('ujm.exo_exercise'),
            $this->container->get('security.token_storage')->getToken()->getUser(),
            $this->get('translator')
        );

        if ($formHandler->processUpdate($interMatching)) {
            if ($exoID == -1) {
                return $this->redirect($this->generateUrl('ujm_question_index'));
            } else {
                return $this->redirect(
                    $this->generateUrl(
                        'ujm_exercise_questions',
                            array(
                                'id' => $exoID,
                            )
                    )
                );
            }
        }

        return $this->forward(
            'UJMExoBundle:Question:edit', array(
                'exoID' => $exoID,
                'id' => $interMatching->getQuestion()->getId(),
                'form' => $editForm,
            )
        );
    }

    /**
     * Deletes a InteractionMatching entity.
     *
     *
     * @param int    $id      id of InteractionMatching
     * @param intger $pageNow for pagination, actual page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id, $pageNow)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('UJMExoBundle:InteractionMatching')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Enable to find InteractionMatching entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('ujm_question_index', array('pageNow' => $pageNow)));
    }

    /**
     * To test the Matching by the teacher.
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function responseMatchingAction()
    {
        $vars = array();
        $request = $this->get('request');
        $postVal = $request->request->all();

        if ($postVal['exoID'] != -1) {
            $exercise = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Exercise')->find($postVal['exoID']);
            $vars['_resource'] = $exercise;
        }

        $interSer = $this->container->get('ujm.exo_UJM\ExoBundle\Entity\InteractionMatching');
        $res = $interSer->response($request);

        $vars['score'] = $res['score'];
        $vars['penalty'] = $res['penalty'];
        $vars['interMatching'] = $res['interMatching'];
        $vars['tabRightResponse'] = $res['tabRightResponse'];
        $vars['tabResponseIndex'] = $res['tabResponseIndex'];
        $vars['exoID'] = $postVal['exoID'];

        return $this->render('UJMExoBundle:InteractionMatching:matchingOverview.html.twig', $vars);
    }
}
