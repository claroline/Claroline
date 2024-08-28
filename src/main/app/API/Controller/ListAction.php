<?php

namespace Claroline\AppBundle\API\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

trait ListAction {
    use CrudAction;

    abstract protected function getDefaultHiddenFilters(): array;

    /**
     * @Route("/", name="list", methods={"GET"})
     *
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
    public function listAction(Request $request): JsonResponse
    {
        $options = static::getOptions();

        $finderQuery = FinderQuery::fromRequest($request)
            ->addFilters($this->getDefaultHiddenFilters());

        //return new JsonResponse($this->getCrud()->search(static::getClass(), $finderQuery, $options));

        return new JsonResponse(
            $this->getCrud()->list(static::getClass(), array_merge([], $request->query->all(), [
                'hiddenFilters' => $this->getDefaultHiddenFilters(),
            ]), $options['list'] ?? [])
        );
    }
}
