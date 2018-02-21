<?php

namespace Claroline\AppBundle\Controller;

use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractApiController extends ContainerAware
{
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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
