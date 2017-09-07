<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API;

use Claroline\CoreBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Theme\Theme;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Theme\ThemeManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/themes", name="claro_theme", options={"expose" = true})
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
     * Updates an existing theme.
     *
     * @EXT\Route("/{uuid}", name="claro_theme_update")
     * @EXT\Method("PUT")
     *
     * @param Theme   $theme
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function updateAction(Theme $theme, Request $request, User $user)
    {
        $this->assertCanEdit($theme, $user);

        try {
            $updated = $this->manager->update($theme, json_decode($request->getContent(), true));

            return new JsonResponse($updated, 200);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * Deletes a theme.
     *
     * @EXT\Route("/", name="claro_themes_delete")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Method("DELETE")
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, User $user)
    {
        try {
            $this->manager->deleteBulk(json_decode($request->getContent()), $user);

            return new JsonResponse(null, 204);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    private function assertCanEdit(Theme $theme, User $user)
    {
        if (!$this->manager->canEdit($theme, $user)) {
            throw new AccessDeniedException('Theme can not be edited or delete.');
        }
    }
}
