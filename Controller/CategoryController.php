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
            $entity  = new Category();
            $entity->setValue($val);
            $entity->setUser($this->container->get('security.context')->getToken()->getUser());
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