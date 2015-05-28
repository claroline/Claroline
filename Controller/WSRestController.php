<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use UJM\ExoBundle\Entity\Document;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

use Symfony\Component\HttpFoundation\Response;
/**
 * WSRest controller.
 * To create REST WS
 *
 */
class WSRestController extends Controller
{

    /**
     * To add a document
     *
     * @access public
     *
     * @param boolean $redirection Add document on create/edit graphic question or Add document on manage documents
     * @param integer $pageToGo for the pagination
     * @param integer $maxPage for the pagination
     * @param integer $nbItem for the pagination
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postDocumentAddAction($redirection, $pageToGo, $maxPage, $nbItem)
    {
        // We post the data label, url, type, login
        // Login allow to link a doc and a user
        // check also login matches to the connected user

        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            $userDir = $this->container->getParameter('ujm.param.exo_directory') . '/users_documents/'.$this->container->get('security.context')
                ->getToken()->getUser()->getUsername();

            if (!is_dir($this->container->getParameter('ujm.param.exo_directory'))) {
                mkdir($this->container->getParameter('ujm.param.exo_directory'));
            }
            if (!is_dir($this->container->getParameter('ujm.param.exo_directory') . '/users_documents/')) {
                mkdir($this->container->getParameter('ujm.param.exo_directory') . '/users_documents/');
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

                $em = $this->getDoctrine()->getManager();
                $document = new Document();

                $document->setLabel(trim($_POST['label']));
                $document->setUrl($userDir.'/images/'. $file);
                $document->setType(strrchr($file, '.'));
                $document->setUser($this->container->get('security.token_storage')->getToken()->getUser());

                if ($redirection == 1 || ($redirection == 0 && (
                        strtolower($document->getType()) == '.png' ||
                        strtolower($document->getType()) == '.jpeg' ||
                        strtolower($document->getType()) == '.jpg' ||
                        strtolower($document->getType()) == '.gif' ||
                        strtolower($document->getType()) == '.bmp'))
                ) {

                    $em->persist($document);
                }

                $em->flush();
            }

            // Add document on create/edit graphic question
            if ($redirection == 0) {

                return new Response($document->getId().';'.$document->getLabel().';'.$document->getType());
            // Add document on manage documents
            } else if ($redirection == 1) {

                $user = $this->container->get('security.token_storage')->getToken()->getUser();

                $repository = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Document');

                $listDoc = $repository->findBy(array('user' => $user->getId()));

                // Pagination of documents
                $adapterDoc = new ArrayAdapter($listDoc);
                $pagerDoc = new Pagerfanta($adapterDoc);

                if ($nbItem != 0) {
                    // If new item > max per page, display next page
                    $rest = $nbItem % $maxPage;

                    if ($rest == 0) {
                        $pageToGo += 1;
                    }
                }

                try {
                    $listDocPager = $pagerDoc
                        ->setMaxPerPage($maxPage)
                        ->setCurrentPage($pageToGo)
                        ->getCurrentPageResults();
                } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
                    throw $this->createNotFoundException("Cette page n'existe pas.");
                }

                return $this->render(
                    'UJMExoBundle:Document:manageImg.html.twig',
                    array(
                        'listDoc' => $listDocPager,
                        'pagerDoc' => $pagerDoc
                    )
                );
            }
        } else {
            return 'Not authorized';
        }
    }

    /**
     * to control if the document's name already exist
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function nameAlreadyExistAction()
    {
        $request = $this->container->get('request');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $response = 'not yet';

        if ($request->isXmlHttpRequest()) {
            $label = trim($request->request->get('label'));

            if ($label) {
                $repository = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Document');

                $list = $repository->findBy(array('user' => $user->getId()));

                $end = count($list);

                for ($i = 0; $i < $end; $i++) {
                    if ($list[$i]->getLabel() == $label) {
                        $response = 'already';
                        break;
                    }
                }
            }
        }

        return new Response($response);
    }
}
