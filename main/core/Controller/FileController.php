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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractApiController;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Library\Utilities\MimeTypeGuesser;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @EXT\Route(options={"expose"=true})
 */
class FileController extends AbstractApiController
{
    use PermissionCheckerTrait;

    /** @var SessionInterface */
    private $session;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var string */
    private $fileDir;
    /** @var SerializerProvider */
    private $serializer;
    /** @var FileManager */
    private $fileManager;
    /** @var MimeTypeGuesser */
    private $mimeTypeGuesser;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var RoleManager */
    private $roleManager;
    /** @var FileUtilities */
    private $fileUtils;

    /**
     * FileController constructor.
     *
     * @DI\InjectParams({
     *     "session"         = @DI\Inject("session"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "fileDir"         = @DI\Inject("%claroline.param.files_directory%"),
     *     "serializer"      = @DI\Inject("claroline.api.serializer"),
     *     "fileManager"     = @DI\Inject("claroline.manager.file_manager"),
     *     "mimeTypeGuesser" = @DI\Inject("claroline.utilities.mime_type_guesser"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "fileUtils"       = @DI\Inject("claroline.utilities.file")
     * })
     *
     * @param SessionInterface      $session
     * @param TokenStorageInterface $tokenStorage
     * @param ObjectManager         $om
     * @param string                $fileDir
     * @param SerializerProvider    $serializer
     * @param FileManager           $fileManager
     * @param MimeTypeGuesser       $mimeTypeGuesser
     * @param ResourceManager       $resourceManager
     * @param RoleManager           $roleManager
     * @param FileUtilities         $fileUtils
     */
    public function __construct(
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        $fileDir,
        SerializerProvider $serializer,
        FileManager $fileManager,
        MimeTypeGuesser $mimeTypeGuesser,
        ResourceManager $resourceManager,
        RoleManager $roleManager,
        FileUtilities $fileUtils
    ) {
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->fileDir = $fileDir;
        $this->serializer = $serializer;
        $this->fileManager = $fileManager;
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->resourceManager = $resourceManager;
        $this->roleManager = $roleManager;
        $this->fileUtils = $fileUtils;
    }

    /**
     * @EXT\Route("/stream/{id}", name="claro_file_stream")
     * @EXT\Method("GET")
     *
     * @param ResourceNode $resourceNode
     *
     * @return BinaryFileResponse
     */
    public function streamAction(ResourceNode $resourceNode)
    {
        return $this->stream($resourceNode);
    }

    /**
     * @EXT\Route("/resource/media/{node}", name="claro_file_get_media")
     * @EXT\Method("GET")
     *
     * @param ResourceNode $node
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
     * @EXT\Route("/tinymce/destinations", name="claro_tinymce_file_destinations")
     * @EXT\Method("GET")
     *
     * @return JsonResponse
     */
    public function listTinyMceDestinationsAction()
    {
        return new JsonResponse(array_map(function (Directory $directory) {
            return $this->serializer->serialize($directory->getResourceNode(), [Options::SERIALIZE_MINIMAL]);
        }, $this->resourceManager->getDefaultUploadDestinations()));
    }

    /**
     * Creates a resource from uploaded file.
     *
     * @EXT\Route("/tinymce/upload", name="claro_tinymce_file_upload")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Method("POST")
     *
     * @param Request $request
     * @param User    $user
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

        $file = $this->resourceManager->create(
            $file,
            $this->resourceManager->getResourceTypeByName('file'),
            $user,
            $parent->getWorkspace(),
            $parent,
            null,
            $rights,
            true
        );

        return new JsonResponse($this->serializer->serialize($file->getResourceNode(), [Options::SERIALIZE_MINIMAL]), 201);
    }

    /**
     * Saves a file.
     *
     * @EXT\Route("/public/upload", name="upload_public_file")
     * @EXT\Method("POST")
     *
     * @param Request $request
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
     * @param ResourceNode $resourceNode
     *
     * @return BinaryFileResponse
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

        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $resourceNode->getMimeType());

        return $response;
    }
}
