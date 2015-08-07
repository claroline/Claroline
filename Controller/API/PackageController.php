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
use Claroline\CoreBundle\Persitence\ObjectManager;
use Claroline\CoreBundle\Manager\BundleManager;

class PackageController extends FOSRestController
{
    private $bundleManager;
    private $om;
    private $fileDir;
    private $ch;

    /**
     * @DI\InjectParams({
     *     "bundleManager"     = @DI\Inject("claroline.manager.bundle_manager"),
     *     "ch"                = @DI\Inject("claroline.config.platform_config_handler"),
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "fileDir"           = @DI\Inject("%claroline.param.files_directory%")
     * })
     */
    public function __construct(
        BundleManager $bundleManager,
        PlatformConfigurationHandler $ch,
        $om,
        $fileDir
    )
    {
        $this->bundleManager     = $bundleManager;
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
        return $this->bundleManager->getInstalled();
    }

    /**
     * @ApiDoc(
     *     description="Returns the platform infos",
     *     views = {"package"}
     * )
     */
    public function getInfosAction()
    {
        return array(
            'name'              => $this->ch->getParameter('name'),
            'support_email'     => $this->ch->getParameter('support_email'),
            'storage_used'      => $this->getUsedStorage(),
            'resources_created' => $this->om->count('Claroline\CoreBundle\Entity\Resource\ResourceNode'),
            'workspace_created' => $this->om->count('Claroline\CoreBundle\Entity\Workspace\Workspace'),
            'users_enabled'     => $this->om->getRepository('ClarolineCoreBundle:User')->countAllEnabledUsers(),
            'users_all'         => $this->om->count('Claroline\CoreBundle\Entity\User')
        );
    }

    private function getUsedStorage()
    {
        $size = 0;

        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->fileDir)) as $file){
            $size += $file->getSize();
        }

        return $this->get('claroline.utilities.misc')->formatFileSize($size);
    }
}
