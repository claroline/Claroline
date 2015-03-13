<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;

class QtiController extends Controller {

    /**
     *Import question in QTI
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */
    public function importAction()
    {
        $request = $this->container->get('request');
        $exoID = $request->get('exerciceID');

        if (strstr($_FILES["qtifile"]["type"], 'application/zip') === false) {

            return $this->importError('qti format warning');
        }

        $qtiRepos = $this->container->get('ujm.qti_repository');
        if ($this->extractFiles($qtiRepos) === false) {

            return $this->importError('qti can\'t open zip');
        }

        if($exoID == -1) {
            $scanFile = $qtiRepos->scanFiles();

        } else {
            $scanFile = $qtiRepos->scanFilesToImport($exoID);

        }

        if ($scanFile !== true) {

                return $this->importError($scanFile);
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

        $rst = 'its a zip file';
        move_uploaded_file($_FILES["qtifile"]["tmp_name"],
                $qtiRepos->getUserDir() . $_FILES["qtifile"]["name"]);
        $zip = new \ZipArchive;
        if ($zip->open($qtiRepos->getUserDir() . $_FILES["qtifile"]["name"]) !== true) {

            return false;
        }
        $res = zip_open($qtiRepos->getUserDir() . $_FILES["qtifile"]["name"]);
        $zip->extractTo($qtiRepos->getUserDir());

        while ($zip_entry = zip_read($res)) {
            if(zip_entry_filesize($zip_entry) > 0) {
                $nom_fichier = zip_entry_name($zip_entry);
                if (substr($nom_fichier, -4, 4) == '.xml') {
                    $root = explode('/', $nom_fichier);
                }
            }
        }

        $zip->close();

        if (count($root) > 1) {
            unset($root[count($root) - 1]);
            $comma_separated = implode('/', $root);
            exec('mv '.$qtiRepos->getUserDir().$comma_separated.'/* '.$qtiRepos->getUserDir());
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
    private function importError($mssg)
    {
        return $this->forward('UJMExoBundle:Question:index',
                    array('qtiError' =>
                        $this->get('translator')->trans($mssg))
                    );
    }

}
