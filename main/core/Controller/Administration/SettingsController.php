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

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Icon\IconSetTypeEnum;
use Claroline\CoreBundle\Manager\IconSetManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\PortalManager;
use Claroline\CoreBundle\Manager\Theme\ThemeManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Tag("security.secure_service")
 */
class SettingsController extends Controller
{
    /**
     * SettingsController constructor.
     *
     * @DI\InjectParams({
     *     "serializer"     = @DI\Inject("claroline.serializer.parameters"),
     *     "localeManager"  = @DI\Inject("claroline.manager.locale_manager"),
     *     "portalManager"  = @DI\Inject("claroline.manager.portal_manager"),
     *     "finder"         = @DI\Inject("claroline.api.finder"),
     *     "translator"     = @DI\Inject("translator"),
     *     "iconSetManager" = @DI\Inject("claroline.manager.icon_set_manager"),
     *     "themeManager"   = @DI\Inject("claroline.manager.theme_manager")
     * })
     *
     * @param SettingsController $serializer
     */
    public function __construct(
        ParametersSerializer $serializer,
        LocaleManager $localeManager,
        PortalManager $portalManager,
        FinderProvider $finder,
        ThemeManager $themeManager,
        TranslatorInterface $translator,
        IconSetManager $iconSetManager
    ) {
        $this->serializer = $serializer;
        $this->localeManager = $localeManager;
        $this->portalManager = $portalManager;
        $this->finder = $finder;
        $this->themeManager = $themeManager;
        $this->translator = $translator;
        $this->iconSetManager = $iconSetManager;
    }

    /**
     * @EXT\Route("/main", name="claro_admin_main_settings")
     * @EXT\Template("ClarolineCoreBundle:administration/settings:main.html.twig")
     * @SEC\PreAuthorize("canOpenAdminTool('main_settings')")
     *
     * @return array
     */
    public function mainAction()
    {
        $portalResources = $this->portalManager->getPortalEnabledResourceTypes();
        $portalChoices = [];

        foreach ($portalResources as $portalResource) {
            $portalChoices[$portalResource] = $this->translator->trans($portalResource, [], 'resource');
        }

        return [
            'parameters' => $this->serializer->serialize(),
            'availablesLocales' => $this->localeManager->getImplementedLocales(),
            'portalResources' => $portalChoices,
        ];
    }

    /**
     * @EXT\Route("/technical", name="claro_admin_technical_settings")
     * @EXT\Template("ClarolineCoreBundle:administration/settings:technical.html.twig")
     * @SEC\PreAuthorize("canOpenAdminTool('technical_settings')")
     *
     * @return array
     */
    public function technicalAction()
    {
        return [
            'parameters' => $this->serializer->serialize(),
        ];
    }

    /**
     * @EXT\Route("/appearance", name="claro_admin_appearance_settings")
     * @EXT\Template("ClarolineCoreBundle:administration/settings:appearance.html.twig")
     * @SEC\PreAuthorize("canOpenAdminTool('appearance_settings')")
     *
     * @return array
     */
    public function appearanceAction()
    {
        $iconSets = $this->iconSetManager->listIconSetsByType(IconSetTypeEnum::RESOURCE_ICON_SET);
        $iconSetChoices = [];

        foreach ($iconSets as $set) {
            $iconSetChoices[$set->getName()] = $set->getName();
        }

        return [
            'parameters' => $this->serializer->serialize(),
            'isReadOnly' => !$this->themeManager->isThemeDirWritable(),
            'iconSetChoices' => $iconSetChoices,
            'themes' => $this->finder->search(
                'Claroline\CoreBundle\Entity\Theme\Theme', [
                    'limit' => -1,
                    'sortBy' => 'name',
                ]
            ),
        ];
    }
}
