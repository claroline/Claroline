<?php

namespace Claroline\AppBundle\API\Controller;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

trait ListAction
{
    use CrudAction;

    abstract protected function getDefaultHiddenFilters(): array;

    #[Route(path: '', name: 'list', methods: ['GET'])]
    public function listAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $options = static::getOptions();

        $results = $this->getCrud()->search(static::getClass(), $finderQuery, $options['list'] ?? []);
        if (is_array($results)) {
            // retro-compatibility with old finders
            return new StreamedJsonResponse($results);
        }

        return $results->toResponse();
    }
}
