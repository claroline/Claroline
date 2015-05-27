<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;

class QtiController extends Controller {

    /**
     * Import question in QTI
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */
    public function importQuestionAction()
    {
        $request = $this->container->get('request');
        $exoID = $request->get('exerciceID');

        if (strstr($_FILES["qtifile"]["type"], 'application/zip') === false) {

            return $this->importError('qti format warning', $exoID);
        }

        $qtiRepos = $this->container->get('ujm.qti_repository');
        if ($this->extractFiles($qtiRepos) === false) {

            return $this->importError('qti can\'t open zip', $exoID);
        }

        if($exoID == -1) {
            $scanFile = $qtiRepos->scanFiles();

        } else {
            $em = $this->getDoctrine()->getManager();
            $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
            $scanFile = $qtiRepos->scanFilesToImport($exercise);

        }

        if ($scanFile !== true) {

                return $this->importError($scanFile, $exoID);
            }

        if ($exoID == -1) {
            return $this->forward('UJMExoBundle:Question:index', array());
        } else {
            return $this->redirect($this->generateUrl( 'ujm_exercise_questions', array( 'id' => $exoID, )));
        }
    }

    /**
     * Create the form to import a QTI file
     *
     * @access public
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
     * Extract the QTI files
     *
     * @access private
     *
     * @param UJM\ExoBundle\Services\classes\QTI $qtiRepos
     *
     * @return boolean
     */
    private function extractFiles($qtiRepos)
    {
        $qtiRepos->createDirQTI();
        $root = array();
        $fichier = array();

        $rst = 'its a zip file';
        move_uploaded_file($_FILES["qtifile"]["tmp_name"],
                $qtiRepos->getUserDir() . $_FILES["qtifile"]["name"]);
        $zip = new \ZipArchive;
        if ($zip->open($qtiRepos->getUserDir() . $_FILES["qtifile"]["name"]) !== true) {

            return false;
        }
        $res = zip_open($qtiRepos->getUserDir() . $_FILES["qtifile"]["name"]);
        $zip->extractTo($qtiRepos->getUserDir());

        $i=0;
        while ($zip_entry = zip_read($res)) {
            if(zip_entry_filesize($zip_entry) > 0) {
                $nom_fichier = zip_entry_name($zip_entry);
                if (substr($nom_fichier, -4, 4) == '.xml') {
                    $root[$i] = $fichier = explode('/', $nom_fichier);
                }
            }
            $i++;
        }

        $zip->close();

        foreach ($root as  $infoFichier){
            if (count($infoFichier) > 1) {
                unset($infoFichier[count($infoFichier) - 1]);
                $comma_separated = implode('/', $infoFichier);
                exec('mv '.$qtiRepos->getUserDir().$comma_separated.'/* '.$qtiRepos->getUserDir());
            }
        }

        return true;
    }

    /**
     * Return a response with warning
     *
     * @access private
     *
     * @return Response
     *
     */
    private function importError($mssg, $exoID)
    {
        if($exoID == -1) {
            return $this->forward('UJMExoBundle:Question:index',
                    array('qtiError' =>
                        $this->get('translator')->trans($mssg))
                    );
        } else {
            return $this->forward('UJMExoBundle:Exercise:showQuestions',
                    array(
                        'id' => $exoID,
                        'qtiError' => $this->get('translator')->trans($mssg),
                        'pageNow'=> 0,
                        'categoryToFind'=> 'z',
                        'titleToFind'=> 'z',
                        'displayAll'=> 0 )
                    );
        }

    }

