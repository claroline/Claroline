<?php

namespace Claroline\HTMLPageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Controller\FileController;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;

class HTMLPageController extends FileController
{

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

        $resource = $this->getDoctrine()->getEntityManager()->getRepository('Claroline\CoreBundle\Entity\Resource\File')->find($id);
        $hashName = $resource->getHashName();
        $this->unzipTmpFile($hashName);
        $relativePath = pathinfo($resource->getHashName(), PATHINFO_FILENAME).$ds.pathinfo($resource->getName(), PATHINFO_FILENAME).$ds."index.html";
        $route = $this->get('router')->getContext()->getBaseUrl();
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
        $path = $this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR . $hashName;
        $zip = new \ZipArchive();

        if ($zip->open($path) === true)
        {
            $zip->extractTo($this->container->getParameter('claroline.html_page.directory') . DIRECTORY_SEPARATOR . pathinfo($hashName, PATHINFO_FILENAME) . DIRECTORY_SEPARATOR);
            $zip->close();
        }
        else
        {
            return 0;
        }
    }
}