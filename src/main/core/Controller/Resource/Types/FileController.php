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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: 'resource_file', name: 'apiv2_resource_file_')]
class FileController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ResourceManager $resourceManager
    ) {
        $this->authorization = $authorization;
    }

    public static function getClass(): string
    {
        return File::class;
    }

    public function getIgnore(): array
    {
        return ['create', 'list', 'deleteBulk', 'get'];
    }

    public static function getName(): string
    {
        return 'file';
    }

    #[Route(path: '{file}/raw', name: 'apiv2_resource_file_raw')]
    public function displayRawAction(string $file): Response
    {
        $fileResource = $this->om->getRepository(File::class)->findOneBy(['uuid' => $file]);
        if (empty($fileResource)) {
            throw new NotFoundHttpException('File not found');
        }

        $this->checkPermission('OPEN', $fileResource->getResourceNode(), [], true);

        $data = $this->resourceManager->download([$fileResource->getResourceNode()], false);

        $file = $data['file'] ?: tempnam('tmp', 'tmp');
        if (!file_exists($file)) {
            return new JsonResponse('File not found.', 500);
        }

        return new BinaryFileResponse($file, 200, [
            'Content-Disposition' => 'inline',
        ]);
    }
}
