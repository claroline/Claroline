<?php

namespace Claroline\AppBundle\Controller;

use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * @todo remove the ContainerAware use. It's not always required so let's implementation choose if it want it or not
 */
abstract class AbstractApiController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

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
