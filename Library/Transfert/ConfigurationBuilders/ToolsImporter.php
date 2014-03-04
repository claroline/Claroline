<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders;

use Claroline\CoreBundle\Library\Transfert\Importer;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ToolsImporter extends Importer implements ConfigurationInterface
{
    private static $data;

    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tools');
        $this->addToolsSection($rootNode);

        return $treeBuilder;
    }

    public function addToolsSection($rootNode)
    {
        $configuration = $this->getConfiguration();
        $availableRoleName = array();

        if (isset($configuration['roles'])) {
            foreach ($configuration['roles'] as $role) {
                $availableRoleName[] = $role['role']['name'];
            }
        }

        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('tool')
                        ->children()
                            ->scalarNode('type')->isRequired()->end()
                            ->scalarNode('translation')->isRequired()->end()
                            ->variableNode('data')->end()
                            ->arrayNode('import')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('path')->isRequired()->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('roles')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->isRequired()
                                            ->validate()
                                                ->ifTrue(
                                                    function ($v) use ($availableRoleName) {
                                                        return call_user_func_array(
                                                            __CLASS__ . '::roleNameExists',
                                                            array($v, $availableRoleName)
                                                        );
                                                    }
                                                )
                                                ->thenInvalid("The role name %s doesn't exists")
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Validate the workspace properties.
     *
     * @param array $data
     * @throws InvalidConfigurationException
     */
    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);

        //root must exists

        //and has no parent

        foreach ($data['tools'] as $tool) {
            $importer = $this->getImporterByName($tool['tool']['type']);

            if (!$importer) {
                throw new InvalidConfigurationException('The importer ' . $tool['tool']['type'] . ' does not exist');
            }

            if (isset($tool['tool']['data'])) {
                $importer->validate($tool['tool']['data']);
            }
        }
    }

    public function getName()
    {
        return 'tools';
    }

    public static function roleNameExists($v, $roles)
    {
        return !in_array($v, $roles);
    }
}