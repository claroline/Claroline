<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @access public
     *
     * @param integer $exoID id of exercise
     * @param integer $page for the pagination, page destination
     * @param boolean $all for use or not use the pagination
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($exoID, $page, $all)
    {
        $nbUserPaper = 0;
        $retryButton = false;
        $nbAttemptAllowed = -1;
        $exerciseSer = $this->container->get('ujm.exo_exercise');
        $badgeExoSer = $this->container->get('ujm.exo_badge');

        $arrayMarkPapers = array();

        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
        $workspace = $exercise->getResourceNode()->getWorkspace();

        $exoAdmin = $exerciseSer->isExerciseAdmin($exercise);

        $this->checkAccess($exercise);

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
            $arrayMarkPapers[$p->getId()] = $this->container->get('ujm.exo_exercise')->getInfosPaper($p);
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

        $badgesInfoUser = $badgeExoSer->badgesInfoUser(
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
     * @access public
     *
     * @param integer $id id of paper
     * @param integer $p to chose the elements to display (marks, question correction ...)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($id, $p = -2)
    {
        $nbAttemptAllowed = -1;
        $retryButton = false;
        $exerciseSer = $this->container->get('ujm.exo_exercise');
        $badgeExoSer = $this->container->get('ujm.exo_badge');

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $paper = $em->getRepository('UJMExoBundle:Paper')->find($id);
        $exercise = $paper->getExercise();

        if ($exerciseSer->controlMaxAttemps($exercise,
                $user, $exerciseSer->isExerciseAdmin($exercise))) {
            $retryButton = true;
        }

        if ($this->container->get('ujm.exo_exercise')->isExerciseAdmin($paper->getExercise())) {
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

        $badgesInfoUser = $badgeExoSer->badgesInfoUser(
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

    /**
     * To display the modal to mark an open question
     *
     * @access public
     *
     * @param integer $respid id of reponse
     * @param integer $maxScore score maximun for the open question
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function markedOpenAction($respid, $maxScore)
    {
        return $this->render(
            'UJMExoBundle:Paper:q_open_mark.html.twig', array(
                'respid'   => $respid,
                'maxScore' => $maxScore

            )
        );
    }

    /**
     * To record the score for a response of an open question
     *
     * @access public
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function markedOpenRecordAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            /** @var \UJM\ExoBundle\Entity\Response $response */
            $response = $em->getRepository('UJMExoBundle:Response')->find($request->get('respid'));

            $response->setMark($request->get('mark'));

            $em->persist($response);
            $em->flush();

            $this->container->get('ujm.exo_exercise')->manageEndOfExercise($response->getPaper());

            return new Response($response->getId());
        } else {
            return new Response('Error');
        }
    }

    /**
     * To search paper
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchUserPaperAction()
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $papersOneUser   = array();
        $papersUser      = array();
        $arrayMarkPapers = array();

        $display = 'none';

        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();

        $nameUser = $request->query->get('userName');
        $exoID    = $request->query->get('exoID');

        $userList = $em->getRepository('ClarolineCoreBundle:User')->findByName($nameUser);
        $end = count($userList);

        for ($i = 0; $i < $end; $i++) {
            $papersOneUser[] = $em->getRepository('UJMExoBundle:Paper')
                                  ->findBy(array(
                                            'user' => $userList[$i]->getId(),
                                            'exercise' => $exoID
                                            )
                                          );

            if ($i > 0) {
                $papersUser = array_merge($papersOneUser[$i - 1], $papersOneUser[$i]);
            } else {
                $papersUser = $papersOneUser[$i];
            }
        }

        foreach ($papersUser as $p) {
            $arrayMarkPapers[$p->getId()] = $this->container->get('ujm.exo_exercise')->getInfosPaper($p);
        }

        if(count($papersUser) > 0) {
            $display = $this->ctrlDisplayPaper($user, $papersUser[0]);
        }

        $divResultSearch = $this->render(
            'UJMExoBundle:Paper:userPaper.html.twig', array(
                'papers'          => $papersUser,
                'arrayMarkPapers' => $arrayMarkPapers,
                'display'         => $display
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
     * @access public
     *
     * @param integer $exerciseId id of exercise
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportResCSVAction($exerciseId)
    {
        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exerciseId);

        if ($this->container->get('ujm.exo_exercise')->isExerciseAdmin($exercise)) {
            $iterableResult = $this->getDoctrine()
                                   ->getManager()
                                   ->getRepository('UJMExoBundle:Paper')
                                   ->getExerciseAllPapersIterator($exerciseId);
            $handle = fopen('php://memory', 'r+');

            while (false !== ($row = $iterableResult->next())) {
                $rowCSV = array();
                $infosPaper = $this->container->get('ujm.exo_exercise')->getInfosPaper($row[0]);
                $score = $infosPaper['scorePaper'] /  $infosPaper['maxExoScore'];
                $score = $score * 20;

                $rowCSV[] = $row[0]->getUser()->getLastName() . '-' . $row[0]->getUser()->getFirstName();
                $rowCSV[] = $row[0]->getNumPaper();
                $rowCSV[] = $row[0]->getStart()->format('Y-m-d H:i:s');
                if ($row[0]->getEnd()) {
                    $rowCSV[] = $row[0]->getEnd()->format('Y-m-d H:i:s');
                } else {
                    $rowCSV[] = $this->get('translator')->trans('noFinish');
                }
                $rowCSV[] = $row[0]->getInterupt();
                $rowCSV[] = $this->container->get('ujm.exo_exercise')->roundUpDown($score);

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

            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }
    }

    /**
     * To check the right to open exo or not
     *
     * @access private
     *
     * @param \UJM\ExoBundle\Entity\Exercise $exo
     *
     * @return exception
     */
    private function checkAccess($exo)
    {
        $collection = new ResourceCollection(array($exo->getResourceNode()));

        if (!$this->get('security.authorization_checker')->isGranted('OPEN', $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    /**
     * To control if the user is allowed to display the paper
     *
     * @access private
     *
     * @param \Claroline\CoreBundle\Entity\User $user user connected
     * @param \UJM\ExoBundle\Entity\Paper $paper paper to display
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function ctrlDisplayPaper($user, $paper)
    {
        $display = 'none';

        if (($this->container->get('ujm.exo_exercise')->isExerciseAdmin($paper->getExercise())) ||
            ($user->getId() == $paper->getUser()->getId()) &&
            (($paper->getExercise()->getCorrectionMode() == 1) ||
            (($paper->getExercise()->getCorrectionMode() == 3) &&
            ($paper->getExercise()->getDateCorrection()->format('Y-m-d H:i:s') <= date("Y-m-d H:i:s"))) ||
            (($paper->getExercise()->getCorrectionMode() == 2) &&
            ($paper->getExercise()->getMaxAttempts() <= $this->container->get('ujm.exo_exercise')->getNbPaper(
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
