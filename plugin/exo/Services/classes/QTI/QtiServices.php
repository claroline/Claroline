<?php

namespace UJM\ExoBundle\Services\classes\QTI;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QtiServices
{
    /**
     * For create a zip where questions while be integrate.
     *
     * @param string $tmpFileName
     * @param string $title
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function createZip($tmpFileName, $title)
    {
        $response = new BinaryFileResponse($tmpFileName);
        $response->headers->set('Content-Type', 'application/application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$title.'_QTI-Archive.zip');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate'); // HTTP 1.1.
        $response->headers->set('Pragma', 'no-cache'); // HTTP 1.0.
        $response->headers->set('Expires', '0'); // Proxies.

        return $response;
    }

    /**
     * sort the paths of questions.
     *
     * @param QtiRepository $qtiRepo
     * @param string        $stepDir directory of step
     *
     * @return array of String array with the paths of questions sorted
     */
    public function sortPathOfQuestions(QtiRepository $qtiRepo, $stepDir = '')
    {
        $pathQtiDir = $qtiRepo->getUserDir().'questions';
        if ($stepDir != '') {
            $pathQtiDir .= '/'.$stepDir;
        }
        $questions = new \DirectoryIterator($pathQtiDir);
        //create array with sort file
        $dirs = array();
        foreach ($questions as $question) {
            if ($question != '.' && $question != '..' && $question->getExtension() == '') {
                $dirs[] = $pathQtiDir.'/'.$question->getFilename();
            }
        }
        sort($dirs);

        return $dirs;
    }
}
