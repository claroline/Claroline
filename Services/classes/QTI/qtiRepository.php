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

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext Dependency Injection
     *
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
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

}