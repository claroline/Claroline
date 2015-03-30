<?php

namespace UJM\ExoBundle\Services\classes\QTI;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class qtiServices {

    /**
     * For create a zip where questions while be integrate
     *
     * @param type $tmpFileName
     *
     * @param type $title
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     */
    public function createZip($tmpFileName, $title) {
        $response = new BinaryFileResponse($tmpFileName);
        //$response->headers->set('Content-Type', $content->getContentType());
        $response->headers->set('Content-Type', 'application/application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$title.'_QTI-Archive.zip');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate'); // HTTP 1.1.
        $response->headers->set('Pragma', 'no-cache'); // HTTP 1.0.
        $response->headers->set('Expires', '0'); // Proxies.

        return $response;
    }

    /**
     * sort the paths of questions
     *
     * @param UJM\ExoBundle\Services\classes\QTI\qtiRepository $qtiRepos
     *
     * @return array of String array with the paths of questions sorted
     */
    public function sortPathOfQuestions($qtiRepos) {
        $pathQtiDir = $qtiRepos->getUserDir().'questions';
        $questions = new \DirectoryIterator($pathQtiDir);
        //create array with sort file
        $qdirs = array();
        foreach ($questions as $question) {
            if ($question != '.' && $question != '..' && $question->getExtension() == "") {
                $qdirs[] = $pathQtiDir.'/'.$question->getFilename();
            }
        }
        sort($qdirs);

        return $qdirs;
    }
}