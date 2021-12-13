<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnalyticsBundle\Controller\User;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user_tracking")
 */
class TrackingController
{
    /** @var FinderProvider */
    private $finder;

    public function __construct(FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @ApiDoc(
     *     description="List the objects of class ResourceUserEvaluation for a user.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     parameters={
     *          {"name": "user", "type": {"string", "integer"}, "description": "The user uuid"}
     *     }
     * )
     * @Route("/{user}/tracking/list", name="apiv2_user_tracking_list")
     * @EXT\ParamConverter("user", class="ClarolineCoreBundle:User", options={"mapping": {"user": "uuid"}})
     */
    public function listAction(User $user, Request $request): JsonResponse
    {
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['user'] = $user->getUuid();
        if (!empty($params['startDate'])) {
            $params['hiddenFilters']['fromDate'] = $params['startDate'];
        }

        if (!empty($params['endDate'])) {
            $params['hiddenFilters']['untilDate'] = $params['endDate'];
        }

        $params['sortBy'] = '-date';

        return new JsonResponse(
            $this->finder->search(ResourceUserEvaluation::class, $params)
        );
    }
}
