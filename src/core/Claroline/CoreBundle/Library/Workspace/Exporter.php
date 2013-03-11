<?php

namespace Claroline\CoreBundle\Library\Workspace;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Event\ExportWorkspaceEvent;

class Exporter
{
    private $em;

    public function __construct(EntityManager $em, $ed)
    {
        $this->em = $em;
        $this->ed = $ed;
    }


    public function export(AbstractWorkspace $workspace)
    {
        $description = array();
        $roleRepo = $this->em->getRepository('ClarolineCoreBundle:Role');
        $tools = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool')->findByWorkspace($workspace, true);
        $roles = $roleRepo->findByWorkspace($workspace);

        foreach ($roles as $role) {
            $name = rtrim(str_replace(range(0,9),'', $role->getName()), '_');
            $arRole[$name] = $role->getTranslationKey();
        }

        foreach($tools as $tool) {
            $roles = $roleRepo->findByWorkspaceAndTool($workspace, $tool);
            $arToolRoles = array();

            foreach ($roles as $role) {
                $arToolRoles[] = rtrim(str_replace(range(0,9),'', $role->getName()), '_');;
            }

            $arTools[$tool->getName()] = $arToolRoles;

            //each tool can export its own config. No implementation yet.
            $event = new ExportWorkspaceEvent($workspace);
            $this->ed->dispatch('export_workspace_'.$tool->getName(), $event);
            if ($event->getConfig() !== null) {
                $description['tools'][$tool->getName()] = $event->getConfig();
            }
        }

        $description['roles'] = $arRole;
        $description['creator_role'] = 'ROLE_WS_MANAGER';
        $description['tools_permissions'] = $arTools;
        
        return $description;
    }
}


