<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Resource\Types;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("resource_file")
 */
class FileController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    private $authorization;
    private $manager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ResourceManager $resourceManager
    ) {
        $this->authorization = $authorization;
        $this->manager = $resourceManager;
    }

    public function getClass()
    {
        return File::class;
    }

    public function getIgnore()
    {
        return ['create', 'exist', 'list', 'copyBulk', 'deleteBulk', 'schema', 'find', 'get'];
    }

    public function getName()
    {
        return 'file';
    }

    /**
     * @Route("{file}/raw", name="claro_resource_file_raw")
     */
    public function displayRawAction(string $file)
    {
        $fileResource = $this->om->getRepository(File::class)->findOneBy(['uuid' => $file]);
        if (empty($fileResource)) {
            throw new NotFoundHttpException('File not found');
        }

        $this->checkPermission('OPEN', $fileResource->getResourceNode(), [], true);

        $data = $this->manager->download([$fileResource->getResourceNode()], false);

        $file = $data['file'] ?: tempnam('tmp', 'tmp');
        if (!file_exists($file)) {
            return new JsonResponse(['file_not_found'], 500);
        }

        return new BinaryFileResponse($file, 200, [
            'Content-Disposition' => 'inline',
        ]);
    }
}
