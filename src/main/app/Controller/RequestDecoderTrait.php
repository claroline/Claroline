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

    /**
     * @param Request $request
     * @param string  $class
     * @param string  $property
     */
    protected function decodeIdsString(Request $request, $class, $property = 'ids')
    {
        $ids = $request->query->get($property) ?? [];

        $property = is_numeric($ids[0]) ? 'id' : 'uuid';

        return $this->om->findList($class, $property, $ids);
    }
}
