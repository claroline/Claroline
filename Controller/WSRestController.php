<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use UJM\ExoBundle\Entity\Picture;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Response;

/**
 * WSRest controller.
 * To create REST WS.
 */
class WSRestController extends Controller
{
    /**
     * To add a picture.
     *
     *
     * @param bool $redirection Add picture on create/edit graphic question or Add picture on manage pictures
     * @param int  $pageToGo    for the pagination
     * @param int  $maxPage     for the pagination
     * @param int  $nbItem      for the pagination
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postPictureAddAction($redirection, $pageToGo, $maxPage, $nbItem)
    {
        // We post the data label, url, type, login
        // Login allow to link a doc and a user
        // check also login matches to the connected user

        $request = $this->container->get('request');
        $fileUp = $request->files->get('picture');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            $userDir = './uploads/ujmexo/users_pictures/'.$this->container->get('security.token_storage')
                ->getToken()->getUser()->getUsername();

            if (!is_dir($this->container->getParameter('ujm.param.exo_directory'))) {
                mkdir($this->container->getParameter('ujm.param.exo_directory'));
            }
            if (!is_dir($this->container->getParameter('ujm.param.exo_directory').'/users_pictures/')) {
                mkdir($this->container->getParameter('ujm.param.exo_directory').'/users_pictures/');
            }

            if (!is_dir($userDir)) {
                $dirs = array('audio','images','media','video');
                mkdir($userDir);

                foreach ($dirs as $dir) {
                    mkdir($userDir.'/'.$dir);
                }
            }
            if ((isset($fileUp)) && ($fileUp != '')) {
                $file = $fileUp->getClientOriginalName();
                $fileUp->move($userDir.'/images/', $fileUp->getClientOriginalName());

                // get height and width of the uploaded picture
                list($width, $height) = getimagesize($userDir.'/images/'.$file);

                $em = $this->getDoctrine()->getManager();
                $picture = new Picture();

                $picture->setLabel(trim($request->get('label')));
                $picture->setUrl($userDir.'/images/'.$file);
                $picture->setType(strrchr($file, '.'));
                $picture->setWidth($width);
                $picture->setHeight($height);
                $picture->setUser($this->container->get('security.token_storage')->getToken()->getUser());

                if ($redirection == 1 || ($redirection == 0 && (
                        strtolower($picture->getType()) == '.png' ||
                        strtolower($picture->getType()) == '.jpeg' ||
                        strtolower($picture->getType()) == '.jpg' ||
                        strtolower($picture->getType()) == '.gif' ||
                        strtolower($picture->getType()) == '.bmp'))
                ) {
                    $em->persist($picture);
                }

                $em->flush();
            }

            // Add picture on create/edit graphic question
            if ($redirection == 0) {
                return new Response($picture->getId().';'.$picture->getLabel().';'.$picture->getType());
            // Add picture on manage pictures
            } elseif ($redirection == 1) {
                $user = $this->container->get('security.token_storage')->getToken()->getUser();

                $repository = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Picture');

                $listDoc = $repository->findBy(array('user' => $user->getId()));

                // Pagination of pictures
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
                    'UJMExoBundle:Picture:manageImg.html.twig',
                    array(
                        'listDoc' => $listDocPager,
                        'pagerDoc' => $pagerDoc,
                    )
                );
            }
        } else {
            return 'Not authorized';
        }
    }

    /**
     * to control if the picture's name already exist.
     *
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
                    ->getRepository('UJMExoBundle:Picture');

                $list = $repository->findBy(array('user' => $user->getId()));

                $end = count($list);

                for ($i = 0; $i < $end; ++$i) {
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
