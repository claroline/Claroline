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

use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\ToolRightsManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * @DI\Service("claroline.importer.tools_importer")
 * @DI\Tag("claroline.importer")
 */
class ToolsImporter extends Importer implements ConfigurationInterface
{
    private $toolManager;
    private $toolRightManager;
    private $roleManager;
    private $om;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "toolManager"      = @DI\Inject("claroline.manager.tool_manager"),
     *     "toolRightManager" = @DI\Inject("claroline.manager.tool_rights_manager"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "om"               = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ToolManager $toolManager,
        ToolRightsManager $toolRightManager,
        RoleManager $roleManager,
        ObjectManager $om
    ) {
        $this->toolManager = $toolManager;
        $this->toolRightManager = $toolRightManager;
        $this->roleManager = $roleManager;
        $this->om = $om;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tools');
        $this->addToolsSection($rootNode);

        return $treeBuilder;
    }

    public function addToolsSection($rootNode)
    {
        $configuration = $this->getConfiguration();
        $availableRoleName = [];

        if (isset($configuration['roles'])) {
            foreach ($configuration['roles'] as $role) {
                $availableRoleName[] = $role['role']['name'];
            }
        }

        $availableRoleName[] = 'ROLE_ANONYMOUS';
        $availableRoleName[] = 'ROLE_USER';

        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('tool')
                        ->children()
                            ->scalarNode('type')->info('The tool type')->example('home')->isRequired()->end()
                            ->scalarNode('translation')->info('The displayed tool name')->example('accueil')->isRequired()->end()
                            ->variableNode('data')->info('The data needed to import the tool')->end()
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
                                        ->scalarNode('name')->info('An existing role name')->example('ROLE_01')->isRequired()
                                            ->validate()
                                                ->ifTrue(
                                                    function ($v) use ($availableRoleName) {
                                                        return call_user_func_array(
                                                            __CLASS__.'::roleNameExists',
                                                            [$v, $availableRoleName]
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
     *
     * @throws InvalidConfigurationException
     */
    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);

        foreach ($data['tools'] as &$tool) {
            $importer = $this->getImporterByName($tool['tool']['type']);

            if (!$importer && isset($tool['tool']['data'])) {
                throw new InvalidConfigurationException('The importer '.$tool['tool']['type'].' does not exist');
            }

            if (isset($tool['tool']['data'])) {
                $array['data'] = $tool['tool']['data'];
                $tool['tool']['data'] = $importer->validate($array);
            }
        }

        return $data;
    }

    public function import(array $tools, Workspace $workspace, array $entityRoles, Directory $root)
    {
        $position = 1;

        foreach ($tools as $tool) {
            $otr = null;
            $toolEntity = $this->om->getRepository('Claroline\CoreBundle\Entity\Tool\Tool')
                ->findOneByName($tool['tool']['type']);

            //check the tool already exists in the workspace~
            $orderedTool = $this->om->getRepository('Claroline\CoreBundle\Entity\Tool\OrderedTool')
                ->findBy(['tool' => $toolEntity, 'workspace' => $workspace]);

            $addRoleToOtr = count($orderedTool) > 0 ? false : true;

            if ($addRoleToOtr && $toolEntity) {
                $otr = $this->toolManager
                    ->setWorkspaceTool(
                        $toolEntity,
                        $position,
                        $tool['tool']['translation'],
                        $workspace
                    );

                ++$position;
            }

            if (isset($tool['tool']['roles']) && $addRoleToOtr && $otr) {
                foreach ($tool['tool']['roles'] as $role) {
                    $this->toolRightManager->setToolRights(
                        $otr,
                        $entityRoles[$role['name']],
                        ToolMaskDecoder::$defaultValues['open']
                    );
                }
            }

            $importer = $this->getImporterByName($tool['tool']['type']);

            if (isset($tool['tool']['data'])) {
                $data['data'] = $tool['tool']['data'];
                $importer->import($data, $workspace, $entityRoles, $root);
            }
        }
    }

    public function getName()
    {
        return 'tools';
    }

    public function export(Workspace $workspace, array &$files, $object)
    {
        $data = [];
        $workspaceTools = $workspace->getOrderedTools();
        $i = 0;

        foreach ($workspaceTools as $workspaceTool) {
            $roles = [];
            foreach ($workspaceTool->getRights() as $right) {
                $roles[] = ['name' => $this->roleManager->getWorkspaceRoleBaseName($right->getRole())];
            }

            $tool = [
                'type' => $workspaceTool->getTool()->getName(),
                'translation' => $workspaceTool->getTool()->getDisplayName(),
                'roles' => $roles,
            ];

            $importer = $this->getImporterByName($workspaceTool->getTool()->getName());

            if ($importer) {
                $tool['data'] = $importer->export($workspace, $files, null);
            }

            $data[$i] = ['tool' => $tool];
            ++$i;
        }

        return $data;
    }

    public static function roleNameExists($v, $roles)
    {
        return !in_array($v, $roles);
    }
}
