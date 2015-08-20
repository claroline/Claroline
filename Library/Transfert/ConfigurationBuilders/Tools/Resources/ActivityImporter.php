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

use Claroline\CoreBundle\Library\Transfert\Importer;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpFoundation\File\File as SfFile;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Activity\ActivityParameters;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

/**
 * @DI\Service("claroline.tool.resources.activity_importer")
 * @DI\Tag("claroline.importer")
 */
class ActivityImporter extends Importer implements ConfigurationInterface
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
                                                __CLASS__ . '::fileNotExists',
                                                array($v, $rootPath)
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
        return $type == 'yml' ? true: false;
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
            //throw new \Exception(var_dump(array_keys($created)));
            $description = file_get_contents($this->getRootPath() . $ds . $item['activity']['description']);
            $activity = new Activity();
            $activity->setTitle($item['activity']['title']);
            $primaryResource = !empty($item['activity']['primary_resource']) && isset($created[$item['activity']['primary_resource']]) && $created[$item['activity']['primary_resource']] ?
                $created[$item['activity']['primary_resource']]->getResourceNode():
                null;
            $activity->setPrimaryResource($primaryResource);
            $activity->setDescription($description);
            $parameters = new ActivityParameters();
            $parameters->setMaxDuration($item['activity']['max_duration']);
            $parameters->setWho($item['activity']['who']);
            $parameters->setWhere($item['activity']['where']);
            $parameters->setEvaluationType($item['activity']['evaluation_type']);

            foreach ($item['activity']['secondary_resources'] as $secondaryResource) {
                $parameters->addSecondaryResource($created[$secondaryResource['uid']]->getResourceNode());
            }

            $activity->setParameters($parameters);
            $this->om->persist($activity);
            $this->om->persist($parameters);

            return $activity;
        }
    }

    public function export(Workspace $workspace, array &$files, $object)
    {
        $uid = uniqid() . '.txt';
        $uid = uniqid() . '.txt';
        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $uid;
        $content = $object->getDescription();
        file_put_contents($tmpPath, $content);
        $files[$uid] = $tmpPath;
        $parameters = $object->getParameters();
        $resources = $parameters->getSecondaryResources();
        $secondaryResources = array();
        //$rules = array();

        foreach ($resources as $resource) {
            $secondaryResources[] = array('uid' => $resource->getId());
        }

        $primaryResource = $object->getPrimaryResource() ? $object->getPrimaryResource()->getId(): null;

        $data = array(array('activity' => array(
            'description' => $uid,
            'title' => $object->getTitle(),
            'primary_resource' => $primaryResource,
            'max_duration' => $parameters->getMaxDuration(),
            'who' => $parameters->getWho(),
            'where' => $parameters->getWhere(),
            'evaluation_type' => $parameters->getEvaluationType(),
            'secondary_resources' => $secondaryResources
        )));

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

        return !file_exists($rootpath . $ds . $v);;
    }

    public function format($data)
    {
        if ($path = $data[0]['activity']['description']) {
            $description = file_get_contents($this->getRootPath() . DIRECTORY_SEPARATOR . $path);
            $entities = $this->om->getRepository('ClarolineCoreBundle:Resource\Activity')->findByDescription($description);

            foreach ($entities as $entity) {
                $text = $entity->getDescription();
                $text = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
                $entity->setDescription($text);
                $this->om->persist($entity);
            }
        }
    }
}
