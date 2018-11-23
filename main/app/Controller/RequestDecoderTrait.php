<?php

namespace Claroline\AppBundle\Controller;

use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\Request;

trait RequestDecoderTrait
{
    /**
     * @param Request $request
     *
     * @return array
     *
     * @throws InvalidDataException
     */
    protected function decodeRequest(Request $request)
    {
        $decodedRequest = json_decode($request->getContent(), true);

        if (null === $decodedRequest) {
            throw new InvalidDataException('Invalid request content sent.', []);
        }

        return $decodedRequest;
    }
}
