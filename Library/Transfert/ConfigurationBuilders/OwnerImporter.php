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

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Claroline\CoreBundle\Library\Transfert\Importer;

class OwnerImporter extends Importer implements ConfigurationInterface
{
    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('owner');
        $this->addOwnerSection($rootNode);

        return $treeBuilder;
    }

    public function addOwnerSection($rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('first_name')->isRequired()->end()
                ->scalarNode('last_name')->isRequired()->end()
                ->scalarNode('username')->isRequired()->end()
                ->scalarNode('password')->isRequired()->end()
                ->scalarNode('mail')->isRequired()->end()
                ->scalarNode('code')->isRequired()->end()
                ->arrayNode('roles')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    /**
     * Validate the workspace properties.
     *
     * @todo validate the owner~
     * @param array $data
     */
    public function validate(array $data)
    {
        $processor = new Processor();
        self::setData($data);
        $processor->processConfiguration($this, $data);

        //checks if the owner already exists~

    }

    private static function setData($data)
    {
        self::$data = $data;
    }

    private static function getData()
    {
        return self::$data;
    }

    public function getName()
    {
        return 'owner_importer';
    }
}