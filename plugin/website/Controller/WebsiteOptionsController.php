<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 9/2/14
 * Time: 2:42 PM.
 */

namespace Icap\WebsiteBundle\Controller;

use Icap\WebsiteBundle\Entity\Website;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class WebsiteOptionsController.
 *
 * @Route(
 *      "/{websiteId}",
 *      requirements={"websiteId" = "\d+"}
 * )
 */
class WebsiteOptionsController extends Controller
{
    /**
     * @Route(
     *      "/options",
     *      name="icap_website_options_update",
     *      options={"expose"=true}
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Method({"PUT"})
     */
    public function putAction(Request $request, Website $website)
    {
        $response = new JsonResponse();
        $data = null;
        $user = $this->getLoggedUser();
        if ($user !== null) {
            try {
                $this->checkAccess('ADMINISTRATE', $website);
                try {
                    $optionsManager = $this->getWebsiteOptionsManager();
                    $response->setData($request->request->all());
                    $data = $optionsManager->processForm($website->getOptions(), $request->request->all(), 'PUT');
                } catch (\Exception $e) {
                    $data = get_class($e);
                    $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } catch (\Exception $e) {
                $data = $e->getMessage();
                $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            }
        } else {
            $response->setStatusCode(Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED);
        }
        $response->setData($data);

        return $response;
    }

    /**
     * @Route(
     *      "/options/upload/{imageStr}",
     *      name="icap_website_options_image_upload",
     *      options={"expose"=true}
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Method({"POST"})
     */
    public function uploadImageFileAction(Request $request, Website $website, $imageStr)
    {
        $response = new JsonResponse();
        $data = null;
        $user = $this->getLoggedUser();
        if ($user !== null) {
            try {
                $this->checkAccess('ADMINISTRATE', $website);
                try {
                    $optionsManager = $this->getWebsiteOptionsManager();
                    $uploadedFile = $request->files->get('imageFile');
                    if ($uploadedFile !== null) {
                        $options = $website->getOptions();
                        $data = $optionsManager->handleUploadImageFile($options, $uploadedFile, $imageStr);
                    } else {
                        $response->setStatusCode(Response::HTTP_EXPECTATION_FAILED);
                    }
                } catch (\Exception $e) {
                    $data = $e;
                    $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } catch (\Exception $e) {
                $data = $e->getMessage();
                $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            }
        } else {
            $response->setStatusCode(Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED);
        }
        $response->setData($data);

        return $response;
    }

    /**
     * @Route(
     *      "/options/update-image/{imageStr}",
     *      name="icap_website_options_image_update",
     *      options={"expose"=true}
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Method({"PUT"})
     */
    public function updateImagePathAction(Request $request, Website $website, $imageStr)
    {
        $response = new JsonResponse();
        $data = null;
        $user = $this->getLoggedUser();
        if ($user !== null) {
            try {
                $this->checkAccess('ADMINISTRATE', $website);
                try {
                    $optionsManager = $this->getWebsiteOptionsManager();
                    $newPath = $request->request->get('newPath');
                    $options = $website->getOptions();
                    $data = $optionsManager->handleUpdateImageUrl($options, $newPath, $imageStr);
                } catch (\Exception $e) {
                    $data = $e;
                    $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } catch (\Exception $e) {
                $data = $e->getMessage();
                $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            }
        } else {
            $response->setStatusCode(Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED);
        }
        $response->setData($data);

        return $response;
    }
}
