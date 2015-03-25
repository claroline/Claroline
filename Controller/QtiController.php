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
            $scanFile = $qtiRepos->scanFilesToImport($exoID);

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
            $typeImport = $request->request->get('typeImport');
        }

        return $this->render('UJMExoBundle:QTI:import.html.twig', array('exoID' => $exoID, 'typeImport' => $typeImport));
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
     * Import questions of exercise in QTI
     *
     * @access public
     *
     * @return type
     */
    public function importQuestionsExerciseAction() {
        $request = $this->container->get('request');
        $exoID = $request->get('exerciceID');

        if (strstr($_FILES["qtifile"]["type"], 'application/zip') === false) {

            return $this->importError('qti format warning', $exoID);
        }

        $qtiRepos = $this->container->get('ujm.qti_repository');
        if ($this->extractFiles($qtiRepos) === false) {

            return $this->importError('qti can\'t open zip', $exoID);
        }

        $scanFile = $qtiRepos->scanFilesToImport($exoID);

        
        if ($scanFile !== true) {
            return $this->importError($scanFile, $exoID);
        }

        return $this->redirect($this->generateUrl( 'ujm_exercise_questions', array( 'id' => $exoID, )));
    }

}
