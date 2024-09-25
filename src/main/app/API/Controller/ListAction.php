<?php

namespace Claroline\AppBundle\API\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

trait ListAction
{
    use CrudAction;

    abstract protected function getDefaultHiddenFilters(): array;

    /**
     * @ApiDoc(
     *     description="List the objects of class $class.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     response={"$list"}
     * )
     */
    #[Route(path: '/', name: 'list', methods: ['GET'])]
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

        return new StreamedJsonResponse([
            'totalResults' => $results->count(),
            'data' => $results->getItems(),
        ]);
    }
}
