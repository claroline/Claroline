<?php

/**
 * To create temporary repository for QTI files
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use Symfony\Component\Security\Core\SecurityContextInterface;

class qtiRepository {

    private $user;
    private $userRootDir;
    private $userDir;
    private $securityContext;
    private $container;
    private $exercise = null;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext Dependency Injection
     * @param \Symfony\Component\DependencyInjection\Container $container
     *
     */
    public function __construct(SecurityContextInterface $securityContext, $container)
    {
        $this->securityContext = $securityContext;
        $this->container = $container;
        $this->user = $this->securityContext->getToken()->getUser();
    }

    /**
     * get user
     *
     * @access public
     *
     */
    public function getQtiUser()
    {

        return $this->user;
    }

    /**
     * Create the repository
     *
     * @access public
     * @param String $directory directory
     */
    public function createDirQTI($directory = 'default')
    {
        $this->userRootDir = './uploads/ujmexo/qti/'.$this->user->getUsername().'/';
        $this->userDir = $this->userRootDir.$directory.'/';

        if (!is_dir('./uploads/ujmexo/')) {
            mkdir('./uploads/ujmexo/');
        }
        if (!is_dir('./uploads/ujmexo/qti/')) {
            mkdir('./uploads/ujmexo/qti/');
        }
        if (!is_dir($this->userRootDir)) {
            mkdir($this->userRootDir);
        } else {
            $this->removeDirectory();
        }
        if (!is_dir($this->userRootDir.$directory)) {
            mkdir($this->userRootDir.$directory);
        }
    }

    /**
     * Delete the repository
     *
     * @access public
     *
     */
    public function removeDirectory()
    {
        if(!is_dir($this->userRootDir)){
            throw new $this->createNotFoundException($this->userRootDir.' is not directory '.__LINE__.', file '.__FILE__);
        } else {
            exec ('rm -rf '.$this->userRootDir.'*');
        }
    }

    /**
     * get userDir
     *
     * @access public
     *
     */
    public function getUserDir()
    {

        return $this->userDir;
    }

    /**
     * Scan the QTI files
     *
     * @access public
     *
     * @return true or code error
     */
    public function scanFiles()
    {
        $xmlFileFound = false;
        if ($dh = opendir($this->getUserDir())) {
            while (($file = readdir($dh)) !== false) {
                if (substr($file, -4, 4) == '.xml') {
                    $xmlFileFound = true;
                    $document_xml = new \DomDocument();
                    $document_xml->load($this->getUserDir().'/'.$file);
                    foreach ($document_xml->getElementsByTagName('assessmentItem') as $ai) {
                        $imported = false;
                        $ib = $ai->getElementsByTagName('itemBody')->item(0);
                        foreach ($ib->childNodes as $node){
                            if ($imported === false) {
                                switch ($node->nodeName) {
                                    case "choiceInteraction": //qcm
                                        $qtiImport = $this->container->get('ujm.qti_qcm_import');
                                        $interX = $qtiImport->import($this, $ai);
                                        $imported = true;
                                        break;
                                    case 'selectPointInteraction': //graphic with the tag selectPointInteraction
                                        $qtiImport = $this->container->get('ujm.qti_graphic_import');
                                        $interX = $qtiImport->import($this, $ai);
                                        $imported = true;
                                        break;
                                    case 'hotspotInteraction': //graphic with the tag hotspotInteraction
                                        $qtiImport = $this->container->get('ujm.qti_graphic_import');
                                        $interX= $qtiImport->import($this, $ai);
                                        $imported = true;
                                        break;
                                    case 'extendedTextInteraction': //open
                                        $qtiImport = $this->container->get('ujm.qti_open_import');
                                        $interX = $qtiImport->import($this, $ai);
                                        $imported = true;
                                        break;
                                    case 'matchInteraction': //matching
                                        $qtiImport = $this->container->get('ujm.qti_matching_import');
                                        $qtiImport->import($this, $ai);
                                        $imported = true;
                                        break;
                                }
                            }
                        }
                        if ($imported === false) {
                            if (($ib->getElementsByTagName('textEntryInteraction')->length > 0)
                                    || ($ib->getElementsByTagName('inlineChoiceInteraction')->length > 0)) { //question with hole
                                $qtiImport = $this->container->get('ujm.qti_hole_import');
                                $interX = $qtiImport->import($this, $ai);
                                $imported = true;
                            } else {
                                return 'qti unsupported format';
                            }
                        }
                    }
                }
            }
            if ($xmlFileFound === false) {

                return 'qti xml not found';
            }
            closedir($dh);
        }
        $this->removeDirectory();

        if ($this->exercise != null) {
            $this->addQuestionInExercise($interX);
        }

        return true;
    }

    /**
     * call method to export a question
     *
     * @access public
     * @param  UJM\ExoBundle\Entity\Interaction $interaction
     *
     */
    public function export($interaction)
    {
        $typeInter = $interaction->getType();
        switch ($typeInter) {
            case "InteractionQCM":
                $qtiExport = $this->container->get('ujm.qti_qcm_export');

                return $qtiExport->export($interaction, $this);

            case "InteractionGraphic":
                $qtiExport = $this->container->get('ujm.qti_graphic_export');

                return $qtiExport->export($interaction, $this);

            case "InteractionHole":
                $qtiExport = $this->container->get('ujm.qti_hole_export');

                return $qtiExport->export($interaction, $this);

            case "InteractionOpen":
                $qtiExport = $this->container->get('ujm.qti_open_export');

                return $qtiExport->export($interaction, $this);

            case "InteractionMatching":
                $qtiExport = $this->container->get('ujm.qti_matching_export');

                return $qtiExport->export($interaction, $this);

        }
    }


    /**
     * Call scanFiles method for ExoImporter
     *
     * @access public
     *
     * @param UJM\ExoBundle\Entity\Exercise $exercise
     */
    public function scanFilesToImport($exercise)
    {
        $this->exercise = $exercise;
        $this->scanFiles();
        $this->exercise = null;
    }

    /**
     *
     * @access private
     *
     * @param UJM\ExoBundle\Entity\InteractionQCM or InteractionGraphic or .... $interX
     */
    private function addQuestionInExercise($interX)
    {
        $exoServ = $this->container->get('ujm.exercise_services');
        $exoServ->setExerciseQuestion($this->exercise->getId(), $interX);
    }

}
