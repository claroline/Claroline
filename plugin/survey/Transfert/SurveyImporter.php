<?php

namespace Claroline\SurveyBundle\Transfert;

use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\SurveyBundle\Manager\SurveyManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.importer.claroline_survey_importer")
 * @DI\Tag("claroline.importer")
 */
class SurveyImporter extends Importer implements ConfigurationInterface, RichTextInterface
{
    /**
     * @var \Icap\LessonBundle\Manager\SurveyManager
     */
    private $surveyManager;

    private $om;

    private $container;

    /**
     * @DI\InjectParams({
     *     "surveyManager" = @DI\Inject("claroline.manager.survey_manager"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "container"     = @DI\Inject("service_container")
     * })
     */
    public function __construct(SurveyManager $surveyManager, ObjectManager $om, ContainerInterface $container)
    {
        $this->surveyManager = $surveyManager;
        $this->om = $om;
        $this->container = $container;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $this->addSurveyDescription($rootNode);

        return $treeBuilder;
    }

    public function getName()
    {
        return 'claroline_survey';
    }

    public function addSurveyDescription($rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('descriptionPath')->end()
                ->arrayNode('questions')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('title')->isRequired()->end()
                            ->scalarNode('questionPath')->isRequired()->end()
                            ->scalarNode('type')->end()
                            ->arrayNode('multiple_choices')
                                ->children()
                                    ->booleanNode('horizontal')->end()
                                    ->arrayNode('choices')
                                        ->prototype('array')
                                            ->children()
                                                ->scalarNode('contentPath')->end()
                                                ->booleanNode('other')->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->booleanNode('commentAllowed')->end()
                            ->scalarNode('commentLabelPath')->end()
                            ->booleanNode('richText')->defaultTrue()->end()
                            ->integerNode('questionOrder')->end()
                            ->booleanNode('mandatory')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('published')->defaultFalse()->end()
                ->booleanNode('closed')->defaultFalse()->end()
                ->booleanNode('hasPublicResult')->defaultFalse()->end()
                ->booleanNode('allowAnswerEdition')->defaultFalse()->end()
                ->scalarNode('startDate')->end()
                ->scalarNode('endDate')->end()
                ->end()
            ->end();
    }

    /**
     * @param array $data
     */
    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);
    }

    public function import(array $data, $name, $created, $workspace)
    {
        $rootPath = $this->getRootPath();
        $loggedUser = $this->getOwner();

        return $this->surveyManager->importSurvey($data, $rootPath, $loggedUser, $workspace);
    }

    /**
     * @param Workspace $workspace
     * @param array     $files
     * @param mixed     $object
     *
     * @return array $data
     */
    public function export($workspace, array &$files, $object)
    {
        return $this->surveyManager->exportSurvey($workspace, $files, $object);
    }

    public function format($data)
    {
        // Format description
        if (isset($data['descriptionPath'])) {
            $text = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$data['descriptionPath']);
            $entity = $this->om->getRepository('Claroline\SurveyBundle\Entity\Survey')->findOneByDescription($text);
            $formattedText = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
            $entity->setDescription($formattedText);
            $this->om->persist($entity);
        }

        // Format question text, comment label, multiple question choice
        if (isset($data['questions'])) {
            foreach ($data['questions'] as $question) {
                $questionText = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$question['questionPath']);
                $commentLabelText = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$question['commentLabelPath']);
                $entity = $this->om->getRepository('Claroline\SurveyBundle\Entity\Question')->findOneByQuestion($questionText);
                $formattedQuestionText = $this->container->get('claroline.importer.rich_text_formatter')->format($questionText);
                $formattedCommentLabelText = $this->container->get('claroline.importer.rich_text_formatter')->format($commentLabelText);

                $entity->setQuestion($formattedQuestionText);
                $entity->setCommentLabel($formattedCommentLabelText);
                $this->om->persist($entity);

                if ($question['type'] === 'multiple_choice_single' || $question['type'] === 'multiple_choice_multiple') {
                    foreach ($question['multiple_choices']['choices'] as $choice) {
                        $choiceText = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$choice['contentPath']);
                        $entity = $this->om->getRepository('Claroline\SurveyBundle\Entity\Choice')->findOneByContent($choiceText);
                        $formattedChoiceText = $this->container->get('claroline.importer.rich_text_formatter')->format($choiceText);

                        $entity->setContent($formattedChoiceText);
                        $this->om->persist($entity);
                    }
                }
            }
        }
    }
}
