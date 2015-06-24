<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use UJM\ExoBundle\Entity\Category;
use UJM\ExoBundle\Form\CategoryType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Category controller.
 *
 */
class CategoryController extends Controller
{

    /**
     * Displays a form to create a new Category entity.
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        $entity = new Category();
        $form   = $this->createForm(new CategoryType(), $entity);

        return $this->render(
            'UJMExoBundle:Category:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
            )
        );
    }

    /**
     * Displays a form to create a new Category entity in AJAX window.
     *
     * @access public
     *
     * @param integer $edit 0 or 1 new category or editcategory
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newPopAction($edit)
    {
        $entity = new Category();
        $form   = $this->createForm(new CategoryType(), $entity);

        return $this->render(
            'UJMExoBundle:Category:new_pop.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'edit'    => $edit
            )
        );
    }



    /**
     * Record a new Category entity to the AJAX form.
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createPopAction()
    {
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            $val = $request->request->get('value');
            $lock = $request->request->get('locker');
            $entity  = new Category();
            $entity->setValue($val);
            $entity->setLocker($lock);
            $entity->setUser($this->container->get('security.token_storage')
                                             ->getToken()->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return new Response($entity->getId());

        } else {

            return 0;
        }
    }

    /**
     * Drop a Category.
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dropAction()
    {
        $request = $this->container->get('request');
        $em = $this->getDoctrine()->getManager();

        if ($request->isXmlHttpRequest()) {
            $idCategory = $request->request->get('idCategory');

            $entity = $em->getRepository('UJMExoBundle:Category')->find($idCategory);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Category entity.');
            }

            $em->remove($entity);
            $em->flush();

            return new Response($entity->getId());

        } else {

            return 0;
        }
    }

    /**
     * Alter a Category.
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function alterAction()
    {

        $request = $this->container->get('request');
        $em = $this->getDoctrine()->getManager();

        if ($request->isXmlHttpRequest()) {
            $newlabel = $request->request->get('newlabel');
            $idOldCategory = $request->request->get('idOldCategory');

            $entity = $em->getRepository('UJMExoBundle:Category')->find($idOldCategory);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Category entity.');
            }

            $entity->setValue($newlabel);

            $em->persist($entity);
            $em->flush();

            return new Response($entity->getId());

        } else {

            return 0;
        }
    }

}
