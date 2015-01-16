<?php

/**
 * To create temporary repository for QTI files
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use Symfony\Component\Security\Core\SecurityContextInterface;

class qtiRepository {

    private $userDir;
    private $securityContext;
    private $container;

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
    }

    /**
     * Create the repository
     *
     * @access public
     *
     */
    public function createDirQTI()
    {
        $this->userDir = './uploads/ujmexo/qti/'
                .$this->securityContext->getToken()
                ->getUser()->getUsername().'/';

        if (!is_dir('./uploads/ujmexo/')) {
            mkdir('./uploads/ujmexo/');
        }
        if (!is_dir('./uploads/ujmexo/qti/')) {
            mkdir('./uploads/ujmexo/qti/');
        }
        if (!is_dir($this->userDir)) {
            mkdir($this->userDir);
        } else {
            $this->removeDirectory();
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
        if(!is_dir($this->userDir)){
            throw new $this->createNotFoundException($this->userDir.' is not directory '.__LINE__.', file '.__FILE__);
        }
        $iterator = new \DirectoryIterator($this->userDir);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDot()) {
                if($fileinfo->isFile()) {
                    unlink($this->userDir."/".$fileinfo->getFileName());

                }
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
                                        $qtiImport->import($this, $ai);
                                        $imported = true;
                                        break;
                                    case 'selectPointInteraction': //graphic with the tag selectPointInteraction
                                        $qtiImport = $this->container->get('ujm.qti_graphic_import');
                                        $qtiImport->import($this, $ai);
                                        $imported = true;
                                        break;
                                    case 'hotspotInteraction': //graphic with the tag hotspotInteraction
                                        $qtiImport = $this->container->get('ujm.qti_graphic_import');
                                        $qtiImport->import($this, $ai);
                                        $imported = true;
                                        break;
                                    case 'extendedTextInteraction': //open
                                        $qtiImport = $this->container->get('ujm.qti_open_import');
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
                                $qtiImport->import($this, $ai);
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

        return true;
    }

}