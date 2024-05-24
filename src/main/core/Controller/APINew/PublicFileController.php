<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Manages platform uploaded files... sort of.
 *
 * @Route("/public_file")
 */
class PublicFileController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly PlatformConfigurationHandler $config
    ) {
        $this->authorization = $authorization;
    }

    public function getClass(): string
    {
        return PublicFile::class;
    }

    public function getIgnore(): array
    {
        return ['create', 'update', 'list'];
    }

    public function getName(): string
    {
        return 'public_file';
    }

    /**
     * @Route("/upload", name="apiv2_file_upload", options={"method_prefix" = false}, methods={"POST"})
     */
    public function uploadAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $files = $request->files->all();

        $objects = [];
        foreach ($files as $file) {
            if (empty($file->getMimeType()) || (!empty($this->config->getParameter('file_blacklist')) && in_array($file->getMimeType(), $this->config->getParameter('file_blacklist')))) {
                throw new InvalidDataException('Unauthorized file type.');
            }

            $object = $this->crud->create(PublicFile::class, [], ['file' => $file, Crud::THROW_EXCEPTION]);
            $objects[] = $this->serializer->serialize($object);
        }

        return new JsonResponse($objects);
    }

    /**
     * @Route("/upload/image", name="apiv2_image_upload", methods={"POST"})
     */
    public function uploadImageAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $files = $request->files->all();

        $objects = [];
        foreach ($files as $file) {
            if (empty($file->getMimeType()) || (!empty($this->config->getParameter('file_blacklist')) && in_array($file->getMimeType(), $this->config->getParameter('file_blacklist')))) {
                throw new InvalidDataException('Unauthorized file type.');
            }

            if (0 !== strpos($file->getMimeType(), 'image')) {
                throw new InvalidDataException('Invalid image type.');
            }

            $object = $this->crud->create(PublicFile::class, [], ['file' => $file, Crud::THROW_EXCEPTION]);
            $objects[] = $this->serializer->serialize($object);
        }

        return new JsonResponse($objects);
    }
}
