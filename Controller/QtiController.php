<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class QtiController extends Controller {

    /**
     *Import question in QTI
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importAction()
    {
        if (strstr($_FILES["qtifile"]["type"], 'application/zip') === false) {

            return $this->importError('qti format warning');
        }
        $xmlFileFound = false;
        $qtiRepos = $this->container->get('ujm.qti_repository');
        $qtiRepos->createDirQTI();

        $rst = 'its a zip file';
        move_uploaded_file($_FILES["qtifile"]["tmp_name"],
                $qtiRepos->getUserDir() . $_FILES["qtifile"]["name"]);
        $zip = new \ZipArchive;
        $zip->open($qtiRepos->getUserDir() . $_FILES["qtifile"]["name"]);
        $res= zip_open($qtiRepos->getUserDir() . $_FILES["qtifile"]["name"]);

        $zip->extractTo($qtiRepos->getUserDir());
        $tab_liste_fichiers = array();
        while ($zip_entry = zip_read($res)) {
            if(zip_entry_filesize($zip_entry) > 0) {
                $nom_fichier = zip_entry_name($zip_entry);
                $rst =$rst . '-_-_-_'.$nom_fichier;
                array_push($tab_liste_fichiers, $nom_fichier);

            }
        }
        $zip->close();

        if ($dh = opendir($qtiRepos->getUserDir())) {
            while (($file = readdir($dh)) !== false) {
                if (substr($file, -4, 4) == '.xml') {
                    $xmlFileFound = true;
                    $imported = false;
                    $document_xml = new \DomDocument();
                    $document_xml->load($qtiRepos->getUserDir().'/'.$file);
                    $ai = $document_xml->getElementsByTagName('assessmentItem')->item(0);
                    if ($ai != null) {
                        $ib = $ai->getElementsByTagName('itemBody')->item(0);
                        foreach ($ib->childNodes as $node){
                            if ($imported === false) {
                                switch ($node->nodeName) {
                                    case "choiceInteraction":
                                        $qtiImport = $this->container->get('ujm.qti_qcm_import');
                                        $qtiImport->import($qtiRepos, $document_xml);
                                        $imported = true;
                                        break;
                                }
                            }
                        }
                        if ($imported === false) {
                            if (($ib->getElementsByTagName('textEntryInteraction')->length > 0)
                                    || ($ib->getElementsByTagName('inlineChoiceInteraction')->length > 0)) {
                                $qtiImport = $this->container->get('ujm.qti_hole_import');
                                $qtiImport->import($qtiRepos, $document_xml);
                                $imported = true;
                            } else {
                                return $this->importError('qti unsupported format');
                            }
                        }
                    }
                }
            }
            if ($xmlFileFound === false) {
                return $this->importError('qti xml not found');
            }
            closedir($dh);
        }

        $qtiRepos->removeDirectory();

        return $this->forward('UJMExoBundle:Question:index', array());
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
        return $this->render('UJMExoBundle:QTI:import.html.twig');
    }


    public function importError($mssg)
    {
        return $this->forward('UJMExoBundle:Question:index',
                    array('qtiError' =>
                        $this->get('translator')->trans($mssg))
                    );
    }

}
