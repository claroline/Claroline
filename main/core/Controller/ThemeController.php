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
use Claroline\CoreBundle\Form\ThemeType;
use Claroline\CoreBundle\Manager\ThemeManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ADMIN')")
 * @EXT\Route("/admin/themes", requirements={"id"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class ThemeController
{
    private $manager;
    private $formFactory;
    private $router;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.theme_manager"),
     *     "factory" = @DI\Inject("form.factory"),
     *     "router"  = @DI\Inject("router")
     * })
     */
    public function __construct(
        ThemeManager $manager,
        FormFactoryInterface $factory,
        RouterInterface $router
    ) {
        $this->manager = $manager;
        $this->formFactory = $factory;
        $this->router = $router;
    }

    /**
     * @EXT\Route("/", name="claro_admin_theme_list")
     * @EXT\Template()
     */
    public function listAction()
    {
        return [
            'isReadOnly' => !$this->manager->isThemeDirWritable(),
            'themes' => $this->manager->listThemes(),
        ];
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
     * @EXT\Route("/new", name="claro_admin_new_theme")
     * @EXT\Template()
     */
    public function formAction()
    {
        return ['form' => $this->formFactory->create(new ThemeType())];
    }

    /**
     * @EXT\Route("/", name="claro_admin_create_theme")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Theme:form.html.twig")
     *
     * @param Request $request
     *
     * @return RedirectResponse|array
     */
    public function createThemeAction(Request $request)
    {
        $form = $this->formFactory->create(new ThemeType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->manager->createCustomTheme(
                $form['name']->getData(),
                $form['stylesheet']->getData(),
                $form['extendingDefault']->getData()
            );

            return new RedirectResponse($this->router->generate('claro_admin_theme_list'));
        }

        return ['form' => $form];
    }
}
