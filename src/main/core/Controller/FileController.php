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
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route(options={"expose"=true})
 *
 * @todo merge with APINew\FileController
 */
class FileController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    /** @var SessionInterface */
    private $session;
    /** @var ObjectManager */
    private $om;
    /** @var string */
    private $fileDir;
    /** @var ResourceNodeSerializer */
    private $serializer;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var RoleManager */
    private $roleManager;
    /** @var FileUtilities */
    private $fileUtils;
    /** @var FinderProvider */
    private $finder;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        SessionInterface $session,
        ObjectManager $om,
        string $fileDir,
        ResourceNodeSerializer $serializer,
        ResourceManager $resourceManager,
        RoleManager $roleManager,
        FileUtilities $fileUtils,
        FinderProvider $finder,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
        $this->om = $om;
        $this->fileDir = $fileDir;
        $this->serializer = $serializer;
        $this->resourceManager = $resourceManager;
        $this->roleManager = $roleManager;
        $this->fileUtils = $fileUtils;
        $this->finder = $finder;
        $this->authorization = $authorization;
    }

    /**
     * @Route("/stream/{id}", name="claro_file_stream", methods={"GET"})
     *
     * @return BinaryFileResponse
     */
    public function streamAction(ResourceNode $resourceNode)
    {
        return $this->stream($resourceNode);
    }

    /**
     * @Route("/resource/media/{node}", name="claro_file_get_media", methods={"GET"})
     *
     * @return Response
     *
     * @deprecated for retro compatibility with old tinymce embedded resources
     */
    public function streamMediaAction(ResourceNode $node)
    {
        return $this->stream($node);
    }

    /**
     * @Route("/tinymce/destinations/{workspace}", name="claro_tinymce_file_destinations", defaults={"workspace"=null}, methods={"GET"})
     *
     * @return JsonResponse
     */
    public function listTinyMceDestinationsAction(Workspace $workspace = null)
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
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function uploadTinyMceAction(Request $request, User $user)
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
     * @return JsonResponse
     *
     * @deprecated only used in quiz content items. Use new file upload route instead.
     */
    public function fileSaveAction(Request $request)
    {
        $url = null;
        $fileName = $request->get('fileName');
        $objectClass = $request->get('objectClass');
        $objectUuid = $request->get('objectUuid');
        $objectName = $request->get('objectName');
        $sourceType = $request->get('sourceType');

        if ($request->files->get('file')) {
            $publicFile = $this->fileUtils->createFile(
                $request->files->get('file'),
                $fileName,
                $objectClass,
                $objectUuid,
                $objectName,
                $sourceType
            );
            $url = $publicFile->getUrl();
        }

        return new JsonResponse($url, 200);
    }

    /**
     * Streams a resource file to the user browser.
     *
     * @return BinaryFileResponse|JsonResponse
     */
    private function stream(ResourceNode $resourceNode)
    {
        //temporary because otherwise injected resource must have the "open" right
        $this->checkPermission('OPEN', $resourceNode, [], true);

        // free the session as soon as possible
        // see https://github.com/claroline/CoreBundle/commit/7cee6de85bbc9448f86eb98af2abb1cb072c7b6b
        $this->session->save();

        /** @var File $file */
        $file = $this->resourceManager->getResourceFromNode($resourceNode);
        $path = $this->fileDir.DIRECTORY_SEPARATOR.$file->getHashName();

        if (!file_exists($path)) {
            return new JsonResponse(['File not found'], 500);
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
