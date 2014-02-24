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
use Symfony\Component\Config\Definition\Processor;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.importer.properties_importer")
 * @DI\Tag("claroline.importer")
 */
class WorkspacePropertiesImporter extends Importer implements ConfigurationInterface
{
    private $om;
    private $owner;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('properties');
        $this->addPropertiesSection($rootNode);

        return $treeBuilder;
    }

    public function addPropertiesSection($rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('name')->isRequired()->end()
                ->scalarNode('code')->isRequired()->end()
                ->booleanNode('visible')->isRequired()->end()
                ->booleanNode('selfregistration')->isRequired()->end()
                ->booleanNode('selfUnregistration')->isRequired()->end()
                ->scalarNode('owner')->end()
                ->end()
            ->end();
    }

    public function getName()
    {
        return 'workspace_properties';
    }

    /**
     * Validate the workspace properties.
     *
     * @todo show the expected array
     * @param array $data
     */
    public function validate(array $data)
    {
        $processor = new Processor();
        //@todo exception handling
        $configuration = $processor->processConfiguration($this, $data);
        //@todo exception handling
        $this->om->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')
            ->findOneByCode($configuration['code']);
        //@todo exception handling
        $this->validateOwner($configuration['owner']);


    }

    function validateOwner($owner)
    {
        $manifest = $this->getManifest();

        if (isset ($manifest['members'])) {
            if (isset ($manifest['members']['owner'])) {
                if ($manifest['members']['owner']['username'] === $owner) {
                    return true;
                }

                throw new \Exception('The user ' . $owner . ' was not found');
            }
        }

        //throws no result exception
        $this->om->getRepository('Claroline\CoreBundle\Entity\User')->findOneByUsername($owner);

        return true;
    }
}