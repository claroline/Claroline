<?php

namespace UJM\ExoBundle\Services\classes\QTI;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QtiServices
{
    private $container;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }
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
        var_dump($stepDir);
        $pathQtiDir = $qtiRepo->getUserDir().'questions';
        if ($stepDir != '') {
            $pathQtiDir .= '/'.$stepDir;
        }
        $questions = new \DirectoryIterator($pathQtiDir);
        //create array with sort file
        $dirs = array();
        foreach ($questions as $question) {
            var_dump($question != '..');
            if ($question != '.' && $question != '..' && $question->getExtension() == '') {
                echo('la');
                $dirs[] = $pathQtiDir.'/'.$question->getFilename();
            }
        }

        sort($dirs);

        return $dirs;
    }
    /**
         * create the directory questions to export an exercise and export the qti files.
         *
         * @param \UJM\ExoBundle\Entity\Question[] $questions
         * @param int                              $numStep
         */
    public function createQuestionsDirectory(array $questions, $numStep)
    {
        $qtiRepo = $this->container->get('ujm.exo_qti_repository');

        @mkdir($qtiRepo->getUserDir().'questions');
        $i = 'a';
        @mkdir($qtiRepo->getUserDir().'questions/'.$numStep);
        foreach ($questions as $question) {
            $qtiRepo->export($question);
            @mkdir($qtiRepo->getUserDir().'questions/'.$numStep.'/'.$numStep.'_question_'.$i);
            $iterator = new \DirectoryIterator($qtiRepo->getUserDir());
            foreach ($iterator as $element) {
                if (!$element->isDot() && $element->isFile()) {
                    rename($qtiRepo->getUserDir().$element->getFilename(), $qtiRepo->getUserDir().'questions/'.$numStep.'/'.$numStep.'_question_'.$i.'/'.$element->getFilename());
                }
            }
            $i .= 'a';
        }
    }
}
