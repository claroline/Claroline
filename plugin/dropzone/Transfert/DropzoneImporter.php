<?php

namespace Icap\DropzoneBundle\Transfert;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Manager\DropzoneManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.importer.icap_dropzone_importer")
 * @DI\Tag("claroline.importer")
 */
class DropzoneImporter extends Importer implements ConfigurationInterface, RichTextInterface
{
    /**
     * @var \Icap\DropzoneBundle\Manager\DropzoneManager
     */
    private $dropzoneManager;

    /**
     * @DI\InjectParams({
     *      "dropzoneManager" = @DI\Inject("icap.manager.dropzone_manager"),
     *      "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *      "container"     = @DI\Inject("service_container")
     * })
     */
    public function __construct(DropzoneManager $dropzoneManager, ObjectManager $om, ContainerInterface $container)
    {
        $this->dropzoneManager = $dropzoneManager;
        $this->om = $om;
        $this->container = $container;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $this->addDropzoneDescription($rootNode);

        return $treeBuilder;
    }

    public function getName()
    {
        return 'icap_dropzone';
    }

    public function addDropzoneDescription($rootNode)
    {
        $rootNode
            ->children()
                ->integerNode('editionState')->defaultValue(1)->end()
                ->scalarNode('instruction')->end()
                ->scalarNode('correctionInstruction')->end()
                ->scalarNode('successMessage')->end()
                ->scalarNode('failMessage')->end()
                ->booleanNode('allowWorkspaceResource')->defaultFalse()->end()
                ->booleanNode('allowUpload')->defaultTrue()->end()
                ->booleanNode('allowUrl')->defaultFalse()->end()
                ->booleanNode('allowRichText')->defaultFalse()->end()
                ->booleanNode('peerReview')->defaultFalse()->end()
                ->integerNode('expectedTotalCorrection')->defaultValue(3)->end()
                ->booleanNode('displayNotationToLearners')->defaultFalse()->end()
                ->booleanNode('displayNotationMessageToLearners')->defaultFalse()->end()
                ->floatNode('minimumScoreToPass')->end()
                ->booleanNode('manualPlanning')->defaultTrue()->end()
                ->scalarNode('manualState')->defaultValue('notStarted')->end()
                ->scalarNode('startAllowDrop')->end()
                ->scalarNode('endAllowDrop')->end()
                ->scalarNode('startReview')->end()
                ->scalarNode('endReview')->end()
                ->booleanNode('allowCommentInCorrection')->defaultFalse()->end()
                ->booleanNode('forceCommentInCorrection')->defaultFalse()->end()
                ->booleanNode('diplayCorrectionsToLearners')->defaultFalse()->end()
                ->booleanNode('allowCorrectionDeny')->defaultFalse()->end()
                ->integerNode('totalCriteriaColumn')->defaultValue(4)->end()
                ->booleanNode('autoCloseOpenedDropsWhenTimeIsUp')->defaultFalse()->end()
                ->scalarNode('autoCloseState')->defaultValue(Dropzone::AUTO_CLOSED_STATE_WAITING)->end()
                ->booleanNode('notifyOnDrop')->defaultFalse()->end()
            ->end()
        ->end();
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);
    }

    public function import(array $data, $name)
    {
        return $this->dropzoneManager->importDropzone($data, $this->getRootPath());
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
        return $this->dropzoneManager->exportDropzone($workspace, $files, $object);
    }

    public function format($data)
    {
        if (isset($data['instruction'])) {
            $text = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$data['instruction']);
            $entity = $this->om->getRepository('Icap\DropzoneBundle\Entity\Dropzone')->findOneByInstruction($text);
            $formattedText = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
            $entity->setInstruction($formattedText);
        }

        if (isset($data['correctionInstruction'])) {
            $text = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$data['correctionInstruction']);
            $entity = $this->om->getRepository('Icap\DropzoneBundle\Entity\Dropzone')->findOneByCorrectionInstruction($text);
            $formattedText = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
            $entity->setCorrectionInstruction($formattedText);
        }

        if (isset($data['successMessage'])) {
            $text = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$data['successMessage']);
            $entity = $this->om->getRepository('Icap\DropzoneBundle\Entity\Dropzone')->findOneBySuccessMessage($text);
            $formattedText = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
            $entity->setSuccessMessage($formattedText);
        }

        if (isset($data['failMessage'])) {
            $text = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$data['failMessage']);
            $entity = $this->om->getRepository('Icap\DropzoneBundle\Entity\Dropzone')->findOneByFailMessage($text);
            $formattedText = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
            $entity->setFailMessage($formattedText);
        }
        $this->om->persist($entity);
    }
}
