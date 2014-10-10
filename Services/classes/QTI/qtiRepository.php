<?php

/**
 * To create temporary repository for QTI files
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

class qtiRepository {

    protected $userDir;

    /**
     * Create the repository
     *
     * @access protected
     *
     */
    protected function createDirQTI()
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
     * @access protected
     *
     */
    protected function removeDirectory()
    {
        if(!is_dir($$this->userDir)){
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

}
