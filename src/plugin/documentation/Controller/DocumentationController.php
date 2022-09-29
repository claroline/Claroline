<?php

namespace Claroline\DocumentationBundle\Controller;

use Claroline\CoreBundle\Manager\CurlManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/doc")
 */
class DocumentationController
{
    /** @var CurlManager */
    private $curlManager;

    public function __construct(CurlManager $curlManager)
    {
        $this->curlManager = $curlManager;
    }

    /**
     * @Route("/", name="apiv2_documentation_list", methods={"GET"})
     */
    public function listAction(Request $request): JsonResponse
    {
        $query = $request->query->all();
        if (!isset($query['filters'])) {
            $query['filters'] = [];
        }

        $query['filters']['resourceType'] = 'icap_lesson'; // only allow lessons for now
        $query['filters']['tags'] = array_merge(isset($query['filters']['tags']) ? $query['filters']['tags'] : [], [
            'documentation', // only get resources related to documentation
        ]);

        return new JsonResponse(
            $this->callDocumentationApi('/apiv2/resource/null/all', $query)
        );
    }

    /**
     * @Route("/{id}", name="apiv2_documentation_get", methods={"GET"})
     */
    public function getAction(string $id): JsonResponse
    {
        return new JsonResponse(
            $this->callDocumentationApi('/resources/load/'.$id)
        );
    }

    private function callDocumentationApi($path, array $query = []): ?array
    {
        $response = $this->curlManager->exec('https://get.claroline.com'.$path, $query, 'GET', [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Requested-With: XMLHttpRequest',
            ],
        ]);

        $result = null;
        if (!empty($response)) {
            $result = json_decode($response, true);
            if (null === $result) {
                // not a json
                $result = $response;
            }
        }

        return $result;
    }
}
