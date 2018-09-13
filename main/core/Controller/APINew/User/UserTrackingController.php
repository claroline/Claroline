<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\User;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/usertracking"),
 */
class UserTrackingController extends AbstractCrudController
{
    public function getName()
    {
        return 'usertracking';
    }

    public function getIgnore()
    {
        return ['create', 'deleteBulk', 'exist', 'copyBulk', 'schema', 'find', 'list'];
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/trackings/list",
     *     name="apiv2_user_trackings_list"
     * )
     * @EXT\ParamConverter(
     *     "user",
     *     class="ClarolineCoreBundle:User",
     *     options={"mapping": {"user": "uuid"}}
     * )
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function trackingsListAction(User $user, Request $request)
    {
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['user'] = $user->getUuid();
        $params['hiddenFilters']['fromDate'] = $params['startDate'];
        $params['hiddenFilters']['untilDate'] = $params['endDate'];

        $params['sortBy'] = '-date';

        $data = $this->finder->search('Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation', $params);

        return new JsonResponse($data, 200);
    }
}
