<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UJM\ExoBundle\Transfer;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Symfony\Component\Config\Definition\Processor;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Subscription;

/**
 * @DI\Service("claroline.importer.exo_importer")
 * @DI\Tag("claroline.importer")
 */
class ExoImporter extends Importer implements ConfigurationInterface
{
    private $container;
    private $om;
    private $new = true;

    /**
     * @DI\InjectParams({
     *      "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *      "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($om, $container)
    {
        $this->container = $container;
        $this->om = $om;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $this->addExoDescription($rootNode);

        return $treeBuilder;
    }

    public function getName()
    {
        return 'ujm_exercise';
    }

    public function addExoDescription($rootNode)
    {
        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('file')
                        ->children()
                            ->scalarNode('path')->isRequired()->end()
                            ->scalarNode('version')->end()
                            ->scalarNode('title')->end()
                        ->end()
                    ->end()
                ->end()
                ->end()
            ->end();
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $result = $processor->processConfiguration($this, $data);
    }

    public function import(array $data)
    {
        $this->om->startFlushSuite();
        //this is the root of the unzipped archive
        $rootPath = $this->getRootPath();
        $exoPath = $data['data'][0]['file']['path'];
        $exoTitle = $data['data'][0]['file']['title'];

        $qtiRepos = $this->container->get('ujm.exo_qti_repository');
        $qtiRepos->razValues();
        $newExercise = $this->createExo($exoTitle, $qtiRepos->getQtiUser());

        if (file_exists($rootPath.'/'.$exoPath)) {
            $questions = opendir($rootPath.'/'.$exoPath);
            $questionFiles = array();
            while (($question = readdir($questions)) !== false) {
                if ($question != '.' && $question != '..') {
                    $questionFiles[] = $rootPath.'/'.$exoPath.'/'.$question;
                }
            }
            sort($questionFiles);
            foreach ($questionFiles as $question) {
                $qtiRepos->createDirQTI();
                $files = opendir($question);
                while (($file = readdir($files)) !== false) {
                    if ($file != '.' && $file != '..') {
                        copy($question.'/'.$file, $qtiRepos->getUserDir().$file);
                    }
                }
                $qtiRepos->scanFilesToImport($newExercise);
            }
        }
        $this->om->endFlushSuite();
        $this->om->forceFlush();
        $qtiRepos->assocExerciseQuestion(true);

        return $newExercise;
    }

    public function export(Workspace $workspace, array &$files, $object)
    {
        $exoTitle = hash('sha1', $object->getTitle());
        $qtiRepos = $this->container->get('ujm.exo_qti_repository');
        $qtiRepos->createDirQTI($exoTitle, $this->new);
        $this->new = false;

        $steps = $this->getStepsToExport($object, $qtiRepos, $exoTitle, $files);

        $version = '1';
        $path = 'qti/'.$exoTitle;

        $data = array(array('file' => array(
            'path' => $path,
            'version' => $version,
            'title' => $object->getTitle(),
            'steps' => $steps,
        )));

        return $data;
    }

    public static function fileNotExists($v, $rootpath)
    {
        $ds = DIRECTORY_SEPARATOR;

        return !file_exists($rootpath.$ds.$v);
    }

    /**
     * create the exercise.
     *
     * @param String      $title
     * @param Object User $user
     */
    private function createExo($title, $user)
    {
        $newExercise = new Exercise();
        $newExercise->setTitle($title);
        $newExercise->setNbQuestion(0);
        $newExercise->setDuration(0);
        $newExercise->setMaxAttempts(0);
        $newExercise->setDateCorrection(new \Datetime());
        $newExercise->setCorrectionMode('1');
        $newExercise->setMarkMode('1');
        $this->om->persist($newExercise);
        $this->om->flush();

        $subscription = new Subscription($user, $newExercise);
        $subscription->setAdmin(1);
        $subscription->setCreator(1);

        $this->om->persist($subscription);
        $this->om->flush();

        return $newExercise;
    }

    /**
     * create the directory questions to export an exercise and export the qti files.
     *
     * @param UJM\ExoBundle\Services\classes\QTI\qtiRepository $qtiRepos
     * @param collection of  UJM\ExoBundle\Entity\Question  $interactions
     * @param Integer $numStep order the step in the exercise
     */
    private function createQuestionsDirectory($qtiRepos, $questions, $numStep)
    {
        @mkdir($qtiRepos->getUserDir().'questions');
        $i = 'a';
        foreach ($questions as $question) {
            $qtiRepos->export($question);
            @mkdir($qtiRepos->getUserDir().'questions/'.$numStep.'_question_'.$i);
            $iterator = new \DirectoryIterator($qtiRepos->getUserDir());
            foreach ($iterator as $element) {
                if (!$element->isDot() && $element->isFile()) {
                    rename($qtiRepos->getUserDir().$element->getFilename(), $qtiRepos->getUserDir().'questions/'.$numStep.'_question_'.$i.'/'.$element->getFilename());
                }
            }
            $i .= 'a';
        }
    }

    /**
     * return steps of an exercise in an array
     *
     * @param Object Exercise $ojbect
     * @param UJM\ExoBundle\Services\classes\QTI\qtiRepository $qtiRepos
     * @param String $exoTitle
     * @return array
     */
    private function getStepsToExport($object, $qtiRepos, $exoTitle, array &$files)
    {
        $qtiServ = $this->container->get('ujm.exo_qti');

        $questionRepo = $this->om->getRepository('UJMExoBundle:Question');

        $steps = array();
        foreach ($object->getSteps() as $step) {
            $s = array(
                    'Text' => $step->getText(),
                    'order' => $step->getOrder(),
                    'shuffle' => $step->getShuffle(),
                    'nbQuestion' => $step->getNbQuestion(),
                    'keepSameQuestion' => $step->getKeepSameQuestion(),
                    'duration' => $step->getDuration(),
                    'maxAttempts' => $step->getMaxAttempts()
                    );
            $steps[] = $s;
            $questions = $questionRepo->findByStep($step);
            $this->createQuestionsDirectory($qtiRepos, $questions, $step->getOrder());
            $qdirs = $qtiServ->sortPathOfQuestions($qtiRepos);

            $i = 'a';
            foreach ($qdirs as $dir) {
                $iterator = new \DirectoryIterator($dir);
                foreach ($iterator as $element) {
                    if (!$element->isDot() && $element->isFile()) {
                        $localPath = 'qti/'.$exoTitle.'/'.$step->getOrder().'_question_'.$i.'/'.$element->getFileName();
                        $files[$localPath] = $element->getPathName();
                    }
                }
                $i .= 'a';
            }
        }

        return $steps;
    }
}
