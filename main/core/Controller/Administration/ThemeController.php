<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Claroline\CoreBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Theme\Theme;
use Claroline\CoreBundle\Manager\Theme\ThemeManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @DI\Tag("security.secure_service")
 *
 * @SEC\PreAuthorize("hasRole('ADMIN')")
 *
 * @EXT\Route("/admin/themes", options={"expose"=true})
 */
class ThemeController
{
    /** @var SerializerProvider */
    private $serializer;

    /** @var ThemeManager */
    private $manager;

    /**
     * ThemeController constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "manager"    = @DI\Inject("claroline.manager.theme_manager")
     * })
     *
     * @param SerializerProvider $serializer
     * @param ThemeManager       $manager
     */
    public function __construct(
        SerializerProvider $serializer,
        ThemeManager $manager)
    {
        $this->serializer = $serializer;
        $this->manager = $manager;
    }

    /**
     * Displays themes management tool.
     *
     * @EXT\Route("/", name="claro_admin_theme_list")
     * @EXT\Method("GET")
     * @EXT\Template()
     */
    public function indexAction()
    {
        return [
            'isReadOnly' => !$this->manager->isThemeDirWritable(),
            'themes' => array_map(function (Theme $theme) {
                return $this->serializer->serialize($theme);
            }, $this->manager->all()),
        ];
    }
}
