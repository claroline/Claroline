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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Manager\SubscriptionManager;
use UJM\ExoBundle\Services\classes\QTI\QtiRepository;
use UJM\ExoBundle\Services\classes\QTI\QtiServices;

/**
 * @DI\Service("claroline.importer.exo_importer")
 * @DI\Tag("claroline.importer")
 */
class ExoImporter extends Importer implements ConfigurationInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var QtiServices
     */
    private $qtiService;

    /**
     * @var QtiRepository
     */
    private $qtiRepository;

    /**
     * @var SubscriptionManager
     */
    private $subscriptionManager;

    /**
     * @var bool
     */
    private $new = true;

    /**
     * @DI\InjectParams({
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "qtiService"          = @DI\Inject("ujm.exo_qti"),
     *     "qtiRepository"       = @DI\Inject("ujm.exo_qti_repository"),
     *     "subscriptionManager" = @DI\Inject("ujm.exo.subscription_manager")
     * })
     *
     * @param ObjectManager       $om
     * @param QtiServices         $qtiService,
     * @param QtiRepository       $qtiRepository,
     * @param SubscriptionManager $subscriptionManager
     */
    public function __construct(
        ObjectManager $om,
        QtiServices $qtiService,
        QtiRepository $qtiRepository,
        SubscriptionManager $subscriptionManager)
    {
        $this->om = $om;
        $this->qtiService = $qtiService;
        $this->qtiRepository = $qtiRepository;
        $this->subscriptionManager = $subscriptionManager;
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

    public function addExoDescription(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                // exercise properties
                ->arrayNode('exercise')
                    ->children()
                        ->scalarNode('path')->isRequired()->end()
                        ->scalarNode('version')->end()
                        ->scalarNode('title')->end()
                        ->scalarNode('description')->end()
                        ->booleanNode('shuffle')->end()
                        ->scalarNode('nbQuestion')->end()
                        ->booleanNode('keepSameQuestion')->end()
                        ->scalarNode('duration')->end()
                        ->booleanNode('doPrint')->end()
                        ->scalarNode('maxAttempts')->end()
                        ->scalarNode('correctionMode')->end()
                        ->scalarNode('dateCorrection')->end()
                        ->scalarNode('markMode')->end()
                        ->booleanNode('dispButtonInterrupt')->end()
                        ->booleanNode('lockAttempt')->end()
                        ->booleanNode('anonymous')->end()
                        ->scalarNode('type')->end()
                    ->end()
                ->end()

                // Steps
                ->arrayNode('steps')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('text')->end()
                            ->scalarNode('order')->end()
                            ->booleanNode('shuffle')->end()
                            ->scalarNode('nbQuestion')->end()
                            ->scalarNode('keepSameQuestion')->end()
                            ->scalarNode('duration')->end()
                            ->scalarNode('maxAttempts')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);
    }

    public function import(array $data)
    {
        $this->om->startFlushSuite();
        //this is the root of the unzipped archive
        $rootPath = $this->getRootPath();
        $exoPath = $data['data']['exercise']['path'];

        $newExercise = $this->createExo($data['data']['exercise'], $this->qtiRepository->getQtiUser());

        if (file_exists($rootPath.'/'.$exoPath)) {
            $this->createQuestion($data['data']['steps'], $newExercise, $rootPath.'/'.$exoPath);
        }
        $this->om->endFlushSuite();
        $this->om->forceFlush();

        return $newExercise;
    }

    public function export(Workspace $workspace, array &$files, $object)
    {
        $exoTitle = hash('sha1', $object->getResourceNode()->getName());
        $this->qtiRepository->createDirQTI($exoTitle, $this->new);
        $this->new = false;

        $steps = $this->getStepsToExport($object, $exoTitle, $files);

        $version = '1';
        $path = 'qti/'.$exoTitle;

        $data['exercise'] = [
            'path' => $path,
            'version' => $version,
            'description' => $object->getDescription(),
            'shuffle' => $object->getShuffle(),
            'nbQuestion' => $object->getPickSteps(),
            'keepSameQuestion' => $object->getKeepSteps(),
            'duration' => $object->getDuration(),
            'doPrint' => $object->getDoprint(),
            'maxAttempts' => $object->getMaxAttempts(),
            'correctionMode' => $object->getCorrectionMode(),
            'dateCorrection' => $object->getDateCorrection(),
            'markMode' => $object->getMarkMode(),
            'dispButtonInterrupt' => $object->getDispButtonInterrupt(),
            'lockAttempt' => $object->getLockAttempt(),
            'anonymous' => $object->getAnonymous(),
            'type' => $object->getType(),
        ];

        $data['steps'] = $steps;

        return $data;
    }

    /**
     * create the exercise.
     *
     * @param array $exercise properties of the exercise
     * @param User  $user
     *
     * @return Exercise
     */
    private function createExo(array $exercise, User $user)
    {
        $newExercise = new Exercise();
        $newExercise->setDescription($exercise['description']);
        $newExercise->setShuffle($exercise['shuffle']);
        $newExercise->setPickSteps($exercise['nbQuestion']);
        $newExercise->setKeepSteps($exercise['keepSameQuestion']);
        $newExercise->setDuration($exercise['duration']);
        $newExercise->setDoprint($exercise['doPrint']);
        $newExercise->setMaxAttempts($exercise['maxAttempts']);
        $newExercise->setDateCorrection(new \Datetime());
        $newExercise->setCorrectionMode($exercise['correctionMode']);
        $newExercise->setMarkMode($exercise['markMode']);
        $newExercise->setDispButtonInterrupt($exercise['dispButtonInterrupt']);
        $newExercise->setLockAttempt($exercise['lockAttempt']);
        $newExercise->setAnonymous($exercise['anonymous']);
        $newExercise->setType($exercise['type']);

        $this->om->persist($newExercise);

        $this->subscriptionManager->subscribe($newExercise, $user);

        $this->om->flush();

        return $newExercise;
    }

    /**
     * create the exercise.
     *
     * @param array    $step     - properties of the step
     * @param Exercise $exercise
     *
     * @return Step
     */
    private function createStep(array $step, Exercise $exercise)
    {
        $newStep = new Step();
        $newStep->setText($step['text']);
        $newStep->setOrder($step['order']);
        $newStep->setShuffle($step['shuffle']);
        $newStep->setNbQuestion($step['nbQuestion']);
        $newStep->setKeepSameQuestion($step['keepSameQuestion']);
        $newStep->setDuration($step['duration']);
        $newStep->setMaxAttempts($step['maxAttempts']);
        $newStep->setExercise($exercise);

        $this->om->persist($newStep);
        $this->om->flush();

        return $newStep;
    }

    /**
     * create the step and the question.
     *
     * @param Step[]   $steps
     * @param Exercise $exercise
     * @param string   $exoPath
     */
    private function createQuestion(array $steps, Exercise $exercise, $exoPath)
    {
        foreach ($steps as $step) {
            $this->qtiRepository->razValues();
            $newStep = $this->createStep($step, $exercise);
            $questions = opendir($exoPath.'/'.$step['order']);
            $questionFiles = [];
            while (($question = readdir($questions)) !== false) {
                if ($question != '.' && $question != '..') {
                    $questionFiles[] = $exoPath.'/'.$step['order'].'/'.$question;
                }
            }
            sort($questionFiles);
            foreach ($questionFiles as $question) {
                $this->qtiRepository->createDirQTI();
                $files = opendir($question);
                while (($file = readdir($files)) !== false) {
                    if ($file != '.' && $file != '..') {
                        copy($question.'/'.$file, $this->qtiRepository->getUserDir().'ws/'.$file);
                    }
                }
                $this->qtiRepository->scanFilesToImport($newStep);
            }
            $this->qtiRepository->assocExerciseQuestion(true);
        }
    }

    /**
     * return steps of an exercise in an array.
     *
     * @param Exercise $object
     * @param string   $exoTitle
     * @param array    $files
     *
     * @return array
     */
    private function getStepsToExport(Exercise $object, $exoTitle, array &$files)
    {
        /** @var \UJM\ExoBundle\Repository\QuestionRepository $questionRepo */
        $questionRepo = $this->om->getRepository('UJMExoBundle:Question');

        $steps = [];
        foreach ($object->getSteps() as $step) {
            $s = [
                'text' => $step->getText(),
                'order' => $step->getOrder(),
                'shuffle' => $step->getShuffle(),
                'nbQuestion' => $step->getNbQuestion(),
                'keepSameQuestion' => $step->getKeepSameQuestion(),
                'duration' => $step->getDuration(),
                'maxAttempts' => $step->getMaxAttempts(),
            ];

            $steps[] = $s;
            $questions = $questionRepo->findByStep($step);
            $this->qtiService->createQuestionsDirectory($questions, $step->getOrder());
            $dirs = $this->qtiService->sortPathOfQuestions($this->qtiRepository, $step->getOrder());

            $i = 'a';
            foreach ($dirs as $dir) {
                $iterator = new \DirectoryIterator($dir);

                /** @var \DirectoryIterator $element */
                foreach ($iterator as $element) {
                    if (!$element->isDot() && $element->isFile()) {
                        $localPath = 'qti/'.$exoTitle.'/'.$step->getOrder().'/'.$step->getOrder().'_question_'.$i.'/'.$element->getFilename();
                        $files[$localPath] = $element->getPathname();
                    }
                }
                $i .= 'a';
            }
        }

        return $steps;
    }
}
