<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\WebResourceBundle\Controller;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\WebResourceBundle\Manager\WebResourceManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WebResourceController
{
    public function __construct(
        private readonly WebResourceManager $webResourceManager
    ) {
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
     */
    public function uploadFile(Workspace $workspace, Request $request): JsonResponse
    {
        $files = $request->files->all();

        foreach ($files as $file) {
            $isZip = $this->webResourceManager->isZip($file, $workspace);
            if (!$isZip) {
                $error = 'not_valid_file';

                return new JsonResponse($error, 422);
            } else {
                $data = $this->webResourceManager->create($file, $workspace);

                return new JsonResponse($data, 200);
            }
        }

        return new JsonResponse();
    }
}
