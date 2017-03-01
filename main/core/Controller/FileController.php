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

use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\TinyMceUploadModalType;
use Claroline\CoreBundle\Form\UpdateFileType;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Library\Utilities\MimeTypeGuesser;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Twig\HomeExtension;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class FileController extends Controller
{
    private $authorization;
    private $fileDir;
    private $fileManager;
    private $formFactory;
    private $homeExtension;
    private $mimeTypeGuesser;
    private $request;
    private $resourceManager;
    private $roleManager;
    private $session;
    private $tokenStorage;
    private $translator;
    private $ut;
    private $fileUtils;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "fileDir"         = @DI\Inject("%claroline.param.files_directory%"),
     *     "fileManager"     = @DI\Inject("claroline.manager.file_manager"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "homeExtension"   = @DI\Inject("claroline.twig.home_extension"),
     *     "mimeTypeGuesser" = @DI\Inject("claroline.utilities.mime_type_guesser"),
     *     "request"         = @DI\Inject("request"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "session"         = @DI\Inject("session"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "translator"      = @DI\Inject("translator"),
     *     "ut"              = @DI\Inject("claroline.utilities.misc"),
     *     "fileUtils"       = @DI\Inject("claroline.utilities.file")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        $fileDir,
        FileManager $fileManager,
        FormFactoryInterface $formFactory,
        HomeExtension $homeExtension,
        MimeTypeGuesser $mimeTypeGuesser,
        Request $request,
        ResourceManager $resourceManager,
        RoleManager $roleManager,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        ClaroUtilities $ut,
        FileUtilities $fileUtils
    ) {
        $this->authorization = $authorization;
        $this->fileDir = $fileDir;
        $this->fileManager = $fileManager;
        $this->formFactory = $formFactory;
        $this->homeExtension = $homeExtension;
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->request = $request;
        $this->resourceManager = $resourceManager;
        $this->roleManager = $roleManager;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->ut = $ut;
        $this->fileUtils = $fileUtils;
    }

    /**
     * @EXT\Route(
     *     "resource/media/{node}",
     *     name="claro_file_get_media",
     *     options={"expose"=true}
     * )
     *
     * @param int $id
     *
     * @return Response
     */
    public function streamMediaAction(ResourceNode $node)
    {
        $collection = new ResourceCollection([$node]);
        $this->checkAccess('OPEN', $collection);

        // free the session as soon as possible
        // see https://github.com/claroline/CoreBundle/commit/7cee6de85bbc9448f86eb98af2abb1cb072c7b6b
        $this->session->save();
        $file = $this->resourceManager->getResourceFromNode($node);
        $path = $this->fileDir.DIRECTORY_SEPARATOR.$file->getHashName();
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $node->getMimeType());

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/upload/{parent}",
     *     name="claro_file_upload_with_ajax",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Creates a resource from uploaded file.
     *
     * @param int $parentId the parent id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function uploadWithAjaxAction(ResourceNode $parent, User $user)
    {
        $parent = $this->resourceManager->getById($parent);
        $collection = new ResourceCollection([$parent]);
        $collection->setAttributes(['type' => 'file']);
        $this->checkAccess('CREATE', $collection);
        $file = new File();
        $fileName = $this->request->get('fileName');
        $tmpFile = $this->request->files->get('file');
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $size = filesize($tmpFile);
        $mimeType = $tmpFile->getClientMimeType();
        $hashName = 'WORKSPACE_'.
            $parent->getWorkspace()->getId().
            DIRECTORY_SEPARATOR.
            $this->ut->generateGuid().
            '.'.
            $extension;
        $destination = $this->fileDir.
            DIRECTORY_SEPARATOR.
            'WORKSPACE_'.
            $parent->getWorkspace()->getId();
        $tmpFile->move($destination, $hashName);
        $file->setSize($size);
        $file->setName($fileName);
        $file->setHashName($hashName);
        $file->setMimeType($mimeType);
        $file = $this->resourceManager->create(
            $file,
            $this->resourceManager->getResourceTypeByName('file'),
            $user,
            $parent->getWorkspace(),
            $parent
        );

        return new JsonResponse(
            [$this->resourceManager->toArray($file->getResourceNode(), $this->tokenStorage->getToken())]
        );
    }

    /**
     * @EXT\Route(
     *     "/tinymce/upload/{parent}",
     *     name="claro_file_upload_with_tinymce",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Creates a resource from uploaded file.
     *
     * @param int $parentId the parent id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function uploadWithTinyMceAction($parent, User $user)
    {
        $parent = $this->resourceManager->getById($parent);
        $workspace = $parent ? $parent->getWorkspace() : null;
        $collection = new ResourceCollection([$parent]);
        $collection->setAttributes(['type' => 'file']);
        $this->checkAccess('CREATE', $collection);

        if (!$this->authorization->isGranted('CREATE', $collection)) {
            //use different header so we know something went wrong
            $content = $this->translator->trans(
                'resource_creation_denied',
                ['%path%' => $parent->getPathForDisplay()],
                'platform'
            );
            $response = new Response($content, 403);
            $response->headers->add(['XXX-Claroline' => 'resource-error']);

            return $response;
        }

        //let's create the file !
        $fileForm = $this->request->files->get('file_form');
        $file = $fileForm['file'];
        $ext = strtolower($file->getClientOriginalExtension());
        $mimeType = $this->mimeTypeGuesser->guess($ext);
        $file = $this->fileManager->create(
            new File(),
            $file,
            $file->getClientOriginalName(),
            $mimeType,
            $workspace
        );

        if ($workspace) {
            $rights = [];
        } else {
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
            $workspace,
            $parent,
            null,
            $rights,
            true
        );

        $nodesArray[0] = $this->resourceManager->toArray(
            $file->getResourceNode(), $this->tokenStorage->getToken()
        );

        return new JsonResponse($nodesArray);
    }

    /**
     * @EXT\Route("uploadmodal", name="claro_upload_modal", options = {"expose" = true})
     *
     * @EXT\Template("ClarolineCoreBundle:Resource:uploadModal.html.twig")
     */
    public function uploadModalAction()
    {
        $destinations = $this->resourceManager->getDefaultUploadDestinations();

        return [
            'form' => $this->formFactory->create(new TinyMceUploadModalType($destinations))->createView(),
        ];
    }

    /**
     * @EXT\Route("/update/{file}/form", name="update_file_form", options = {"expose" = true})
     *
     * @EXT\Template()
     */
    public function updateFileFormAction(File $file)
    {
        $collection = new ResourceCollection([$file->getResourceNode()]);
        $this->checkAccess('EDIT', $collection);
        $form = $this->formFactory->create(new UpdateFileType(), new File());

        return [
            'form' => $form->createView(),
            'resourceType' => 'file',
            'file' => $file,
            '_resource' => $file,
        ];
    }

    /**
     * @EXT\Route("/update/{file}", name="update_file", options = {"expose" = true})
     *
     * @EXT\Template("ClarolineCoreBundle:File:updateFileForm.html.twig")
     */
    public function updateFileAction(File $file)
    {
        $collection = new ResourceCollection([$file->getResourceNode()]);
        $this->checkAccess('EDIT', $collection);
        $form = $this->formFactory->create(new UpdateFileType(), new File());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $tmpFile = $form->get('file')->getData();
            $this->fileManager->changeFile($file, $tmpFile);

            if ($this->homeExtension->isDesktop()) {
                $url = $this->generateUrl('claro_desktop_open_tool', ['toolName' => 'resource_manager']);
            } else {
                $url = $this->generateUrl(
                    'claro_workspace_open_tool',
                    [
                        'toolName' => 'resource_manager',
                        'workspaceId' => $file->getResourceNode()->getWorkspace()->getId(),
                    ]
                );
            }

            return $this->redirect($url);
        }

        return [
            'form' => $form->createView(),
            'resourceType' => 'file',
            'file' => $file,
            '_resource' => $file,
        ];
    }

    /**
     * Saves a file.
     *
     * @EXT\Route(
     *     "/public/file/upload",
     *     name="upload_public_file",
     *     options = {"expose" = true}
     * )
     * @EXT\Method("POST")
     *
     * @return JsonResponse
     */
    public function fileSaveAction()
    {
        $url = null;
        $fileName = $this->request->get('fileName');
        $objectClass = $this->request->get('objectClass');
        $objectUuid = $this->request->get('objectUuid');
        $objectName = $this->request->get('objectName');
        $sourceType = $this->request->get('sourceType');

        if ($this->request->files->get('file')) {
            $publicFile = $this->fileUtils->createFile(
                $this->request->files->get('file'),
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
     * Checks if the current user has the right to perform an action on a ResourceCollection.
     * Be careful, ResourceCollection may need some aditionnal parameters.
     *
     * - for CREATE: $collection->setAttributes(array('type' => $resourceType))
     *  where $resourceType is the name of the resource type.
     * - for MOVE / COPY $collection->setAttributes(array('parent' => $parent))
     *  where $parent is the new parent entity.
     *
     * @param string             $permission
     * @param ResourceCollection $collection
     *
     * @throws AccessDeniedException
     */
    private function checkAccess($permission, ResourceCollection $collection)
    {
        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
