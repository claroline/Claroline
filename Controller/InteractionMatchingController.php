<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;

use UJM\ExoBundle\Entity\InteractionMatching;
use UJM\ExoBundle\Form\InteractionMatchingType;
use UJM\ExoBundle\Form\InteractionMatchingHandler;

/**
 * InteractionMatching Controller
 *
 */
class InteractionMatchingController extends Controller
{

    /**
     * Creates a new InteractionMatching entity.
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $interMatchSer = $this->container->get('ujm.InteractionMatching');
        $interMatching = new InteractionMatching();
        $form = $this->createForm(
            new InteractionMatchingType(
                $this->container->get('security.token_storage')
                                ->getToken()->getUser()
            ), $interMatching
        );

        $exoID = $this->container->get('request')->request->get('exercise');

        //Get the lock category
        $user = $this->container->get('security.token_storage')->getToken()->getUser()->getId();
        $Locker = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Category')->getCategoryLocker($user);
        if (empty($Locker)) {
            $catLocker = "";
        } else {
            $catLocker = $Locker[0];
        }

        $exercise = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Exercise')->find($exoID);
        $formHandler = new InteractionMatchingHandler(
                $form, $this->get('request'), $this->getDoctrine()->getManager(),
                $this->container->get('ujm.exo_exercise'),
                $this->container->get('security.token_storage')->getToken()->getUser(), $exercise,
                $this->get('translator')
         );
        $matchingHandler = $formHandler->processAdd();
        if ( $matchingHandler === TRUE ) {
            $categoryToFind = $interMatching->getInteraction()->getQuestion()->getCategory();
            $titleToFind = $interMatching->getInteraction()->getQuestion()->getTitle();

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
        }

        if ($matchingHandler == 'infoDuplicateQuestion') {
            $form->addError(new FormError(
                    $this->get('translator')->trans('infoDuplicateQuestion')
                    ));
        }

        $typeMatching = $interMatchSer->getTypeMatching();
        $formWithError = $this->render(
            'UJMExoBundle:InteractionMatching:new.html.twig', array(
            'entity' => $interMatching,
            'form'   => $form->createView(),
            'error'  => true,
            'exoID'  => $exoID,
            'typeMatching' => json_encode($typeMatching)
            )
        );

        $formWithError = substr($formWithError, strrpos($formWithError, 'GMT') + 3);

        return $this->render(
            'UJMExoBundle:Question:new.html.twig', array(
            'formWithError' => $formWithError,
            'exoID'  => $exoID,
            'linkedCategory' =>  $this->container->get('ujm.exo_question')->getLinkedCategories(),
            'locker' => $catLocker
            )
        );

    }

    /**
     * Edits an existing InteractionMatching entity.
     *
     * @access public
     *
     * @param integer $id id of InteractionMatching
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

        if ( $user->getId() != $interMatching->getInteraction()->getQuestion()->getUser()->getId() ) {
            $catID = $interMatching->getInteraction()->getQuestion()->getUser()->getId();
        }

        $editForm = $this->createForm(
            new InteractionMatchingType(
                $this->container->get('security.token_storage')->getToken()->getUser(),
                $catID
            ),$interMatching
        );
        $formHandler = new InteractionMatchingHandler(
            $editForm, $this->get('request'), $this->getDoctrine()->getManager(),
            $this->container->get('ujm.exo_exercise'),
            $this->container->get('security.token_storage')->getToken()->getUser(),
            $this->get('translator')
        );

        if ( $formHandler->processUpdate($interMatching) ) {
            if ( $exoID == -1 ) {
                return $this->redirect($this->generateUrl('ujm_question_index'));
            } else {
                return $this->redirect(
                    $this->generateUrl(
                        'ujm_exercise_questions',
                            array(
                                'id' => $exoID
                            )
                    )
                );
            }
        }

        return $this->forward(
            'UJMExoBundle:Question:edit', array(
                'exoID' => $exoID,
                'id'    => $interMatching->getInteraction()->getQuestion()->getId(),
                'form'  => $editForm
            )
        );
    }

    /**
     * Deletes a InteractionMatching entity.
     *
     * @access public
     *
     * @param integer $id id of InteractionMatching
     * @param intger $pageNow for pagination, actual page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id, $pageNow)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('UJMExoBundle:InteractionMatching')->find($id);

        if ( !$entity ) {
            throw $this->createNotFoundException('Enable to find InteractionMatching entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('ujm_question_index', array('pageNow' => $pageNow)));
    }

    /**
     * To test the Matching by the teacher
     *
     * @access public
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

        $interSer = $this->container->get('ujm.exo_InteractionMatching');
        $res = $interSer->response($request);

        $vars['score']            = $res['score'];
        $vars['penalty']          = $res['penalty'];
        $vars['interMatching']    = $res['interMatching'];
        $vars['tabRightResponse'] = $res['tabRightResponse'];
        $vars['tabResponseIndex'] = $res['tabResponseIndex'];
        $vars['exoID']            = $postVal['exoID'];

        return $this->render('UJMExoBundle:InteractionMatching:matchingOverview.html.twig', $vars);
    }
}
