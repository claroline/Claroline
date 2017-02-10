<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\Resources;

use Claroline\CoreBundle\Entity\Activity\ActivityParameters;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @DI\Service("claroline.tool.resources.activity_importer")
 * @DI\Tag("claroline.importer")
 */
class ActivityImporter extends Importer implements ConfigurationInterface, RichTextInterface
{
    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->maskManager = $container->get('claroline.manager.mask_manager');
        $this->resourceManager = $container->get('claroline.manager.resource_manager');
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $this->addActivitySection($rootNode);

        return $treeBuilder;
    }

    public function addActivitySection($rootNode)
    {
        $rootPath = $this->getRootPath();

        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('activity')
                        ->children()
                            ->scalarNode('description')->isRequired()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($rootPath) {
                                            return call_user_func_array(
                                                __CLASS__.'::fileNotExists',
                                                [$v, $rootPath]
                                            );
                                        }
                                    )
                                    ->thenInvalid("The file %s doesn't exists")
                                ->end()
                            ->end()
                            ->scalarNode('title')->isRequired()->end()
                            ->scalarNode('primary_resource')->end()
                            ->scalarNode('max_duration')->isRequired()->end()
                            ->scalarNode('who')->isRequired()->end()
                            ->scalarNode('where')->isRequired()->end()
                            ->scalarNode('evaluation_type')->isRequired()->end()
                            ->arrayNode('secondary_resources')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('uid')->isRequired()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    public function supports($type)
    {
        return $type === 'yml' ? true : false;
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);
    }

    public function import(array &$array, $name, $created)
    {
        $ds = DIRECTORY_SEPARATOR;

        foreach ($array['data'] as $item) {
            $description = file_get_contents($this->getRootPath().$ds.$item['activity']['description']);
            $activity = new Activity();
            $activity->setTitle($item['activity']['title']);
            $primaryResource = !empty($item['activity']['primary_resource']) && isset($created[$item['activity']['primary_resource']]) && $created[$item['activity']['primary_resource']] ?
                $created[$item['activity']['primary_resource']]->getResourceNode() :
                null;
            $activity->setPrimaryResource($primaryResource);
            $activity->setDescription($description);
            $parameters = new ActivityParameters();
            $parameters->setMaxDuration($item['activity']['max_duration']);
            $parameters->setWho($item['activity']['who']);
            $parameters->setWhere($item['activity']['where']);
            $parameters->setEvaluationType($item['activity']['evaluation_type']);

            foreach ($item['activity']['secondary_resources'] as $secondaryResource) {
                //in a perfect world, this shouldn't happend
                if (isset($created[$secondaryResource['uid']])) {
                    $parameters->addSecondaryResource($created[$secondaryResource['uid']]->getResourceNode());
                }
            }

            $activity->setParameters($parameters);
            $this->om->persist($activity);
            $this->om->persist($parameters);

            return $activity;
        }
    }

    public function export($workspace, array &$_files, $object)
    {
        // we need to add things that aren't here first...
        $_data = &$this->getExtendedData();

        // Get primary resource
        $primaryResource = $object->getPrimaryResource() ? $object->getPrimaryResource()->getId() : null;

        // Get secondary resources
        $secondaryResources = [];

        /** @var \Claroline\CoreBundle\Entity\Activity\ActivityParameters $parameters */
        $parameters = $object->getParameters();
        if (!empty($parameters)) {
            /** @var \Claroline\CoreBundle\Entity\Resource\ResourceNode $resource */
            foreach ($parameters->getSecondaryResources() as $resource) {
                if (!$this->container->get('claroline.importer.rich_text_formatter')->getItemFromUid($resource->getId(), $_data)) {
                    $this->addResourceToData($resource, $_data, $_files);
                }

                $secondaryResources[] = ['uid' => $resource->getId()];
            }
        }

        // Process rich text description
        $uid = uniqid().'.txt';
        $tmpPath = $this->container->get('claroline.config.platform_config_handler')->getParameter('tmp_dir').DIRECTORY_SEPARATOR.$uid;
        $content = $object->getDescription();
        file_put_contents($tmpPath, $content);
        $_files[$uid] = $tmpPath;

        $data = [
            [
                'activity' => [
                    'description' => $uid,
                    'title' => $object->getTitle(),
                    'primary_resource' => $primaryResource,
                    'max_duration' => $parameters->getMaxDuration(),
                    'who' => $parameters->getWho(),
                    'where' => $parameters->getWhere(),
                    'evaluation_type' => $parameters->getEvaluationType(),
                    'secondary_resources' => $secondaryResources,
                ],
            ],
        ];

        return $data;
    }

    public function getName()
    {
        return 'activity';
    }

    public function getPriority()
    {
        return 98;
    }

    public static function fileNotExists($v, $rootpath)
    {
        $ds = DIRECTORY_SEPARATOR;

        return !file_exists($rootpath.$ds.$v);
    }

    public function format($data)
    {
        if (!isset($data[0])) {
            return;
        }

        if ($path = $data[0]['activity']['description']) {
            $description = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$path);
            $entities = $this->om->getRepository('ClarolineCoreBundle:Resource\Activity')->findByDescription($description);

            foreach ($entities as $entity) {
                $text = $entity->getDescription();
                $text = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
                $entity->setDescription($text);
                $this->om->persist($entity);
            }
        }
    }

    private function createActivityFolder(array &$_data)
    {
        if ($this->activityFolderExists($_data)) {
            return;
        }

        $roles = [];
        $roles[] = ['role' => [
            'name' => 'ROLE_USER',
            'rights' => $this->maskManager->decodeMask(7, $this->resourceManager->getResourceTypeByName('directory')),
        ]];

        $parentId = $_data['root']['uid'];

        $_data['directories'][] = ['directory' => [
            'name' => 'activity_folder',
            'creator' => null,
            'parent' => $parentId,
            'published' => true,
            'uid' => 'activity_folder',
            'roles' => $roles,
            'index' => null,
        ]];
    }

    private function activityFolderExists(array $data)
    {
        if (!isset($data['directories'])) {
            return false;
        }
        foreach ($data['directories'] as $directory) {
            if ($directory['directory']['uid'] === 'activity_folder') {
                return true;
            }
        }

        return false;
    }

    private function addResourceToData(ResourceNode $node, array &$_data, array &$_files)
    {
        $this->createActivityFolder($_data);
        $el = $this->container->get('claroline.importer.rich_text_formatter')
            ->getImporterByName('resource_manager')->getResourceElement(
                $node,
                $node->getWorkspace(),
                $_files,
                $_data
            );
        $el['item']['parent'] = 'activity_folder';
        $el['item']['roles'] = [['role' => [
            'name' => 'ROLE_USER',
            'rights' => $this->maskManager->decodeMask(7, $this->resourceManager->getResourceTypeByName('activity')),
        ]]];
        $_data['items'][] = $el;
    }
}
