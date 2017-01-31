<?php

namespace UJM\ExoBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;

class AbstractController
{
    /**
     * Gets and Deserializes JSON data from Request.
     *
     * @param Request $request
     *
     * @return mixed $data
     */
    protected function decodeRequestData(Request $request)
    {
        $dataRaw = $request->getContent();

        $data = null;
        if (!empty($dataRaw)) {
            $data = json_decode($dataRaw);
        }

        return $data;
    }
}
