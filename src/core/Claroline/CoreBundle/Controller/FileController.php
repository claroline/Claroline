<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\FileType;
use Claroline\CoreBundle\Library\Resource\MimeTypes;

class FileController extends Controller
{
    /**
     * Deletes a file
     *
     * @param AbstractResource $file
     */
    public function delete(AbstractResource $file)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($file);
        $em->flush();
        $pathName = $this->container->getParameter('claroline.files.directory')
            . DIRECTORY_SEPARATOR
            . $file->getHashName();
        chmod($pathName, 0777);
        unlink($pathName);
    }

    /**
     * Returns the resource form.
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->get('form.factory')->create(new FileType, new File());
    }

    /**
     * Returns the form in a template. $twigFile will contain the default template called
     * but you can do your own.
     *
     * @param string $twigFile
     * @param integer $id
     * @param string $type
     *
     * @return string
     */
    public function getFormPage($twigFile, $id, $type)
    {
        $form = $this->get('form.factory')->create(new FileType, new File());

        return $this->render(
            $twigFile, array('form' => $form->createView(), 'parentId' => $id, 'type' => $type)
        );
    }

    /**
     * Create a directory. Right/user/parent are set by the resource controller
     * but you can use them here aswell.
     *
     * @param Form    $form
     * @param integer $id   the parent id
     * @param User    $user $user the user creating the directory
     *
     * @return File
     */
    public function add(File $file, $id, User $user)
    {
        $tmpFile = $file->getName();
        $fileName = $tmpFile->getClientOriginalName();
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $size = filesize($tmpFile);
        $hashName = $this->generateGuid() . "." . $extension;
        $tmpFile->move($this->container->getParameter('claroline.files.directory'), $hashName);
        $file->setSize($size);
        $file->setName($fileName);
        $file->setHashName($hashName);
        $mimeType = MimeTypes::getMimeType($extension);
        $file->setMimeType($mimeType);
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($file);
        $em->flush();
        //$this->thumbnailGenerator->createThumbNail("{$this->dir}/$hashName", "{$this->dir}/tn_{$hashName}", ThumbnailGenerator::WIDTH, ThumbnailGenerator::HEIGHT);

        return $file;
    }

    /**
     * Returns the copied AbstractResource file.
     * Copy a file.
     *
     * @param type $resource
     * @param User $user
     *
     * @return \Claroline\CoreBundle\Entity\Resource\File
     */
    public function copy($resource, User $user)
    {
        $newFile = new File();
        $newFile->setSize($resource->getSize());
        $newFile->setName($resource->getName());
        $hashName = $this->generateGuid() . "." . pathinfo($resource->getHashName(), PATHINFO_EXTENSION);
        $newFile->setHashName($hashName);
        $filePath = $this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR . $resource->getHashName();
        $newPath = $this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR . $hashName;
        copy($filePath, $newPath);
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($newFile);
        $em->flush();

        return $newFile;
    }

    /**
     * Returns the resource type as a string, it'll be used by the resource controller to find this service
     *
     * @return string
     */
    public function getResourceType()
    {
        return "file";
    }

    /**
     * Default action for a file: downloading the file.
     *
     * @param integer $resourceId
     *
     * @return Response
     */
    public function getDefaultAction($resourceId)
    {
        $response = new Response();
        $file = $this->getDoctrine()->getEntityManager()->getRepository('Claroline\CoreBundle\Entity\Resource\File')->find($resourceId);

        return $this->setDownloadHeaders($file, $response);
        ;
    }

    /**
     * Fired when OpenAction is fired for a directory in the resource controller.
     * It's send with the workspaceId to keep the context. Thr workspaceId should
     * be removed.
     *
     * @param integer          $workspaceId
     * @param ResourceInstance $resourceInstance
     *
     * @return Response
     */
    public function indexAction($resourceId)
    {
        $resource = $this->getDoctrine()->getEntityManager()->getRepository('Claroline\CoreBundle\Entity\Resource\File')->find($resourceId);
        $mime = $resource->getMimeType();

        if($mime != 'claroline\default')
        {
            $name = $this->findPlayerService($mime);
            if(null != $name)
            {
                return $this->get($name)->indexAction($resourceId);
            }

        }

        $content = $this->render(
            'ClarolineCoreBundle:File:index.html.twig');
        return new Response($content);
    }

    /**
     * Returns the name of the default player.
     * It'll define what will happen when indexAction is fired
     *
     * @return string
     */
    public function getPlayerName()
    {
        return "default file player";
    }

    /**
     * Generated an unique identifier.
     *
     * @see http://php.net/manual/fr/function.com-create-guid.php
     *
     * @return string;
     */
    private function generateGuid()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    /**
     * Set the header for the file downloda response.
     *
     * @param File $file
     * @param Response $response
     *
     * @return Response
     */
    private function setDownloadHeaders(File $file, Response $response)
    {
        $response->setContent(file_get_contents($this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR . $file->getHashName()));
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $file->getName());
        $response->headers->set('Content-Length', $file->getSize());
        $response->headers->set('Content-Type', 'application/' . pathinfo($file->getName(), PATHINFO_EXTENSION));
        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * Returns the service's name for the MimeType $mimeType
     *
     * @param string $mimeType
     *
     * @return string
     */
    private function findPlayerService($mimeType)
    {
        $services = $this->container->getParameter("claroline.resource_players");
        $names = array_keys($services);
        $serviceName = null;

        foreach ($names as $name) {
            $fileMime = $this->get($name)->getMimeType();
            $serviceName = null;

            if ($fileMime == $mimeType && $serviceName == null) {
                $serviceName = $name;
            }
        }

        return $serviceName;
    }
}
