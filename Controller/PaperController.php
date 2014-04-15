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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Form\PaperType;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;

/**
 * Paper controller.
 *
 */
class PaperController extends Controller
{
    /**
     * Lists all Paper entities.
     *
     */
    public function indexAction($exoID, $page, $all)
    {
        $nbUserPaper = 0;
        $retryButton = false;
        $nbAttemptAllowed = -1;
        $exerciseSer = $this->container->get('ujm.exercise_services');

        $arrayMarkPapers = array();

        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
        $workspace = $exercise->getResourceNode()->getWorkspace();
        
        $exoAdmin = $exerciseSer->isExerciseAdmin($exercise);

        $this->checkAccess($exercise);

        /*if (count($subscription) < 1) {
            return $this->redirect($this->generateUrl('exercise_show', array('id' => $exoID)));
        }*/

        if ($exoAdmin === true) {
            $paper = $this->getDoctrine()
                            ->getManager()
                            ->getRepository('UJMExoBundle:Paper')
                            ->getExerciseAllPapers($exoID);
            $nbUserPaper = $exerciseSer->getNbPaper($user->getId(),
                                                    $exercise->getId());
        } else {
            $paper = $this->getDoctrine()
                            ->getManager()
                            ->getRepository('UJMExoBundle:Paper')
                            ->getExerciseUserPapers($user->getId(), $exoID);
            $nbUserPaper = count($paper);
        }

        // Pagination of the paper list
        if ($all == 1) {
            $max = count($paper);
        } else {
            $max = 10; // Max per page
        }

        $adapter = new ArrayAdapter($paper);
        $pagerfanta = new Pagerfanta($adapter);

        try {
            $papers = $pagerfanta
                ->setMaxPerPage($max)
                ->setCurrentPage($page)
                ->getCurrentPageResults();
        } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
            throw $this->createNotFoundException("Cette page n'existe pas.");
        }

        if (count($paper) > 0) {
            $display = $this->ctrlDisplayPaper($user, $paper[0]);
        } else {
            $display = 'all';
        }

        foreach ($paper as $p) {
            $arrayMarkPapers[$p->getId()] = $this->container->get('ujm.exercise_services')->getInfosPaper($p);
        }

        if (($exerciseSer->controlDate($exoAdmin, $exercise) === true)
            && ($exerciseSer->controlMaxAttemps($exercise, $user, $exoAdmin) === true)
            && ( ($exercise->getPublished() === true) || ($exoAdmin == 1) )
        ) {
            $retryButton = true;
        }

        if ($exercise->getMaxAttempts() > 0) {
            if ($exoAdmin === false) {
                $nbAttemptAllowed = $exercise->getMaxAttempts() - count($paper);
            }
        }

        $badgesInfoUser = $exerciseSer->badgesInfoUser(
                $user->getId(), $exercise->getResourceNode()->getId(),
                $this->container->getParameter('locale'));

        $nbQuestions = $em->getRepository('UJMExoBundle:ExerciseQuestion')
                          ->getCountQuestion($exoID);

