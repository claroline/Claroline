<?php

namespace Claroline\HTMLPageBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Claroline\CoreBundle\Library\Resource\CustomActionResourceEvent;
use Claroline\CoreBundle\Library\Resource\CreateFormResourceEvent;
use Claroline\CoreBundle\Listener\FileListener;
use Claroline\CoreBundle\Form\FileType;
use Claroline\CoreBundle\Entity\Resource\File;

class HTMLPageListener extends FileListener
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new FileType, new File());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:resource_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'HTMLElement'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onOpen(CustomActionResourceEvent $event)
    {
        $ds = DIRECTORY_SEPARATOR;
        $resource = $this->container->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\File')->find($event->getResourceId());
        $hashName = $resource->getHashName();
        $this->unzipTmpFile($hashName);
        $relativePath = pathinfo($resource->getHashName(), PATHINFO_FILENAME).$ds.pathinfo($resource->getName(), PATHINFO_FILENAME).$ds."index.html";
        $route = $this->container->get('router')->getContext()->getBaseUrl();
        $fp = preg_replace('"/web/app_dev.php$"', "/web/HTMLPage/$relativePath", $route);
        $event->setResponse(new RedirectResponse($fp));
        $event->stopPropagation();
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