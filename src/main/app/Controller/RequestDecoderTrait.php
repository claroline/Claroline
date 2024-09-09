<?php

namespace Claroline\AppBundle\Controller;

use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\Request;

trait RequestDecoderTrait
{
    protected function decodeRequest(Request $request): mixed
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

    protected function decodeIdsString(Request $request, string $class, string $property = 'ids'): array
    {
        $ids = $request->query->all($property) ?? [];
        if (empty($ids)) {
            return [];
        }

        // for retro-compatibility. We should no longer use auto-ids
        $idProp = 'uuid';
        if (is_numeric($ids[0])) {
            $idProp = 'id';
        }

        return $this->om->getRepository($class)->findBy([$idProp => $ids]);
    }
}