    /**
    * For export all questions of an exercise
    *
    * @return $response
    */
    public function exportQuestionsExerciseAction() {
       $request = $this->container->get('request');
       $exoID = $request->get('exoID');
       $search = array(' ', '/');
       $title = str_replace($search, '_', $request->get('exoName'));
       $qtiServ = $this->container->get('ujm.qti_services');

       $qtiRepos = $this->container->get('ujm.qti_repository');
       $qtiRepos->createDirQTI($title, TRUE);

       $em = $this->getDoctrine()->getManager();
       $interRepos = $em->getRepository('UJMExoBundle:Interaction');
       $interactions = $interRepos->getExerciseInteraction(
               $this->container->get('doctrine')->getManager(),
               $exoID, FALSE);

       $this->createQuestionsDirectory($qtiRepos, $interactions);
       $qdirs = $qtiServ->sortPathOfQuestions($qtiRepos);

       if ($qdirs == null) {
           $mssg = 'qti no questions';
            return $this->forward('UJMExoBundle:Exercise:showQuestions',
                   array(
                       'id' => $exoID,
                       'qtiError' => $this->get('translator')->trans($mssg),
                       'pageNow'=> 0,
                       'categoryToFind'=> 'z',
                       'titleToFind'=> 'z',
                       'displayAll'=> 0 )
                   );
       }

       $tmpFileName = $qtiRepos->getUserDir().'zip/'.$title.'_qestion_qti.zip';

       $zip = new \ZipArchive();
       $zip->open($tmpFileName, \ZipArchive::CREATE);

       $userName = $this->container->get('security.token_storage')->getToken()->getUser()->getUserName();

       foreach ($qdirs as $dir) {
           $iterator = new \DirectoryIterator($dir);
               foreach ($iterator as $element) {
                   if (!$element->isDot() && $element->isFile() && $element->getExtension() != "xml") {
                       $path = $element->getPath();
                       $partDirectory = str_replace($this->container->getParameter('ujm.param.exo_directory') . '/qti/'.$userName.'/'.$title.'/questions/questionDoc_','', $path);

                       $zip->addFile($element->getPathname(), $title.'/question_'.$partDirectory.'/'.$element->getFilename());
                   }
                   if (!$element->isDot() && $element->isFile() && $element->getExtension() == "xml") {
                       $path = $element->getPath();
                       $partDirectory = str_replace($this->container->getParameter('ujm.param.exo_directory') . '/qti/'.$userName.'/'.$title.'/questions/question_','', $path);
                       $zip->addFile($element->getPathname(), $title.'/question_'.$partDirectory.'/question_'.$partDirectory.'.'.$element->getExtension());
                   }
               }
       }
       $zip->close();

       $qtiSer = $this->container->get('ujm.qti_services');
       $response = $qtiSer->createZip($tmpFileName,$title);

       return $response;
    }

    /**
     * Export an existing Question in QTI.
     *
     * @access public
     *
     * @param integer $id : id of question
     *
     */
    public function ExportAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $question = $this->container->get('ujm.exercise_services')->controlUserQuestion($id, $this->container, $em);

        $qtiRepos = $this->container->get('ujm.qti_repository');
        $qtiRepos->createDirQTI();

        if (count($question) > 0) {
            $interaction = $em->getRepository('UJMExoBundle:Interaction')
                              ->getInteraction($id);
            $export = $qtiRepos->export($interaction);
        }

        return $export;
    }

    /**
    * create the directory questions to export an exercise and export the qti files
    *
    * @param type $qtiRepos
    *
    * @param type $interactions
    */
    private function createQuestionsDirectory($qtiRepos, $interactions) {
        mkdir($qtiRepos->getUserDir().'questions');
        $i = 'a';
        foreach ($interactions as $interaction) {
            $qtiRepos->export($interaction);
            mkdir($qtiRepos->getUserDir().'questions/'.'question_'.$i);
            $iterator = new \DirectoryIterator($qtiRepos->getUserDir());
            foreach ($iterator as $element) {
                // for xml file (question files)
                if (!$element->isDot() && $element->isFile() && $element->getExtension() == "xml") {
                    rename($qtiRepos->getUserDir().$element->getFilename(), $qtiRepos->getUserDir().'questions/question_'.$i.'/'.$element->getFilename());
                }
                // for bind documents
                if (!$element->isDot() && $element->isFile() && $element->getExtension() != "xml") {
                    mkdir($qtiRepos->getUserDir().'questions/'.'questionDoc_'.$i);
                    rename($qtiRepos->getUserDir().$element->getFilename(), $qtiRepos->getUserDir().'questions/questionDoc_'.$i.'/'.$element->getFilename());
                }
            }
            $i .='a';
        }
    }
}