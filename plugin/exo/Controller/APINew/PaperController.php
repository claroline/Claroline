<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UJM\ExoBundle\Controller\APINew;

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Mode\MarkMode;
use UJM\ExoBundle\Library\Options\Transfer;

/**
 * @ApiMeta(
 *     class="UJM\ExoBundle\Entity\Attempt\Paper",
 *     ignore={"create", "update", "deleteBulk", "exist", "copyBulk", "schema", "find", "list"}
 * )
 * @EXT\Route("/exopaper")
 */
class PaperController extends AbstractCrudController
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /* @var FinderProvider */
    protected $finder;

    /**
     * EntryController constructor.
     *
     * @DI\InjectParams({
     *      "authorization" = @DI\Inject("security.authorization_checker"),
     *     "finder"         = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param FinderProvider                $finder
     */
    public function __construct(AuthorizationCheckerInterface $authorization, FinderProvider $finder)
    {
        $this->authorization = $authorization;
        $this->finder = $finder;
    }

    public function getName()
    {
        return 'exopaper';
    }

    /**
     * @EXT\Route(
     *     "/exo/{exercise}/papers/list",
     *     name="apiv2_exopaper_list"
     * )
     * @EXT\ParamConverter(
     *     "exercise",
     *     class="UJMExoBundle:Exercise",
     *     options={"mapping": {"exercise": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param Exercise $exercise
     * @param User     $user
     * @param Request  $request
     *
     * @return JsonResponse
     */
    public function papersListAction(Exercise $exercise, User $user, Request $request)
    {
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['exercise'] = $exercise->getId();
        $serializationOptions = [];

        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if ($this->authorization->isGranted('ADMINISTRATE', $collection) ||
            $this->authorization->isGranted('MANAGE_PAPERS', $collection) ||
            MarkMode::NEVER !== $exercise->getMarkMode()
        ) {
            $serializationOptions[] = Transfer::INCLUDE_USER_SCORE;
        }

        $data = $this->finder->search('UJM\ExoBundle\Entity\Attempt\Paper', $params, $serializationOptions);

        return new JsonResponse($data, 200);
    }
}
