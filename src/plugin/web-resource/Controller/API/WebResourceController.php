<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\WebResourceBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\WebResourceBundle\Manager\WebResourceManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WebResourceController extends AbstractCrudController
{
    private $resourceManager;
    private $webResourceManager;

    public function __construct(
        ResourceManager $resourceManager,
        WebResourceManager $webResourceManager
    ) {
        $this->resourceManager = $resourceManager;
        $this->webResourceManager = $webResourceManager;
    }

    public function getName()
    {
        return 'web-resource';
    }

    /**
     * @Route(
     *    "workspace/{workspace}/webResource/file/upload",
     *    name="apiv2_webresource_file_upload"
     * )
     *
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
     *
     * @return JsonResponse
     */
    public function uploadFile(Workspace $workspace, Request $request)
    {
        $files = $request->files->all();
        $error = null;

        foreach ($files as $file) {
            $isZip = $this->webResourceManager->isZip($file, $workspace);
            if (!$isZip) {
                $error = 'not_valid_file';

                return new JsonResponse($error, 500);
            } else {
                $data = $this->webResourceManager->create($file, $workspace);

                return new JsonResponse($data, 200);
            }
        }
    }
}
