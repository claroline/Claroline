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

use UJM\ExoBundle\Entity\Document;

/**
 * WSRest controller.
 * To create REST WS
 *
 */
class WSRestController extends Controller
{

    /**
     * To add a document with the plugin advimage with tinyMCEBundle
     *
     */
    public function postDocumentAddAction()
    {
        //on poste les données label,url, type, login
        //le login permet de lier le doc à un user + de vérifier que le login correspond bien à l'user connecté

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $userDir = './bundles/ujmexo/users_documents/'.$this->container->get('security.context')
                ->getToken()->getUser()->getUsername();

            if (!is_dir('./bundles/ujmexo/users_documents/')) {
                mkdir('./bundles/ujmexo/users_documents/');
            }

            if (!is_dir($userDir)) {
                $dirs = array('audio','images','media','video');
                mkdir($userDir);

                foreach ($dirs as $dir) {
                    mkdir($userDir.'/'.$dir);
                }
            }

            if ((isset($_FILES['picture'])) && ($_FILES['picture'] != '')) {
                $file = basename($_FILES['picture']['name']);
                move_uploaded_file($_FILES['picture']['tmp_name'], $userDir.'/images/'. $file);

                $em = $this->getDoctrine()->getEntityManager();
                $document = new Document();

                $document->setLabel($_POST['label']);
                $document->setUrl($userDir.'/images/'. $file);
                $document->setType(strrchr($file, '.'));
                $document->setUser($this->container->get('security.context')->getToken()->getUser());

                $em->persist($document);

                $em->flush();
            }

            return $this->render(
                'UJMExoBundle:InteractionGraphic:page.html.twig',
                array(
                    'idDoc' => $document->getId(),
                    'label' => $document->getLabel()
                )
            );
        } else {
            return 'Not authorized';
        }
    }
}