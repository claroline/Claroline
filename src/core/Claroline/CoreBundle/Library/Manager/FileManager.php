<?php

namespace Claroline\CoreBundle\Library\Manager;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactory;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\FileType;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Claroline\CoreBundle\Library\Services\ThumbnailGenerator;

/**
 * this service is called when the resource controller is using a "file" resource
 */
class FileManager implements ResourceManagerInterface
{

    /** @var EntityManager */
    protected $em;

    /** @var string */
    protected $dir;

    /** @var FormFactory */
    protected $formFactory;

    /** @var RightManagerInterface */
    protected $rightManager;

    /** @var TwigEngine */
    protected $templating;

    /** @var ThumbnilGenerator * */
    protected $thumbnailGenerator;

    /**
     * Constructor
     *
     * @param FormFactory $formFactory
     * @param EntityManager $em
     * @param RightManagerInterface $rightManager
     * @param string $dir
     * @param TwigEngine $templating
     * @param ThumbnailGenerator $thumbnailGenerator
     */
    public function __construct(FormFactory $formFactory, EntityManager $em, RightManagerInterface $rightManager, $dir, TwigEngine $templating, ThumbnailGenerator $thumbnailGenerator)
    {
        $this->em = $em;
        $this->rightManager = $rightManager;
        $this->formFactory = $formFactory;
        $this->dir = $dir;
        $this->templating = $templating;
        $this->thumbnailGenerator = $thumbnailGenerator;
    }

    /**
     * Deletes a file
     *
     * @param AbstractResource $file
     */
    public function delete(AbstractResource $file)
    {
        $this->removeFile($file);
        $this->em->remove($file);
        $this->em->flush();
    }

    /**
     * Returns the resource form.
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->formFactory->create(new FileType, new File());
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
        $form = $this->formFactory->create(new FileType, new File());

        return $this->templating->render(
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
    public function add($form, $id, User $user)
    {
        $tmpFile = $form['name']->getData();
        $fileName = $tmpFile->getClientOriginalName();
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $size = filesize($tmpFile);
        $hashName = $this->generateGuid() . "." . $extension;
        $tmpFile->move($this->dir, $hashName);
        $file = new File();
        $file->setSize($size);
        $file->setName($fileName);
        $file->setHashName($hashName);
        $mime = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\MimeType')->findOneBy(array('extension' => $extension));

        if (null === $mime) {
            $mime = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\MimeType')->findOneBy(array('extension' => 'default'));
        }

        $file->setMimeType($mime);
        $this->em->persist($file);
        $this->em->flush();
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
        $filePath = $this->dir . DIRECTORY_SEPARATOR . $resource->getHashName();
        $newPath = $this->dir . DIRECTORY_SEPARATOR . $hashName;
        copy($filePath, $newPath);
        $this->em->persist($newFile);
        $this->em->flush();

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
        $file = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\File')->find($resourceId);

        return $this->setDownloadHeaders($file, $response);
        ;
    }

    /**
     * Fired when OpenAction is fired for a directory in the resource controller.
     * It's send with the workspaceId to keep the context
     *
     * @param integer          $workspaceId
     * @param ResourceInstance $resourceInstance
     *
     * @return Response
     */
    public function indexAction($workspaceId, ResourceInstance $resourceInstance)
    {
        $content = $this->templating->render(
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
     * found on http://php.net/manual/fr/function.com-create-guid.php
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
        $response->setContent(file_get_contents($this->dir . DIRECTORY_SEPARATOR . $file->getHashName()));
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $file->getName());
        $response->headers->set('Content-Length', $file->getSize());
        $response->headers->set('Content-Type', 'application/' . pathinfo($file->getName(), PATHINFO_EXTENSION));
        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * removes a file physically
     *
     * @param File $file
     */
    private function removeFile(File $file)
    {
        $pathName = $this->dir . DIRECTORY_SEPARATOR . $file->getHashName();
        chmod($pathName, 0777);
        unlink($pathName);
    }
}
