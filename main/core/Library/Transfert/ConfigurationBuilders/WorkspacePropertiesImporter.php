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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @DI\Service("claroline.importer.properties_importer")
 * @DI\Tag("claroline.importer")
 */
class WorkspacePropertiesImporter extends Importer implements ConfigurationInterface
{
    private $om;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function getConfigTreeBuilder()
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
                ->scalarNode('name')->example('ANGLAIS 01')->isRequired()->end()
                ->scalarNode('code')->example('AN01')->isRequired()->end()
                ->booleanNode('visible')->defaultTrue()->example('true')->isRequired()->end()
                ->booleanNode('self_registration')->defaultFalse()->example('true')->isRequired()->end()
                ->booleanNode('self_unregistration')->defaultFalse()->example('true')->isRequired()->end()
                ->scalarNode('owner')->info('The workspace owner username')->example('jdoe')->end()
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
     * @param array $data
     */
    public function validate(array $data)
    {
        $processor = new Processor();
        $configuration = $processor->processConfiguration($this, $data);
        $this->validateOwner($configuration['owner']);
        $this->validateCode($configuration['code']);
    }

    public function validateCode($code)
    {
        $ws = $this->om->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findByCode($code);

        if ($ws !== []) {
            throw new \Exception('The code '.$code.' already exists');
        }
    }

    public function validateOwner($owner)
    {
        $manifest = $this->getConfiguration();

        if (isset($manifest['members'])) {
            if (isset($manifest['members']['owner'])) {
                if ($manifest['members']['owner']['username'] === $owner) {
                    return true;
                }

                throw new \Exception('The user '.$owner.' was not found');
            }
        }

        //throws no result exception
        $this->om->getRepository('Claroline\CoreBundle\Entity\User')
            ->findOneByUsername($owner);

        return true;
    }

    public function export($workspace, array &$files, $object)
    {
        return [];
    }
}
