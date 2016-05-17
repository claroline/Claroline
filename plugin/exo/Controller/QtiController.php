<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Library\Utilities\FileSystem;

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

        $qtiRepos = $this->container->get('ujm.exo_qti_repository');
        $qtiRepos->razValues();
        if ($this->extractFiles($qtiRepos) === false) {
            return $this->importError('qti can\'t open zip', $exoID);
        }

        if ($exoID == -1) {
            $scanFile = $qtiRepos->scanFiles();
        } else {
            $em = $this->getDoctrine()->getManager();
            $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
            $step = $this->container->get('ujm.exo_exercise')->createStep($exercise, 1, $em);
            $em->flush();
            $scanFile = $qtiRepos->scanFilesToImport($step);
        }

        if ($scanFile !== true) {
            return $this->importError($scanFile, $exoID);
        }

        if ($exoID == -1) {
            return $this->forward('UJMExoBundle:Question:index', array());
        } else {
            $qtiRepos->assocExerciseQuestion(false);

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
     * @param UJM\ExoBundle\Services\classes\QTI $qtiRepos
     *
     * @return bool
     */
    private function extractFiles($qtiRepos)
    {
        $request = $this->container->get('request');
        $file = $request->files->get('qtifile');

        $qtiRepos->createDirQTI();
        $root = array();
        $fichier = array();

        $file->move($qtiRepos->getUserDir(), $file->getClientOriginalName());
        $zip = new \ZipArchive();
        if ($zip->open($qtiRepos->getUserDir().$file->getClientOriginalName()) !== true) {
            return false;
        }
        $res = zip_open($qtiRepos->getUserDir().$file->getClientOriginalName());
        $zip->extractTo($qtiRepos->getUserDir());

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
        $fs = new FileSystem();

        //if the xml is in subdirectory and not in the root
        foreach ($root as  $infoFichier) {
            if (count($infoFichier) > 1) {
                unset($infoFichier[count($infoFichier) - 1]);
                $comma_separated = implode('/', $infoFichier);
                //please use $fs->move() instead
                //@see http://symfony.com/doc/current/components/filesystem/introduction.html
                exec('mv '.$qtiRepos->getUserDir().$comma_separated.'/* '.$qtiRepos->getUserDir());
            }
        }

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
        $qtiServ = $this->container->get('ujm.exo_qti');

        $qtiRepos = $this->container->get('ujm.exo_qti_repository');
        $qtiRepos->createDirQTI($title, true);

        $em = $this->getDoctrine()->getManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
        $questionRepo = $em->getRepository('UJMExoBundle:Question');
        $questions = $questionRepo->findByExercise($exercise);

        $this->createQuestionsDirectory($qtiRepos, $questions);
        $qdirs = $qtiServ->sortPathOfQuestions($qtiRepos);

        if ($qdirs == null) {
            $mssg = 'qti_no_questions';

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

        $tmpFileName = $qtiRepos->getUserDir().'zip/'.$title.'_qestion_qti.zip';

        $zip = new \ZipArchive();
        $zip->open($tmpFileName, \ZipArchive::CREATE);

        $userName = $this->container->get('security.token_storage')->getToken()->getUser()->getUserName();

        foreach ($qdirs as $dir) {
            $iterator = new \DirectoryIterator($dir);
            foreach ($iterator as $element) {
                if (!$element->isDot() && $element->isFile() && $element->getExtension() != 'xml') {
                    $path = $element->getPath();
                    $partDirectory = str_replace($this->container->getParameter('ujm.param.exo_directory').'/qti/'.$userName.'/'.$title.'/questions/questionDoc_', '', $path);

                    $zip->addFile($element->getPathname(), $title.'/question_'.$partDirectory.'/'.$element->getFilename());
                }
                if (!$element->isDot() && $element->isFile() && $element->getExtension() == 'xml') {
                    $path = $element->getPath();
                    $partDirectory = str_replace($this->container->getParameter('ujm.param.exo_directory').'/qti/'.$userName.'/'.$title.'/questions/question_', '', $path);
                    $zip->addFile($element->getPathname(), $title.'/question_'.$partDirectory.'/question_'.$partDirectory.'.'.$element->getExtension());
                }
            }
        }
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

        $qtiRepos = $this->container->get('ujm.exo_qti_repository');
        $qtiRepos->createDirQTI();

        if (count($question) > 0 || count($sharedQuestions) > 0) {
            if (count($sharedQuestions) > 0) {
                $question = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Question')->find($id);
            }
            $export = $qtiRepos->export($question);

            return $export;
        } else {
            return $this->redirect($this->generateUrl('ujm_question_index'));
        }
    }

    /**
     * create the directory questions to export an exercise and export the qti files.
     *
     * @param type $qtiRepos
     * @param type $questions
     */
    private function createQuestionsDirectory($qtiRepos, $questions)
    {
        mkdir($qtiRepos->getUserDir().'questions');
        $i = 'a';
        foreach ($questions as $question) {
            $qtiRepos->export($question);
            mkdir($qtiRepos->getUserDir().'questions/'.'question_'.$i);
            $iterator = new \DirectoryIterator($qtiRepos->getUserDir());
            foreach ($iterator as $element) {
                // for xml file (question files)
                if (!$element->isDot() && $element->isFile() && $element->getExtension() == 'xml') {
                    rename($qtiRepos->getUserDir().$element->getFilename(), $qtiRepos->getUserDir().'questions/question_'.$i.'/'.$element->getFilename());
                }
                // for bind documents
                if (!$element->isDot() && $element->isFile() && $element->getExtension() != 'xml') {
                    mkdir($qtiRepos->getUserDir().'questions/'.'questionDoc_'.$i);
                    rename($qtiRepos->getUserDir().$element->getFilename(), $qtiRepos->getUserDir().'questions/questionDoc_'.$i.'/'.$element->getFilename());
                }
            }
            $i .= 'a';
        }
    }
}
