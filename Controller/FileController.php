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

use Claroline\CoreBundle\Form\FileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Form\UpdateFileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class FileController extends Controller
{
    /**
     * @EXT\Route(
     *     "resource/media/{node}",
     *     name="claro_file_get_media",
     *     options={"expose"=true}
     * )
     *
     * @param integer $id
     *
     * @return Response
     */
    public function streamMediaAction(ResourceNode $node)
    {
        $collection = new ResourceCollection(array($node));
        $this->checkAccess('OPEN', $collection);
        $file = $this->get('claroline.manager.resource_manager')->getResourceFromNode($node);
        $path = $this->container->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR
            . $file->getHashName();

        $response = new StreamedResponse();
        $response->setCallBack(
            function () use ($path) {
                readfile($path);
            }
        );

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
     * @param integer $parentId the parent id
     *
     * @throws \Exception
     * @return Response
     */
    public function uploadWithAjaxAction(ResourceNode $parent, User $user)
    {
        $collection = new ResourceCollection(array($parent));
        $collection->setAttributes(array('type' => 'file'));
        $this->checkAccess('CREATE', $collection);
        $file = new File();
        $request = $this->getRequest();
        $fileName = $request->get('fileName');
        $tmpFile = $request->files->get('file');
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $size = filesize($tmpFile);
        $mimeType = $tmpFile->getClientMimeType();
        $hashName = $parent->getWorkspace()->getCode() .
            DIRECTORY_SEPARATOR .
            $this->container->get('claroline.utilities.misc')->generateGuid() .
            '.' .
            $extension;
        $destination = $this->container->getParameter('claroline.param.files_directory') .
            DIRECTORY_SEPARATOR .
            $parent->getWorkspace()->getCode();
        $tmpFile->move($destination, $hashName);
        $file->setSize($size);
        $file->setName($fileName);
        $file->setHashName($hashName);
        $file->setMimeType($mimeType);
        $manager = $this->get('claroline.manager.resource_manager');
        $file = $manager->create(
            $file,
            $manager->getResourceTypeByName('file'),
            $user,
            $parent->getWorkspace(),
            $parent
        );

        return new JsonResponse(
            array($manager->toArray($file->getResourceNode(), $this->get('security.context')->getToken()))
        );
    }

    /**
     * @EXT\Route("uploadmodal", name="claro_upload_modal", options = {"expose" = true})
     *
     * @EXT\Template("ClarolineCoreBundle:Resource:uploadModal.html.twig")
     */
    public function uploadModalAction()
    {
        return array(
            'form' => $this->get('form.factory')->create(new FileType())->createView(),
            'workspace' => $this->get('claroline.manager.resource_manager')->getWorkspaceRoot(
                $this->getCurrentUser()->getPersonalWorkspace()
            )->getId()
        );
    }

    /**
     * @EXT\Route("/update/{file}/form", name="update_file_form", options = {"expose" = true})
     *
     * @EXT\Template()
     */
    public function updateFileFormAction(File $file)
    {
        $collection = new ResourceCollection(array($file->getResourceNode()));
        $this->checkAccess('EDIT', $collection);
        $form = $this->get('form.factory')->create(new UpdateFileType(), new File());

        return array(
            'form' => $form->createView(),
            'resourceType' => 'file',
            'file' => $file,
            '_resource' => $file
        );
    }

    /**
     * @EXT\Route("/update/{file}", name="update_file", options = {"expose" = true})
     *
     * @EXT\Template("ClarolineCoreBundle:File:updateFileForm.html.twig")
     */
    public function updateFileAction(File $file)
    {
        $collection = new ResourceCollection(array($file->getResourceNode()));
        $this->checkAccess('EDIT', $collection);
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new UpdateFileType(), new File());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $tmpFile = $form->get('file')->getData();
            $this->get('claroline.manager.file_manager')->changeFile($file, $tmpFile);

            if ($this->get('claroline.twig.home_extension')->isDesktop()) {
                $url = $this->generateUrl('claro_desktop_open_tool', array('toolName' => 'resource_manager'));
            } else {
                $url = $this->generateUrl(
                    'claro_workspace_open_tool',
                    array(
                        'toolName' => 'resource_manager',
                        'workspaceId' => $file->getResourceNode()->getWorkspace()->getId()
                    )
                );
            }

            return $this->redirect($url);
        }

        return array(
            'form' => $form->createView(),
            'resourceType' => 'file',
            'file' => $file,
            '_resource' => $file
        );
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
        if (!$this->get('security.context')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    /**
     * Get Current User
     *
     * @return mixed Claroline\CoreBundle\Entity\User or null
     */
    private function getCurrentUser()
    {
        if (is_object($token = $this->get('security.context')->getToken()) and is_object($user = $token->getUser())) {
            return $user;
        }
    }
}
