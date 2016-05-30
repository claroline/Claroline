<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use UJM\ExoBundle\Entity\Exercise;

class QtiController extends Controller
{
    /**
     * Import question in QTI.
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importQuestionAction()
    {
        $request = $this->container->get('request');
        $exoID = $request->get('exerciceID');
        $file = $request->files->get('qtifile');

        if ($file->getMimeType() != 'application/zip') {
            return $this->importError('qti_format_warning', $exoID);
        }

        $qtiRepo = $this->container->get('ujm.exo_qti_repository');
        $qtiRepo->razValues();
        if ($this->extractFiles($qtiRepo) === false) {
            return $this->importError('qti can\'t open zip', $exoID);
        }

        if ($exoID == -1) {
            $scanFile = $qtiRepo->scanFiles();
        } else {
            $em = $this->getDoctrine()->getManager();
            $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
            $step = $this->container->get('ujm.exo_exercise')->createStep($exercise, 1);
            $em->flush();
            $scanFile = $qtiRepo->scanFilesToImport($step);
        }

        if ($scanFile !== true) {
            return $this->importError($scanFile, $exoID);
        }

        if ($exoID == -1) {
            return $this->forward('UJMExoBundle:Question:index', array());
        } else {
            $qtiRepo->assocExerciseQuestion(false);

            return $this->redirect($this->generateUrl('ujm_exercise_open', ['id' => $exoID]).'#/steps');
        }
    }

    /**
     * Create the form to import a QTI file.
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importFormAction()
    {
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            $exoID = $request->request->get('exoID');
        }

        return $this->render('UJMExoBundle:QTI:import.html.twig', array('exoID' => $exoID));
    }

    /**
     * Extract the QTI files.
     *
     *
     * @param UJM\ExoBundle\Services\classes\QTI $qtiRepo
     *
     * @return bool
     */
    private function extractFiles($qtiRepo)
    {
        $request = $this->container->get('request');
        $file = $request->files->get('qtifile');

        $qtiRepo->createDirQTI();
        $root = array();
        $fichier = array();

        $file->move($qtiRepo->getUserDir(), $file->getClientOriginalName());
        $zip = new \ZipArchive();
        if ($zip->open($qtiRepo->getUserDir().$file->getClientOriginalName()) !== true) {
            return false;
        }
        $res = zip_open($qtiRepo->getUserDir().$file->getClientOriginalName());
        $zip->extractTo($qtiRepo->getUserDir());

        $i = 0;
        while ($zip_entry = zip_read($res)) {
            if (zip_entry_filesize($zip_entry) > 0) {
                $nom_fichier = zip_entry_name($zip_entry);
                if (substr($nom_fichier, -4, 4) == '.xml') {
                    $root[$i] = $fichier = explode('/', $nom_fichier);
                }
            }
            ++$i;
        }

        $zip->close();

        return true;
    }

    /**
     * Return a response with warning.
     *
     *
     * @return Response
     */
    private function importError($mssg, $exoID)
    {
        if ($exoID == -1) {
            return $this->forward('UJMExoBundle:Question:index',
                array('qtiError' => $this->get('translator')->trans($mssg, array(), 'ujm_exo'))
            );
        } else {
            return $this->forward('UJMExoBundle:Exercise:showQuestions',
                array(
                    'id' => $exoID,
                    'qtiError' => $this->get('translator')->trans($mssg, array(), 'ujm_exo'),
                    'pageNow' => 0,
                    'categoryToFind' => 'z',
                    'titleToFind' => 'z',
                    'displayAll' => 0, )
            );
        }
    }

    /**
     * For export all questions of an exercise.
     *
     * @return $response
     */
    public function exportQuestionsExerciseAction()
    {
        $request = $this->container->get('request');
        $exoID = $request->get('exoID');
        $search = array(' ', '/');
        $title = str_replace($search, '_', $request->get('exoName'));

        $qtiRepo = $this->container->get('ujm.exo_qti_repository');
        $qtiRepo->createDirQTI($title, true);

        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
        $tmpFileName = $qtiRepo->getUserDir().'zip/'.$title.'_qestion_qti.zip';

        $zip = new \ZipArchive();
        $zip->open($tmpFileName, \ZipArchive::CREATE);

        $this->getStepsToExport($exercise, $title, $zip);

        $zip->close();

        $qtiSer = $this->container->get('ujm.exo_qti');
        $response = $qtiSer->createZip($tmpFileName, $title);

        return $response;
    }

    /**
     * Export an existing Question in QTI.
     *
     *
     * @param int $id : id of question
     */
    public function ExportAction($id)
    {
        $service = $this->container->get('ujm.exo_question');
        $question = $service->controlUserQuestion($id);
        $sharedQuestions = $service->controlUserSharedQuestion($id);

        $qtiRepo = $this->container->get('ujm.exo_qti_repository');
        $qtiRepo->createDirQTI();

        if (count($question) > 0 || count($sharedQuestions) > 0) {
            if (count($sharedQuestions) > 0) {
                $question = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Question')->find($id);
            }
            $export = $qtiRepo->export($question);

            return $export;
        } else {
            return $this->redirect($this->generateUrl('ujm_question_index'));
        }
    }

    private function getStepsToExport(Exercise $exercise, $title, $zip)
    {
        $em = $this->getDoctrine()->getManager();
        $questionRepo = $em->getRepository('UJMExoBundle:Question');
        $qtiSer = $this->container->get('ujm.exo_qti');
        $qtiRepo = $this->container->get('ujm.exo_qti_repository');

        foreach ($exercise->getSteps() as $step) {
            $questions = $questionRepo->findByStep($step);

            $qtiSer->createQuestionsDirectory($questions, $step->getOrder());
            $dirs = $qtiSer->sortPathOfQuestions($qtiRepo, $step->getOrder());
            $i = 'a';
            foreach ($dirs as $dir) {
                $iterator = new \DirectoryIterator($dir);
                /** @var \DirectoryIterator $element */
                foreach ($iterator as $element) {
                    if (!$element->isDot() && $element->isFile()) {
                        $partDirectory = $title.'/'.$step->getOrder().'/'.$step->getOrder().'_question_'.$i.'/'.$element->getFilename();
                        $zip->addFile($element->getPathname(), $partDirectory);
                    }
                }
                $i .= 'a';
            }
        }
    }
}
