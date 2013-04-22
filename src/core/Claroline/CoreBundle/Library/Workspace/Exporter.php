<?php

namespace Claroline\CoreBundle\Library\Workspace;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Event\ExportToolEvent;
use Symfony\Component\Yaml\Yaml;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.workspace.exporter")
 */
class Exporter
{
    private $em;
    private $ed;
    private $templateDir;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "ed" = @DI\Inject("event_dispatcher"),
     *     "templateDir" = @DI\Inject("%claroline.param.templates_directory%")
     * })
     */
    public function __construct(EntityManager $em, $ed, $templateDir)
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->templateDir = $templateDir;
    }

    public function export(AbstractWorkspace $workspace, $configName)
    {
        if (!is_writable($this->templateDir)) {
            throw new \Exception("{$this->templateDir} is not writable");
        }

        $archive = new \ZipArchive();
        $pathArch = $this->templateDir."{$configName}.zip";
        $archive->open($pathArch, \ZipArchive::CREATE);
        $arTools = array();
        $description = array();
        $roleRepo = $this->em->getRepository('ClarolineCoreBundle:Role');
        $workspaceTools = $this->em
            ->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findBy(array('workspace' => $workspace));
        $roles = $roleRepo->findByWorkspace($workspace);
        $root = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findWorkspaceRoot($workspace);

        foreach ($roles as $role) {
            $name = rtrim(str_replace(range(0, 9), '', $role->getName()), '_');
            $arRole[$name] = $role->getTranslationKey();
        }

        foreach ($roles as $role) {
            $perms = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                ->findMaximumRights(array($role->getName()), $root);
            $perms['canCreate'] = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                ->findCreationRights(array($role->getName()), $root);

            $description['root_perms'][rtrim(str_replace(range(0, 9), '', $role->getName()), '_')] = $perms;
        }

        foreach ($workspaceTools as $workspaceTool) {

            $tool = $workspaceTool->getTool();
            $roles = $roleRepo->findByWorkspaceAndTool($workspace, $tool);
            $arToolRoles = array();

            foreach ($roles as $role) {
                $arToolRoles[] = rtrim(str_replace(range(0, 9), '', $role->getName()), '_');
            }

            $arTools[$tool->getName()]['perms'] = $arToolRoles;
            $arTools[$tool->getName()]['name'] = $workspaceTool->getName();

            if ($workspaceTool->getTool()->isExportable()) {
                $event = new ExportToolEvent($workspace);
                $this->ed->dispatch('tool_'.$tool->getName().'_to_template', $event);

                if ($event->getConfig() === null) {
                    throw new \Exception(
                        'The event tool_' . $tool->getName() .
                        '_to_template did not return any config.'
                    );
                }

                $description['tools'][$tool->getName()] = $event->getConfig();
                $description['tools'][$tool->getName()]['files'] = $event->getFilenamesFromArchive();

                foreach ($event->getFiles() as $file) {
                    $archive->addFile($file['original_path'], $file['archive_path']);
                }
            }
        }

        $description['roles'] = $arRole;
        $description['creator_role'] = 'ROLE_WS_MANAGER';
        $description['tools_infos'] = $arTools;
        $description['name'] = $configName;
        $yaml = Yaml::dump($description, 10);
        $archive->addFromString('config.yml', $yaml);
        $archive->close();
    }
}


