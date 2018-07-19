<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Tool\Home;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractApiController;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/home")
 */
class HomeController extends AbstractApiController
{
    /** @var FinderProvider */
    private $finder;
    /** @var Crud */
    private $crud;
    /** @var SerializerProvider */
    private $serializer;

    /**
     * HomeController constructor.
     *
     * @DI\InjectParams({
     *     "finder"     = @DI\Inject("claroline.api.finder"),
     *     "crud"       = @DI\Inject("claroline.api.crud"),
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param FinderProvider     $finder
     * @param Crud               $crud
     * @param SerializerProvider $serializer
     */
    public function __construct(
        FinderProvider $finder,
        Crud $crud,
        SerializerProvider $serializer)
    {
        $this->finder = $finder;
        $this->crud = $crud;
        $this->serializer = $serializer;
    }

    /**
     * @EXT\Route(
     *    "/{context}/{contextId}/tabs",
     *    name="apiv2_home_update",
     *    options={ "method_prefix" = false }
     * )
     * @EXT\Method("PUT")
     *
     * @param Request $request
     * @param string  $context
     * @param string  $contextId
     *
     * @return JsonResponse
     */
    public function updateTabsAction(Request $request, $context, $contextId)
    {
        // grab tabs data
        $tabs = $this->decodeRequest($request);

        $ids = [];
        $updated = [];
        foreach ($tabs as $tab) {
            // do not update tabs set by the administration tool
            if (HomeTab::TYPE_ADMIN_DESKTOP !== $tab['type']) {
                $updated[] = $this->crud->update(HomeTab::class, $tab);
                $ids[] = $tab['id']; // will be used to determine deleted tabs
            }
        }

        // retrieve existing tabs for the context to remove deleted ones
        /** @var HomeTab[] $installedTabs */
        $installedTabs = $this->finder->fetch(HomeTab::class, [
            $context => $contextId,
        ]);

        foreach ($installedTabs as $installedTab) {
            if (!in_array($installedTab->getUuid(), $ids)) {
                // the tab no longer exist we can remove it
                $this->crud->delete($installedTab);
            }
        }

        return new JsonResponse(array_map(function (HomeTab $tab) {
            return $this->serializer->serialize($tab);
        }, $updated));
    }
}
