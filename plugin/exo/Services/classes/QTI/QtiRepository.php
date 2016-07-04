<?php

namespace UJM\ExoBundle\Services\classes\QTI;

use Claroline\CoreBundle\Library\Utilities\FileSystem;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use UJM\ExoBundle\Entity\InteractionOpen;
use UJM\ExoBundle\Entity\Step;

/**
 * To create temporary repository for QTI files.
 */
class QtiRepository
{
    private $userRootDir;
    private $userDir;
    private $tokenStorage;
    private $container;
    private $step = null;
    private $exerciseQuestions = [];
    private $importedQuestions = [];

    /**
     * Constructor.
     *
     * @param TokenStorageInterface                            $tokenStorageInterface
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct(TokenStorageInterface $tokenStorageInterface, $container)
    {
        $this->tokenStorage = $tokenStorageInterface;
        $this->container = $container;
    }

     /**
      *
      */
     public function razValues()
     {
         $this->step = null;
         $this->exerciseQuestions = [];
     }

    /**
     * get user.
     */
    public function getQtiUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * Create the repository.
     *
     * @param string $directory directory
     * @param bool   $clear     to clear or no the directory userRootDir
     */
    public function createDirQTI($directory = 'default', $clear = true)
    {
        $fs = new FileSystem();
        $this->userRootDir = $this->container->getParameter('ujm.param.exo_directory').'/qti/'.$this->getQtiUser()->getUsername().'/';
        $this->userDir = $this->userRootDir.$directory.'/';
        if ($clear === true) {
            $this->removeDirectory();
        }
        if (!is_dir($this->container->getParameter('ujm.param.exo_directory').'/qti/')) {
            $fs->mkdir($this->container->getParameter('ujm.param.exo_directory').'/qti/');
        }
        if (!is_dir($this->userRootDir.$directory.'/zip')) {
            $fs->mkdir($this->userRootDir.$directory.'/zip');
        }
        if (!is_dir($this->userRootDir.$directory.'/ws')) {
            $fs->mkdir($this->userRootDir.$directory.'/ws');
        }
    }

    /**
     * Delete the repository.
     */
    public function removeDirectory()
    {
        $fs = new FileSystem();
        $fs->rmdir($this->userRootDir, true);
    }

    /**
     * get userDir.
     */
    public function getUserDir()
    {
        return $this->userDir;
    }

    /**
     * Scan the QTI files.
     *
     *
     * @return true or code error
     */
    public function scanFiles()
    {
        $xmlFileFound = false;
        $info = '';
        $iterator = new RecursiveDirectoryIterator($this->getUserDir(), \FilesystemIterator::SKIP_DOTS);
        foreach (new \RecursiveIteratorIterator($iterator) as $file) {
            if ($file->getExtension() == 'xml' && $this->alreadyImported(base64_encode($file)) === false) {
                $xmlFileFound = true;
                $document_xml = new \DomDocument();
                $document_xml->load($file);
                foreach ($document_xml->getElementsByTagName('assessmentItem') as $ai) {
                    $path = dirname($file);
                    $imported = false;
                    $ib = $ai->getElementsByTagName('itemBody')->item(0);
                    foreach ($ib->childNodes as $node) {
                        if ($imported === false) {
                            switch ($node->nodeName) {
                                case 'choiceInteraction': //qcm
                                    $qtiImport = $this->container->get('ujm.exo_qti_import_InteractionQCM');
                                    $interX = $qtiImport->import($this, $ai, $path);
                                    break;
                                case 'selectPointInteraction': //graphic with the tag selectPointInteraction
                                    $qtiImport = $this->container->get('ujm.exo_qti_import_InteractionGraphic');
                                    $interX = $qtiImport->import($this, $ai, $path);
                                    break;
                                case 'hotspotInteraction': //graphic with the tag hotspotInteraction
                                    $qtiImport = $this->container->get('ujm.exo_qti_import_InteractionGraphic');
                                    $interX = $qtiImport->import($this, $ai, $path);
                                    break;
                                case 'extendedTextInteraction': /*open (long or short)*/
                                    $qtiImport = $this->longOrShort($ai);
                                    $interX = $qtiImport->import($this, $ai, $path);
                                    break;
                                case 'matchInteraction': //matching
                                    $qtiImport = $this->container->get('ujm.exo_qti_import_matching');
                                    $interX = $qtiImport->import($this, $ai, $path);
                                    break;
                            }
                        }
                    }
                    if (isset($interX) && $interX !== false) {
                        $imported = true;
                    }
                    if ($imported === false) {
                        $other = $this->importOther($ai, $path);
                        $interX = $other[0];
                        $imported = $other[1];
                        if ($imported == false) {
                            $info .= $file.' qti unsupported format'."\n";
                        }
                    }

                    if ($this->step != null) {
                        $this->exerciseQuestions[] = $file;
                        $this->importedQuestions[dirname($file).'/'.$file->getFileName()] = $interX;
                    }
                }
            }
        }
        if ($xmlFileFound === false) {
            $info .= 'qti xml not found'."\n";
        }

        $this->removeDirectory();
        if ($info != '') {
            return $info;
        }

        return true;
    }

