<?php

namespace Innova\MediaResourceBundle\Controller;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Innova\MediaResourceBundle\Entity\MediaResource;
use Innova\MediaResourceBundle\Entity\Options;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class MediaResourceController.
 *
 * @Route("workspaces/{workspaceId}", options={"expose"=true})
 * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\Workspace", options={"mapping": {"workspaceId": "id"}})
 */
class MediaResourceController extends Controller
{
    /**
     * display a media resource.
     *
     * @Route("/open/{id}", requirements={"id" = "\d+"}, name="innova_media_resource_open")
     * @Method("GET")
     */
    public function openAction(Workspace $workspace, MediaResource $mr)
    {
        if (false === $this->container->get('security.context')->isGranted('OPEN', $mr->getResourceNode())) {
            throw new AccessDeniedException();
        }

        return $this->render('InnovaMediaResourceBundle:MediaResource:players.html.twig', [
                  '_resource' => $mr,
              ]
      );
    }

    /**
     * administrate a media resource.
     *
     * @Route("/edit/{id}", requirements={"id" = "\d+"}, name="innova_media_resource_administrate")
     * @Method("GET")
     */
    public function administrateAction(Workspace $workspace, MediaResource $mr)
    {
        if (false === $this->container->get('security.context')->isGranted('ADMINISTRATE', $mr->getResourceNode())) {
            throw new AccessDeniedException();
        }

        return $this->render('InnovaMediaResourceBundle:MediaResource:administrate.html.twig', [
                    '_resource' => $mr,
          ]
        );
    }

    /**
     * Save resource action, save regions and there configuration and ressource options.
     *
     * @Route("/save/{id}", requirements={"id" = "\d+"}, name="media_resource_save")
     * @Method("POST")
     */
    public function save(Workspace $workspace, MediaResource $resource)
    {
        $data = $this->container->get('request')->request->all();
        $this->get('innova_media_resource.manager.media_resource_options')->update($resource->getOptions(), $data);
        $this->get('innova_media_resource.manager.media_resource_region')->updateRegions($resource, $data);

        return new JsonResponse($resource);
    }

    /**
     * Serve a ressource file that is not in the web folder as a base64 string.
     *
     * @Route(
     *     "/{id}/media",
     *     name="innova_get_mediaresource_resource_file"
     * )
     * @Method({"GET", "POST"})
     */
    public function serveMediaResourceFile(MediaResource $mr)
    {
        $fileUrl = $this->get('innova_media_resource.manager.media_resource_media')->getAudioMediaUrlForAjax($mr);
        $path = $this->container->getParameter('claroline.param.files_directory')
            .DIRECTORY_SEPARATOR
            .$fileUrl;
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->file($path);
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $type);

        return $response;
    }

    /**
     * Create a zip that contains :
     * - the original file
     * - .vtt file (might be empty)
     * - all regions as audio files.
     *
     * @Route(
     *     "/{id}/zip",
     *     name="mediaresource_zip_export",
     * )
     * @Method("POST")
     */
    public function exportToZip(MediaResource $resource)
    {
        $data = $this->container->get('request')->request->all();
        $zipData = $this->get('innova_media_resource.manager.media_resource')->exportToZip($resource, $data);

        $response = new Response();
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.urlencode($zipData['name']));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Connection', 'close');
        $response->sendHeaders();

        $response->setContent(readfile($zipData['zip']));
        // remove zip file
        unlink($zipData['zip']);
        // remove temp folder
        rmdir($zipData['tempFolder']);

        return $response;
    }
}
