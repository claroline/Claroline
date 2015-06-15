<?php

/**
 * To create temporary repository for QTI files
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use Claroline\CoreBundle\Library\Utilities\FileSystem;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class QtiRepository {

    private $user;
    private $userRootDir;
    private $userDir;
    private $tokenStorageInterface;
    private $container;
    private $exercise = null;
    private $exerciseQuestions = array();
    private $importedQuestions = array();

    /**
     * Constructor
     *
     * @access public
     *
     * @param Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorageInterface Dependency Injection
     * @param \Symfony\Component\DependencyInjection\Container $container
     *
     */
    public function __construct(TokenStorageInterface $tokenStorageInterface, $container)
    {
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->container = $container;
        $this->user = $this->tokenStorageInterface->getToken()->getUser();
    }

    /**
     *
     * @access public
     */
     public function razValues ()
     {
         $this->exercise = null;
         $this->exerciseQuestions = array();
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
     * @param boolean $clear to clear or no the directory userRootDir
     */
    public function createDirQTI($directory = 'default', $clear = TRUE)
    {
        $fs = new FileSystem();
        $this->userRootDir = $this->container->getParameter('ujm.param.exo_directory') . '/qti/'.$this->user->getUsername().'/';
        $this->userDir = $this->userRootDir.$directory.'/';
        if ($clear === TRUE) {
            $this->removeDirectory();
        }
        if (!is_dir($this->container->getParameter('ujm.param.exo_directory') . '/qti/')) {
            $fs->mkdir($this->container->getParameter('ujm.param.exo_directory') . '/qti/');
        }
        if (!is_dir($this->userRootDir.$directory.'/zip')) {
            $fs->mkdir($this->userRootDir.$directory.'/zip');
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
         if(is_dir($this->userRootDir)) {
            exec ('rm -rf '.$this->userRootDir.'*');
            $fs = new FileSystem();
            $iterator = new \DirectoryIterator($this->userRootDir);

            foreach ($iterator as $el) {
                if ($el->isDir()) $fs->rmDir($el->getRealPath(), true);
                if ($el->isFile()) $fs->rm($el->getRealPath());
            }
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
                 if (substr($file, -4, 4) == '.xml'
                        && $this->alreadyImported($file) === false) {
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
                                    case 'extendedTextInteraction': /*open (long or short)*/
                                        $qtiImport = $this->longOrShort($ai);
                                        $interX = $qtiImport->import($this, $ai);
                                        $imported = true;
                                        break;
                                    case 'matchInteraction': //matching
                                        $qtiImport = $this->container->get('ujm.qti_matching_import');
                                        $interX =  $qtiImport->import($this, $ai);
                                        $imported = true;
                                        break;
                                }
                            }
                        }
                        if ($imported === false) {
                            $other = $this->importOther($ai);
                            $interX = $other[0];
                            $imported = $other[1];
                            if ($imported == false) {

                                return 'qti unsupported format';
                            }
                        }
                        if ($this->exercise != null) {
                            $this->exerciseQuestions[] = $file;
                            $this->importedQuestions[$file] = $interX;
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

        return true;
    }

    /**
    *
    * @access private
    * @param String name of the xml file
    *
    * @return boolean
    */
    private function alreadyImported($fileName)
    {
        $alreadyImported = false;
        if (isset($this->importedQuestions[$fileName])) {
            $this->exerciseQuestions[] = $fileName;
            $alreadyImported = true;
        }

        return $alreadyImported;
    }

    /**
     * to try import other type of question
     *
     * @access private
     * @param  DOMElement $ai
     *
     *  @return array
     */
    private function importOther($ai)
    {
        $imported = false;
        $interX   = NULL;
        $response = array();
        $ib = $ai->getElementsByTagName('itemBody')->item(0);
        $nbNodes = 0;
        $promptTag = false;
        $textEntryInteractionTag = false;
        foreach ($ib->childNodes as $node) {
            if(!($node instanceof \DomText)) {
                $nbNodes++;
            }
            if ($node->nodeName == 'prompt') {
                $promptTag = true;
            }
            if ($node->nodeName == 'textEntryInteraction') {
                $textEntryInteractionTag = true;
            }
        }
        if ($nbNodes == 2 && $promptTag === true && $textEntryInteractionTag === true) {
            $qtiImport = $this->container->get('ujm.qti_open_one_word_import');
            $interX = $qtiImport->import($this, $ai);
            $imported = true;
        } else if (($ib->getElementsByTagName('textEntryInteraction')->length > 0)
                    || ($ib->getElementsByTagName('inlineChoiceInteraction')->length > 0)) { //question with hole
                        $qtiImport = $this->container->get('ujm.qti_hole_import');
                        $interX = $qtiImport->import($this, $ai);
                        $imported = true;
        }

        $response[] = $interX;
        $response[] = $imported;

        return $response;
    }

    /**
     * to determine if an open question is with long answer or short answer
     *
     * @access private
     * @param  DOMElement $ai
     *
     * @return Service Container
     */
    private function longOrShort ($ai)
    {
        if ($ai->getElementsByTagName('mapping')->item(0)) {
            $qtiImport = $this->container->get('ujm.qti_open_short_import');
        } else {
            $qtiImport = $this->container->get('ujm.qti_open_long_import');
        }

        return $qtiImport;
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
        if ($interaction->getType() != 'InteractionOpen') {
            $service = 'ujm.qti_export_' . $interaction->getType();
            $qtiExport = $this->container->get($service);
        } else {
            $qtiExport = $this->serviceOpenQuestion($interaction->getId());
        }

        return $qtiExport->export($interaction, $this);

    }

    /**
     * To select the service (long, oneWord, ...) for an open question
     *
     * @access private
     *
     * @param  Integer $interId id of the interaction
     *
     * @return instance of service ujm.qti_open
     *
     */
    private function serviceOpenQuestion($interId)
    {
        $em = $this->container->get('doctrine')->getManager();
        $interOpen = $em->getRepository('UJMExoBundle:InteractionOpen')
                        ->getInteractionOpen($interId);
        $type = ucfirst($interOpen->getTypeOpenQuestion());
        $serv = $this->container->get('ujm.qti_open_'.$type.'_export');

        return $serv;
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
        $scanFile = $this->scanFiles();
        if ($scanFile === true ) {
            return true;
        } else {
            return $scanFile;
        }
    }

    /**
     *
     * @access private
     *
     * @param UJM\ExoBundle\Entity\InteractionQCM or InteractionGraphic or .... $interX
     */
    private function addQuestionInExercise($interX, $order = -1)
    {
        $exoServ = $this->container->get('ujm.exercise_services');
        $exoServ->setExerciseQuestion($this->exercise, $interX, $order);
    }

    /**
     *
     * @access public
     *
     * Associate an imported question with an exercise
     */
    public function assocExerciseQuestion($ws = false)
    {
        $order = 1;
        foreach($this->exerciseQuestions as $xmlName) {
            if ($ws === false) {
                $order = -1;
            }
            $this->addQuestionInExercise($this->importedQuestions[$xmlName], $order);
            $order ++;
        }
     }
}