    /**
     * @param string name of the xml file
     *
     * @return bool
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
     * to try import other type of question.
     *
     * @param DOMElement $ai
     * @param            $path
     *
     *  @return array
     */
    private function importOther($ai, $path)
    {
        $imported = false;
        $interX = null;
        $response = [];
        $ib = $ai->getElementsByTagName('itemBody')->item(0);
        $nbNodes = 0;
        $promptTag = false;
        $textEntryInteractionTag = false;
        foreach ($ib->childNodes as $node) {
            if (!($node instanceof \DomText)) {
                ++$nbNodes;
            }
            if ($node->nodeName == 'prompt') {
                $promptTag = true;
            }
            if ($node->nodeName == 'textEntryInteraction') {
                $textEntryInteractionTag = true;
            }
        }
        if ($nbNodes == 2 && $promptTag === true && $textEntryInteractionTag === true) {
            $qtiImport = $this->container->get('ujm.exo_qti_import_open_one_word');
            $interX = $qtiImport->import($this, $ai, $path);
            $imported = true;
        } elseif (($ib->getElementsByTagName('textEntryInteraction')->length > 0)
                    || ($ib->getElementsByTagName('inlineChoiceInteraction')->length > 0)) { //question with hole
                        $qtiImport = $this->container->get('ujm.exo_qti_import_InteractionHole');
            $interX = $qtiImport->import($this, $ai, $path);
            $imported = true;
        }

        $response[] = $interX;
        $response[] = $imported;

        return $response;
    }

    /**
     * to determine if an open question is with long answer or short answer.
     *
     * @param DOMElement $ai
     *
     * @return Service Container
     */
    private function longOrShort($ai)
    {
        if ($ai->getElementsByTagName('mapping')->item(0)) {
            $qtiImport = $this->container->get('ujm.exo_qti_import_open_short');
        } else {
            $qtiImport = $this->container->get('ujm.exo_qti_import_open_long');
        }

        return $qtiImport;
    }

    /**
     * call method to export a question.
     *
     * @param \UJM\ExoBundle\Entity\Question $question
     */
    public function export($question)
    {
        if ($question->getType() !== InteractionOpen::TYPE) {
            $service = 'ujm.exo_qti_export_'.$question->getType();
            $qtiExport = $this->container->get($service);
        } else {
            $qtiExport = $this->serviceOpenQuestion($question->getId());
        }

        return $qtiExport->export($question, $this);
    }

    /**
     * To select the service (long, oneWord, ...) for an open question.
     *
     * @param int $questionId
     *
     * @return instance of service ujm.qti_open
     */
    private function serviceOpenQuestion($questionId)
    {
        $em = $this->container->get('doctrine')->getManager();
        $interOpen = $em->getRepository('UJMExoBundle:InteractionOpen')
            ->findOneByQuestion($questionId);
        $type = $interOpen->getTypeOpenQuestion();
        $serv = $this->container->get('ujm.exo_qti_export_open_'.$type);

        return $serv;
    }

    /**
     * Call scanFiles method for ExoImporter.
     *
     * @param Step $step
     *
     * @return mixed
     */
    public function scanFilesToImport(Step $step)
    {
        $this->step = $step;
        $scanFile = $this->scanFiles();
        if ($scanFile === true) {
            return true;
        } else {
            return $scanFile;
        }
    }

    /**
     * @param bool $ws
     */
    public function assocExerciseQuestion($ws = false)
    {
        $order = 1;
        foreach ($this->exerciseQuestions as $xmlName) {
            if ($ws === false) {
                $order = -1;
            }

            if (isset($this->importedQuestions[dirname($xmlName).'/'.$xmlName->getFileName()])) {
                $this->container->get('ujm.exo_exercise')->addQuestionInStep($this->importedQuestions[dirname($xmlName).'/'.$xmlName->getFileName()]->getQuestion(), $this->step, $order);
            }

            ++$order;
        }
    }
}
