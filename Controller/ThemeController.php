<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Theme\Theme;
use Claroline\CoreBundle\Manager\ThemeManager;
use JMS\SecurityExtraBundle\Annotation as SEC;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ADMIN')")
 * @EXT\Route("/admin/themes", requirements={"id"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class ThemeController
{
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.theme_manager")
     * })
     */
    public function __construct(ThemeManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @EXT\Route("/", name="claro_admin_theme_list")
     * @EXT\Template()
     */
    public function listAction()
    {
        return ['themes' => $this->manager->listThemes()];
    }

    /**
     * @EXT\Route("/{id}", name="claro_admin_theme_delete")
     * @EXT\Method("DELETE")
     */
    public function deleteAction(Theme $theme)
    {
        $this->manager->deleteTheme($theme);

        return new JsonResponse();
    }

    /**
     * @EXT\Route("/new", name="claro_admin_theme_create")
     * @EXT\Template()
     */
    public function formAction()
    {

    }
}
