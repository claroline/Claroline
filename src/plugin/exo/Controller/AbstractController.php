<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated use AbstractApiController
 */
class AbstractController
{
    /**
     * Gets and Deserializes JSON data from Request.
     *
     * @return mixed $data
     */
    protected function decodeRequestData(Request $request)
    {
        $dataRaw = $request->getContent();
        $data = null;

        if (!empty($dataRaw)) {
            $data = json_decode($dataRaw, true);
        }

        return $data;
    }
}
