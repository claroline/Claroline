<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Resource\Types;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/resource_file', name: 'apiv2_resource_file_')]
class FileController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly ResourceManager $resourceManager
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/{file}/raw', name: 'raw', methods: ['GET'])]
    public function displayRawAction(string $file): Response
    {
        $fileResource = $this->om->getRepository(File::class)->findOneBy(['uuid' => $file]);
        if (empty($fileResource)) {
            throw new NotFoundHttpException('File not found');
        }

        $this->checkPermission('OPEN', $fileResource->getResourceNode(), [], true);

        $file = $this->resourceManager->download([$fileResource->getResourceNode()]);
        if (empty($file)) {
            return new JsonResponse('File not found.', 500);
        }

        return new BinaryFileResponse($file['path'], 200, [
            'Content-Disposition' => "inline; filename={$file['filename']}",
        ]);
    }
}
