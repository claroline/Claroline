<?php

namespace Claroline\HTMLPageBundle\Manager;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Claroline\CoreBundle\Library\Manager\FileManager;
use Claroline\CoreBundle\Library\Services\ThumbnailGenerator;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * this service is called when the resource controller is using a "HTMLPage" resource
 */
class HTMLPageManager extends FileManager
{
    /**@ var string */
    private $pageDir;

    /**@var router */
    private $router;

    /**
     * Constructor
     *
     * @param FormFactory $formFactory
     * @param EntityManager $em
     * @param RightManagerInterface $rightManager
     * @param string $dir
     * @param TwigEngine $templating
     * @param ThumbnailGenerator $thumbnailGenerator
     * @param Router $router
     * @param string $pageDir
     */
    public function __construct(FormFactory $formFactory, EntityManager $em, RightManagerInterface $rightManager, $dir, TwigEngine $templating, ThumbnailGenerator $thumbnailGenerator, Router $router, $pageDir)
    {
        parent::__construct($formFactory, $em, $rightManager, $dir, $templating, $thumbnailGenerator);

        $this->router = $router;
        $this->pageDir = $pageDir;
    }

    /**
     * Returns the resource type as a string, it'll be used by the resource controller to find this service
     *
     * @return string
     */
    public function getResourceType()
    {
        return "HTMLElement";
    }

    /**
     * Fired when OpenAction is fired for a HTMLPage in the resource controller.
     * It's send with the workspaceId to keep the context.
     *
     * @param integer $workspaceId
     * @param ResourceInstance $resourceInstance
     *
     * @return RedirectResponse
     */
    public function indexAction($workspaceId, ResourceInstance $resourceInstance)
    {
        return new Response("index PAGEMANAGER HTML");
    }

    /**
     * Default action for a HTMLPage. Unzip the zipfile and redirect to the file index.html
     *
     * @param integer $id
     *
     * @return RedirectResponse
     */
    public function getDefaultAction($id)
    {
        $ds = DIRECTORY_SEPARATOR;

        $resource = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\File')->find($id);
        $hashName = $resource->getHashName();
        $this->unzipTmpFile($hashName);
        $relativePath = pathinfo($resource->getHashName(), PATHINFO_FILENAME).$ds.pathinfo($resource->getName(), PATHINFO_FILENAME).$ds."index.html";
        $route = $this->router->getContext()->getBaseUrl();
        $fp = preg_replace('"/web/app_dev.php$"', "/web/HTMLPage/$relativePath", $route);

        return new RedirectResponse($fp);
    }

    /**
     * Unzip an archive in the web/HTMLPage directory.
     *
     * @param type $hashName
     *
     * @return int
     */
    private function unzipTmpFile($hashName)
    {
        $path = $this->dir . DIRECTORY_SEPARATOR . $hashName;
        $zip = new \ZipArchive();

        if ($zip->open($path) === true)
        {
            $zip->extractTo($this->pageDir . DIRECTORY_SEPARATOR . pathinfo($hashName, PATHINFO_FILENAME) . DIRECTORY_SEPARATOR);
            $zip->close();
        }
        else
        {
            return 0;
        }
    }
}
