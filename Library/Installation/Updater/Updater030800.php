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

use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater030800
{
    private $container;
    private $maskManager;
    private $om;
    private $orderedToolRepo;
    private $toolRepo;
    private $toolRightsManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->maskManager =
            $container->get('claroline.manager.tool_mask_decoder_manager');
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->orderedToolRepo =
            $this->om->getRepository('ClarolineCoreBundle:Tool\OrderedTool');
        $this->toolRepo = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool');
        $this->toolRightsManager =
            $container->get('claroline.manager.tool_rights_manager');
    }

    public function postUpdate()
    {
        $this->om->startFlushSuite();
        $this->createDefaultToolMaskDecoders();
        $this->updateToolsRights();
        $this->om->endFlushSuite();
    }

    public function createDefaultToolMaskDecoders()
    {
        $this->log('Creating default tool mask decoders...');
        $this->om->startFlushSuite();
        $tools = $this->toolRepo->findToolsDispayableInWorkspace();

        foreach ($tools as $tool) {
            $maskDecoders = $this->maskManager->getMaskDecodersByTool($tool);

            if (count($maskDecoders) === 0) {
                $this->maskManager->createToolMaskDecoder($tool);
            }
        }
        $this->om->endFlushSuite();
    }

    public function updateToolsRights()
    {
        $this->log('Updating tool rights...');
        $this->om->startFlushSuite();
        $orderedTools = $this->orderedToolRepo->findAll();

        foreach ($orderedTools as $orderedTool) {

            foreach ($orderedTool->getRoles() as $role) {
                $toolRights = $this->toolRightsManager
                    ->getRightsByRoleAndOrderedTool($role, $orderedTool);

                if (count($toolRights) === 0) {
                    $this->toolRightsManager->createToolRights(
                        $orderedTool,
                        $role,
                        ToolMaskDecoder::$defaultValues['open']
                    );
                }
            }
        }
        $this->om->endFlushSuite();
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }
}
