<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/file")
 */
class FileController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly ResourceNodeSerializer $serializer,
        private readonly ResourceManager $resourceManager,
        private readonly RoleManager $roleManager,
        private readonly FileManager $fileManager,
        private readonly FinderProvider $finder,
        private readonly TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    /**
     * @Route("/stream/{id}", name="claro_file_stream", methods={"GET"})
     */
    public function streamAction(ResourceNode $resourceNode, Request $request): BinaryFileResponse
    {
        return $this->stream($resourceNode, $request);
    }

    /**
     * @Route("/resource/media/{node}", name="claro_file_get_media", methods={"GET"})
     *
     * @deprecated for retro compatibility with old tinymce embedded resources
     */
    public function streamMediaAction(ResourceNode $node, Request $request): BinaryFileResponse
    {
        return $this->stream($node, $request);
    }

    /**
     * @Route("/tinymce/destinations/{workspace}", name="claro_tinymce_file_destinations", defaults={"workspace"=null}, methods={"GET"})
     */
    public function listTinyMceDestinationsAction(Workspace $workspace = null): JsonResponse
    {
        $data = $this->finder->search(
            ResourceNode::class, [
                'filters' => [
                    'meta.uploadDestination' => true,
                    'roles' => $this->tokenStorage->getToken()->getRoleNames(),
                ],
            ],
            [Options::SERIALIZE_MINIMAL]
        );

        return new JsonResponse($data['data']);
    }

    /**
     * Creates a resource from uploaded file.
     *
     * @Route("/tinymce/upload", name="claro_tinymce_file_upload", methods={"POST"})
     *
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function uploadTinyMceAction(Request $request, User $user): JsonResponse
    {
        // grab and validate user submission
        $content = $this->decodeRequest($request);
        if (empty($content) || empty($content['file']) || empty($content['parent'])) {
            $errors = [];
            if (empty($content['parent'])) {
                $errors[] = [
                    'path' => 'parent',
                    'message' => 'This value should not be blank.',
                ];
            }

            if (empty($content['file'])) {
                $errors[] = [
                    'path' => 'file',
                    'message' => 'This value should not be blank.',
                ];
            }

            throw new InvalidDataException('Invalid data.', $errors);
        }

        // check user rights
        $parent = $this->om->getRepository(ResourceNode::class)->findOneBy(['uuid' => $content['parent']]);
        $this->checkPermission('CREATE', new ResourceCollection([$parent], ['type' => 'file']));

        // create the new file resource
        $file = new File();
        $file->setSize($content['file']['size']);
        $file->setName($content['file']['filename']);
        $file->setHashName($content['file']['url']);
        $file->setMimeType($content['file']['mimeType']);

        $rights = [];
        if (!$parent->getWorkspace()) {
            $rights = [
                'ROLE_ANONYMOUS' => [
                    'open' => true, 'export' => true, 'create' => [],
                    'role' => $this->roleManager->getRoleByName('ROLE_ANONYMOUS'),
                ],
            ];
        }

        // TODO : create with crud instead
        $file = $this->resourceManager->create(
            $file,
            $this->resourceManager->getResourceTypeByName('file'),
            $user,
            $parent->getWorkspace(),
            $parent,
            $rights,
            true
        );

        return new JsonResponse($this->serializer->serialize($file->getResourceNode(), [Options::SERIALIZE_MINIMAL]), 201);
    }

    /**
     * Saves a file.
     *
     * @Route("/public/upload", name="upload_public_file", methods={"POST"})
     *
     * @deprecated only used in quiz content items. Use new file upload route instead.
     */
    public function fileSaveAction(Request $request): JsonResponse
    {
        $url = null;
        $fileName = $request->get('fileName');
        $objectClass = $request->get('objectClass');
        $objectUuid = $request->get('objectUuid');
        $objectName = $request->get('objectName');

        if ($request->files->get('file')) {
            $publicFile = $this->fileManager->createFile(
                $request->files->get('file'),
                $fileName,
                $objectClass,
                $objectUuid,
                $objectName
            );
            $url = $publicFile->getUrl();
        }

        return new JsonResponse($url, 200);
    }

    /**
     * Streams a resource file to the user browser.
     */
    private function stream(ResourceNode $resourceNode, Request $request): BinaryFileResponse
    {
        // temporary because otherwise injected resource must have the "open" right
        $this->checkPermission('OPEN', $resourceNode, [], true);

        // free the session as soon as possible
        // see https://github.com/claroline/CoreBundle/commit/7cee6de85bbc9448f86eb98af2abb1cb072c7b6b
        $request->getSession()->save();

        /** @var File $file */
        $file = $this->resourceManager->getResourceFromNode($resourceNode);
        $path = $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$file->getHashName();

        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found');
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $resourceNode->getMimeType());
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            TextNormalizer::toKey($resourceNode->getName()).'.'.$extension
        );

        return $response;
    }
}
