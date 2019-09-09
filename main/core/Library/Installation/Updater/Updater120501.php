<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120501 extends Updater
{
    /** @var ContainerInterface */
    private $container;
    private $conn;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->serializer = $container->get('claroline.api.serializer');
    }

    public function postUpdate()
    {
        $this->updateWorkspaceRedirection();
        $this->removeTool('dashboard');
        $this->addDefaultAdminWidget();
    }

    private function updateWorkspaceRedirection()
    {
        $this->log('Updating workspace redirection');

        $data = [
          'platform_dashboard' => 'dashboard',
          'agenda_' => 'agenda',
          'resource_manager' => 'resources',
          'users' => 'community',
          'user_management' => 'community',
          'data_transfer' => 'transfer',
        ];

        foreach ($data as $old => $new) {
            $sql = "UPDATE claro_workspace_options SET details = REPLACE(details, '$old', '$new')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }
    }

    private function removeTool($toolName)
    {
        $this->log(sprintf('Removing `%s` tool...', $toolName));

        $tool = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneBy(['name' => $toolName]);
        if (!empty($tool)) {
            $this->om->remove($tool);
            $this->om->flush();
        }

        $sql = "DELETE FROM claro_ordered_tool WHERE name = '${toolName}'";

        $this->log($sql);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }

    private function addDefaultAdminWidget()
    {
        $tabs = $this->om->getRepository(HomeTab::class)->findBy(['type' => HomeTab::TYPE_ADMIN]);
        if (!empty($tabs)) {
            $serializedTab = $this->serializer->serialize($tabs[0]);

            if (!isset($serializedTab['widgets'])) {
                $serializedTab['widgets'] = [];
            }

            // adds Admin Tools widget (it's easier to use JSON, to be sure all Entities are correctly created)
            $serializedTab['widgets'][] = [
                'visible' => true,
                'display' => [
                    'layout' => [1],
                    'color' => '#333333',
                    'backgroundType' => 'color',
                    'background' => '#ffffff',
                ],
                'parameters' => [],
                'contents' => [[
                    'type' => 'list',
                    'source' => 'admin_tools',
                    'parameters' => [
                        'showResourceHeader' => false,
                        'display' => 'tiles-sm',
                        'enableDisplays' => false,
                        'availableDisplays' => [],
                        'card' => [
                            'display' => ['icon', 'flags', 'subtitle']
                        ],
                        'paginated' => false,
                        'count' => false,
                    ],
                ]],
            ];

            $tab = $this->serializer->deserialize($serializedTab, $tabs[0]);

            $this->om->persist($tab);
            $this->om->flush();
        }
    }
}
