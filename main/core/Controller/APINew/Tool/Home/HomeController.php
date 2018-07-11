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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/home")
 */
class HomeController extends AbstractCrudController
{
    /**
     * @EXT\Route(
     *    "/update",
     *    name="apiv2_home_update",
     *    options={ "method_prefix" = false }
     * )
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateHomeAction(Request $request)
    {
        $tabs = $this->decodeRequest($request);
        $ids = [];

        foreach ($tabs as $tab) {
            if (HomeTab::TYPE_ADMIN_DESKTOP !== $tab['type']) {
                $this->crud->update(HomeTab::class, $tab);
                if ($tab['user']) {
                    $filters['user'] = $tab['user']['id'];
                }

                if ($tab['workspace']) {
                    $filters['workspace'] = $tab['workspace']['uuid'];
                }

                $ids[] = $tab['id'];
            }
        }

        //remove superfluous tabs
        $installedTabs = $this->finder->fetch(HomeTab::class, $filters);

        foreach ($installedTabs as $installedTab) {
            if (!in_array($installedTab->getUuid(), $ids)) {
                $this->crud->delete($installedTab);
            }
        }

        return new JsonResponse($tabs);
    }

    /** @return string */
    public function getName()
    {
        return 'home';
    }
}
