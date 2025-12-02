<?php

namespace Claroline\AppBundle\API\Controller;

use Claroline\AppBundle\API\Finder\FinderRequest;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

trait ListAction
{
    use CrudAction;

    #[Route(path: '', name: 'list', methods: ['GET'])]
    public function listAction(
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $options = static::getOptions();

        $results = $this->getCrud()->search(static::getClass(), $finderRequest, $options['list'] ?? []);
        if (is_array($results)) {
            // retro-compatibility with old finders
            return new StreamedJsonResponse($results);
        }

        return $results->toResponse();
    }
}
