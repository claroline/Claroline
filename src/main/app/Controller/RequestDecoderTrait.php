<?php

namespace Claroline\AppBundle\Controller;

use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\Request;

trait RequestDecoderTrait
{
    /**
     * @return mixed|null
     */
    protected function decodeRequest(Request $request)
    {
        $decodedRequest = null;
        if (!empty($request->getContent())) {
            $decodedRequest = json_decode($request->getContent(), true);

            if (null === $decodedRequest) {
                throw new InvalidDataException('Invalid request content sent.', []);
            }
        }

        return $decodedRequest;
    }

    protected function decodeIdsString(Request $request, string $class, string $property = 'ids')
    {
        $ids = $request->query->get($property) ?? [];
        if (empty($ids)) {
            return [];
        }

        $property = is_numeric($ids[0]) ? 'id' : 'uuid';

        return $this->om->findList($class, $property, $ids);
    }
}
