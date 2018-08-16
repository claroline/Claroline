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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
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
    /** @var ObjectManager */
    private $om;

    /**
     * HomeController constructor.
     *
     * @DI\InjectParams({
     *     "finder"     = @DI\Inject("claroline.api.finder"),
     *     "crud"       = @DI\Inject("claroline.api.crud"),
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "om"         = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param FinderProvider     $finder
     * @param Crud               $crud
     * @param SerializerProvider $serializer
     */
    public function __construct(
        FinderProvider $finder,
        Crud $crud,
        SerializerProvider $serializer,
        ObjectManager $om
    ) {
        $this->finder = $finder;
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->om = $om;
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

            if (HomeTab::TYPE_ADMIN_DESKTOP === $context) {
                $updated[] = $this->crud->update(HomeTab::class, $tab, [$context]);
                $ids[] = $tab['id']; // will be used to determine deleted tabs
            } else {
                if (HomeTab::TYPE_ADMIN_DESKTOP !== $tab['type']) {
                    $updated[] = $this->crud->update(HomeTab::class, $tab, [$context]);
                    $ids[] = $tab['id']; // will be used to determine deleted tabs
                } else {
                    $updated[] = $this->om->getObject($tab, HomeTab::class);
                }
            }
        }

        // retrieve existing tabs for the context to remove deleted ones
        /* @var HomeTab[] $installedTabs */
        if ('administration' === $context) {
            $installedTabs = $this->finder->fetch(HomeTab::class, ['type' => HomeTab::TYPE_ADMIN_DESKTOP]);
        } else {
            $installedTabs = $this->finder->fetch(HomeTab::class, 'desktop' === $context ? [
                'user' => $contextId,
            ] : [
                $context => $contextId,
            ]);
            $installedTabs = array_filter($installedTabs, function (HomeTab $tab) {
                return HomeTab::TYPE_ADMIN_DESKTOP !== $tab->getType();
            });
        }

        foreach ($installedTabs as $installedTab) {
            if (!in_array($installedTab->getUuid(), $ids)) {
                // the tab no longer exist we can remove it
                $this->crud->delete($installedTab);
            }
        }
        //for some reason doesn't serialize the widgets content yet the first time

        return new JsonResponse(array_map(function (HomeTab $tab) {
            return $this->serializer->serialize($tab);
        }, $updated));
    }
}
