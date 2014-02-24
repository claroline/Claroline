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
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;

class RolesImporter extends Importer implements ConfigurationInterface
{
    private static $data;
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

    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('users');
        $this->addRolesSection($rootNode);

        return $treeBuilder;
    }

    public function addRolesSection($rootNode)
    {
        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('role')
                        ->children()
                            ->scalarNode('name')->isRequired()
                                ->validate()
                                ->ifTrue(
                                    function ($v) {
                                        return call_user_func_array(
                                            __CLASS__ . '::nameAlreadyExists',
                                            array($v)
                                        );
                                    }
                                )
                                ->thenInvalid("The name w/e already exists")
                                ->end()
                            ->end()
                            ->scalarNode('translation')->isRequired()->end()
                            ->booleanNode('is_base_role')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function getName()
    {
        return 'role_importer';
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
        self::setData($data);
        $processor->processConfiguration($this, $data);
    }

    private static function setData($data)
    {
        self::$data = $data;
    }

    private static function getData()
    {
        return self::$data;
    }

    public static function nameAlreadyExists($v)
    {
        $roles = self::getData();

        $found = false;
        foreach ($roles as $el) {
            foreach ($el as $role) {
                if ($role['role']['name'] === $v) {
                    if ($found) {
                        return true;
                    }
                    $found = true;
                }
            }
        }

        return false;
    }
}