<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Claroline\Persitence\ObjectManager;

class PackageController extends FOSRestController
{
    private $dependencyManager;

    /**
     * @DI\InjectParams({
     *     "dependencyManager" = @DI\Inject("claroline.manager.dependency_manager"),
     *     "ch"                = @DI\Inject("claroline.config.platform_config_handler"),
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "fileDir"           = @DI\Inject("%claroline.param.files_directory%")
     * })
     */
    public function __construct(
        DependencyManager $dependencyManager,
        PlatformConfigurationHandler $ch,
        ObjectManager $om,
        $fileDir
    )
    {
        $this->dependencyManager = $dependencyManager;
        $this->ch                = $ch;
        $this->fileDir           = $fileDir;
        $this->om                = $om;
        $this->$fileDir          = $fileDir;
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the package list",
     *     views = {"package"}
     * )
     */
    public function getPackagesAction()
    {
        $this->dependencyManager->getAllInstalled();
    }

    /**
     * @ApiDoc(
     *     description="Returns the platform infos",
     *     views = {"package"}
     * )
     */
    public function getInfos()
    {
        return array(
            'name'              => $this->ch->getParameter('name'),
            'support_email'     => $this->ch->getParameter('support_email'),
            'storage_used'      => $this->getUsedStorage(),
            'resources_created' => $this->om->count('Claroline\CoreBundle\Entity\Resource\ResourceNode'),
            'workspace_created' => $this->om->count('Claroline\CoreBundle\Entity\Workspace\Workspace'),
            'users_enabled'     => $this->om->countAllEnabledUsers(),
            'users_all'         => $this->om->count('Claroline\CoreBundle\Entity\User')
        );
    }

    private function getUsedStorage()
    {
        $size = 0;

        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->fileDir)) as $file){
            $size += $file->getSize();
        }

        return $size;
    }
}