        return $this->render(
            'UJMExoBundle:Paper:index.html.twig',
            array(
                'workspace'        => $workspace,
                'papers'           => $papers,
                'isAdmin'          => $exoAdmin,
                'pager'            => $pagerfanta,
                'exoID'            => $exoID,
                'display'          => $display,
                'retryButton'      => $retryButton,
                'nbAttemptAllowed' => $nbAttemptAllowed,
                'badgesInfoUser'   => $badgesInfoUser,
                'nbUserPaper'      => $nbUserPaper,
                'nbQuestions'      => $nbQuestions['nbq'],
                '_resource'        => $exercise,
                'arrayMarkPapers'  => $arrayMarkPapers
            )
        );
    }

    /**
     * Finds and displays a Paper entity.
     *
     */
    public function showAction($id, $p = -2)
    {
        $nbAttemptAllowed = -1;
        $retryButton = false;
        $exerciseSer = $this->container->get('ujm.exercise_services');

        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $paper = $em->getRepository('UJMExoBundle:Paper')->find($id);
        $exercise = $paper->getExercise();

        if ($exerciseSer->controlMaxAttemps($exercise,
                $user, $exerciseSer->isExerciseAdmin($exercise))) {
            $retryButton = true;
        }

        if ($this->container->get('ujm.exercise_services')->isExerciseAdmin($paper->getExercise())) {
            $admin = 1;
        } else {
            $admin = 0;
        }

        $worspace = $paper->getExercise()->getResourceNode()->getWorkspace();

        $display = $this->ctrlDisplayPaper($user, $paper);

        if ((($this->checkAccess($paper->getExercise())) && ($paper->getEnd() == null)) || ($display == 'none')) {
            return $this->redirect($this->generateUrl('ujm_exercise_open', array('exerciseId' => $paper->getExercise()->getId())));
        }

        $infosPaper = $exerciseSer->getInfosPaper($paper);

        $hintViewed = $this->getDoctrine()
            ->getManager()
            ->getRepository('UJMExoBundle:LinkHintPaper')
            ->getHintViewed($paper->getId());

        $nbMaxQuestion = count($infosPaper['interactions']);

        $badgesInfoUser = $exerciseSer->badgesInfoUser(
                $user->getId(), $exercise->getResourceNode()->getId(),
                $this->container->getParameter('locale'));

        if ($exercise->getMaxAttempts() > 0) {
            if (!$exerciseSer->isExerciseAdmin($exercise)) {
                $nbpaper = $exerciseSer->getNbPaper($user->getId(),
                                                    $exercise->getId());

                $nbAttemptAllowed = $exercise->getMaxAttempts() - $nbpaper;
            }
        }

        return $this->render(
            'UJMExoBundle:Paper:show.html.twig',
            array(
                'workspace'        => $worspace,
                'exoId'            => $paper->getExercise()->getId(),
                'interactions'     => $infosPaper['interactions'],
                'responses'        => $infosPaper['responses'],
                'scorePaper'       => $infosPaper['scorePaper'],
                'scoreTemp'        => $infosPaper['scoreTemp'],
                'maxExoScore'      => $infosPaper['maxExoScore'],
                'hintViewed'       => $hintViewed,
                'correction'       => $paper->getExercise()->getCorrectionMode(),
                'display'          => $display,
                'admin'            => $admin,
                'nbAttemptAllowed' => $nbAttemptAllowed,
                'badgesInfoUser'   => $badgesInfoUser,
                '_resource'        => $paper->getExercise(),
                'p'                => $p,
                'nbMaxQuestion'    => $nbMaxQuestion,
                'paperID'          => $paper->getId(),
                'retryButton'      => $retryButton
            )
        );
    }

    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('UJMExoBundle:Paper')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Paper entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('paper'));
    }

    public function markedOpenAction($respid, $maxScore)
    {
        return $this->render(
            'UJMExoBundle:Paper:q_open_mark.html.twig', array(
                'respid'   => $respid,
                'maxScore' => $maxScore

            )
        );
    }

    public function markedOpenRecordAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            /** @var \UJM\ExoBundle\Entity\Response $response */
            $response = $em->getRepository('UJMExoBundle:Response')->find($request->get('respid'));

            $response->setMark($request->get('mark'));

            $em->persist($response);
            $em->flush();

            $this->container->get('ujm.exercise_services')->manageEndOfExercise($response->getPaper());

            return new Response($response->getId());
        } else {
            return new Response('Error');
        }
    }

    public function searchUserPaperAction()
    {
        $papersOneUser = array();

        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();

        $nameUser = $request->query->get('userName');

        $userList = $em->getRepository('ClarolineCoreBundle:User')->findByName($nameUser);
        $end = count($userList);

        for ($i = 0; $i < $end; $i++) {
            $papersOneUser[] = $em->getRepository('UJMExoBundle:Paper')->getPaperUser($userList[$i]->getId());

            if ($i > 0) {
                $papersUser = array_merge($papersOneUser[$i - 1], $papersOneUser[$i]);
            } else {
                $papersUser = $papersOneUser[$i];
            }
        }

        foreach ($papersUser as $p) {
            $arrayMarkPapers[$p->getId()] = $this->container->get('ujm.exercise_services')->getInfosPaper($p);
        }

        $divResultSearch = $this->render(
            'UJMExoBundle:Paper:userPaper.html.twig', array(
                'papers'    => $papersUser,
                'arrayMarkPapers' => $arrayMarkPapers
            )
        );
        // If request is ajax (first display of the first search result (page = 1))
        if ($request->isXmlHttpRequest()) {
            return $divResultSearch; // Send the twig with the result
        } else {
            // Cut the header of the request to only have the twig with the result
            $divResultSearch = substr($divResultSearch, strrpos($divResultSearch, '<link'));

            // Send the form to search and the result
            return $this->render(
                'UJMExoBundle:Paper:index.html.twig', array(
                'divResultSearch' => $divResultSearch
                )
            );
        }
    }

    /**
     * To export results in CSV
     *
     */
    public function exportResCSVAction($exerciseId)
    {
        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exerciseId);

        if ($this->container->get('ujm.exercise_services')->isExerciseAdmin($exercise)) {
            $iterableResult = $this->getDoctrine()
                                   ->getManager()
                                   ->getRepository('UJMExoBundle:Paper')
                                   ->getExerciseAllPapersIterator($exerciseId);
            $handle = fopen('php://memory', 'r+');

            while (false !== ($row = $iterableResult->next())) {
                $rowCSV = array();
                $infosPaper = $this->container->get('ujm.exercise_services')->getInfosPaper($row[0]);
                $score = $infosPaper['scorePaper'] /  $infosPaper['maxExoScore'];
                $score = $score * 20;

                $rowCSV[] = $row[0]->getUser()->getLastName() . '-' . $row[0]->getUser()->getFirstName();
                $rowCSV[] = $row[0]->getNumPaper();
                $rowCSV[] = $row[0]->getStart()->format('Y-m-d H:i:s');
                $rowCSV[] = $row[0]->getEnd()->format('Y-m-d H:i:s');
                $rowCSV[] = $row[0]->getInterupt();
                $rowCSV[] = $this->container->get('ujm.exercise_services')->roundUpDown($score);

                fputcsv($handle, $rowCSV);
                $em->detach($row[0]);
            }

            rewind($handle);
            $content = stream_get_contents($handle);
            fclose($handle);

            return new Response($content, 200, array(
                'Content-Type' => 'application/force-download',
                'Content-Disposition' => 'attachment; filename="export.csv"'
            ));

        } else {

            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
        }
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
                    ->add('id', 'hidden')
                    ->getForm();
    }

    private function checkAccess($exo)
    {
        $collection = new ResourceCollection(array($exo->getResourceNode()));

        if (!$this->get('security.context')->isGranted('OPEN', $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    private function ctrlDisplayPaper($user, $paper)
    {
        $display = 'none';

        if (($this->container->get('ujm.exercise_services')->isExerciseAdmin($paper->getExercise())) ||
            ($user->getId() == $paper->getUser()->getId()) &&
            (($paper->getExercise()->getCorrectionMode() == 1) ||
            (($paper->getExercise()->getCorrectionMode() == 3) &&
            ($paper->getExercise()->getDateCorrection()->format('Y-m-d H:i:s') <= date("Y-m-d H:i:s"))) ||
            (($paper->getExercise()->getCorrectionMode() == 2) &&
            ($paper->getExercise()->getMaxAttempts() <= $this->container->get('ujm.exercise_services')->getNbPaper(
                $user->getId(), $paper->getExercise()->getId()
            )
            ))
            )
        ) {
            $display = 'all';
        } else if (($user->getId() == $paper->getUser()->getId()) && ($paper->getExercise()->getMarkMode() == 2)) {
            $display = 'score';
        }

        return $display;
    }
}