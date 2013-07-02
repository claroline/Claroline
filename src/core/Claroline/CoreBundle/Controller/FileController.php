<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;

class FileController extends Controller
{
	/**
     * @Route(
     *     "resource/img/{imageId}",
     *     name="claro_file_get_image",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * @param integer $id
     *
     * @return Response
     */
    public function getImg($imageId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $file = $em->getRepository('ClarolineCoreBundle:Resource\File')->find($imageId);
        $imgpath = $this->container->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR
            . $file->getHashName();

        $response = new StreamedResponse();
        $response->setCallBack(
            function () use ($imgpath) {
                readfile($imgpath);
            }
        );

        $response->headers->set('Content-Type', $file->getMimeType());

        return $response;
    }

    /**
     * @Route(
     *     "/upload/{parentId}",
     *     name="claro_file_upload_with_ajax",
     *     options={"expose"=true}
     * )
     *
     * Creates a resource from uploaded file.
     *
     * @param integer $parentId the parent id
     *
     * @throws \Exception
     * @return Response
     */
    public function uploadWithAjaxAction($parentId)
    {
        $parent = $this->getDoctrine()
            ->getManager()
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($parentId);
        $collection = new ResourceCollection(array($parent));
        $collection->setAttributes(array('type' => 'file'));
        $this->checkAccess('CREATE', $collection);

        $response = new Response();

        $file = new File();

        $request = $this->getRequest();
        $fileName = $request->get('fileName');
        $tmpFile = $request->files->get('file');
        //$fileName = $tmpFile->getClientOriginalName();

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        $size = filesize($tmpFile);
        $mimeType = $tmpFile->getClientMimeType();
        $hashName = $this->container->get('claroline.resource.utilities')->generateGuid() . "." . $extension;
        $tmpFile->move($this->container->getParameter('claroline.param.files_directory'), $hashName);
        $ds = DIRECTORY_SEPARATOR;

        $file->setSize($size);
        $file->setName($fileName);
        $file->setHashName($hashName);
        $file->setMimeType($mimeType);

        $manager = $this->get('claroline.resource.manager');
        $file = $manager->create($file, $parentId, 'file');
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(
            $this->get('claroline.resource.converter')
                ->toJson($file, $this->get('security.context')->getToken())
        );

        return $response;
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
     * @param string $permission
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
}
