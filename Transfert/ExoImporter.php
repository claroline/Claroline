<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UJM\ExoBundle\Transfert;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Symfony\Component\Config\Definition\Processor;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Subscription;
use UJM\ExoBundle\Form\ExerciseHandler;

/**
 * @DI\Service("claroline.importer.exo_importer")
 * @DI\Tag("claroline.importer")
 */
class ExoImporter extends Importer implements ConfigurationInterface
{
    private $container;
    private $om;

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

    public function  getConfigTreeBuilder()
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
        $rootPath = $this->getRootPath();

        $rootNode
            ->children()
                ->arrayNode('file')
                    ->children()
                        ->scalarNode('path')->isRequired()->end()
                        ->scalarNode('version')->end()
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
        //this is the root of the unzipped archive
        $rootPath = $this->getRootPath();

        $qtiRepos = $this->container->get('ujm.qti_repository');

        if ($exercises = opendir($rootPath.'/qti')) {
            while (($exercise = readdir($exercises)) !== false) {
                if ($exercise != '.' && $exercise != '..') {
                    $newExercise = new Exercise();
                    $newExercise->setTitle($exercise);
                    $newExercise->setDateCreate(new \Datetime());
                    $newExercise->setNbQuestionPage(1);
                    $newExercise->setNbQuestion(0);
                    $newExercise->setDuration(0);
                    $newExercise->setMaxAttempts(0);
                    $newExercise->setStartDate(new \Datetime());
                    $newExercise->setEndDate(new \Datetime());
                    $newExercise->setDateCorrection(new \Datetime());
                    $newExercise->setCorrectionMode('1');
                    $newExercise->setMarkMode('1');
                    $newExercise->setPublished(FALSE);
                    $this->om->persist($newExercise);
                    $this->om->flush();

                    $subscription = new Subscription($qtiRepos->getQtiUser(), $newExercise);
                    $subscription->setAdmin(1);
                    $subscription->setCreator(1);

                    $this->om->persist($subscription);
                    $this->om->flush();
                    $questions = opendir($rootPath.'/qti/'.$exercise);
                    while (($question = readdir($questions)) !== false) {
                        $qtiRepos->createDirQTI();
                        if ($question != '.' && $question != '..') {
                            $files = opendir($rootPath.'/qti/'.$exercise.'/'.$question);
                            while (($file = readdir($files)) !== false) {
                                if ($file != '.' && $file != '..') {
                                    copy($rootPath.'/qti/'.$exercise.'/'.$question.'/'.$file, $qtiRepos->getUserDir().$file);
                                }
                            }
                        }
                        $qtiRepos->scanFilesToImport($newExercise);
                    }
               }
           }
        }

        return $newExercise;
    }

    public function export(Workspace $workspace, array &$files, $object)
    {
        $qtiRepos = $this->container->get('ujm.qti_repository');
        $qtiRepos->createDirQTI($object->getTitle());

        $interRepos = $this->om->getRepository('UJMExoBundle:Interaction');
        $interactions = $interRepos->getExerciseInteraction(
                $this->container->get('doctrine')->getManager(),
                $object->getId(), FALSE);

        exec('mkdir '.$qtiRepos->getUserDir().'questions');
        $i = 1;
        foreach ($interactions as $interaction) {
            $qtiRepos->export($interaction);
            exec('mkdir '.$qtiRepos->getUserDir().'questions/'.'question_'.$i);
            $iterator = new \DirectoryIterator($qtiRepos->getUserDir());
            foreach ($iterator as $element) {
                if (!$element->isDot() && $element->isFile()) {
                    exec ('mv '.$qtiRepos->getUserDir().$element->getFilename().' '.$qtiRepos->getUserDir().'questions/'.'question_'.$i);
                }
            }
            $i++;
        }

        $pathQtiDir = $qtiRepos->getUserDir().'questions';
        $questions = new \DirectoryIterator($pathQtiDir);
        $i = 1;
        foreach ($questions as $question) {
            if ($question != '.' && $question != '..') {
                $iterator = new \DirectoryIterator($pathQtiDir.'/'.$question->getFilename());
                foreach ($iterator as $element) {
                    if (!$element->isDot() && $element->isFile()) {
                        $localPath = 'qti/'.$object->getTitle().'/question_'.$i.'/'.$element->getFileName();
                        $files[$localPath] = $element->getPathName();
                    }
                }
                $i++;
            }
        }

        $version = '1';
        $path = 'qti/'.$object->getTitle();

        $data = array(array('file' => array(
            'path' => $path,
            'version' =>  $version
        )));

        return $data;
    }

    public static function fileNotExists($v, $rootpath)
    {
        $ds = DIRECTORY_SEPARATOR;

        return !file_exists($rootpath . $ds . $v);;
    }
}
