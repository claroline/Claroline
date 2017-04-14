<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionGroup;
use Claroline\CursusBundle\Entity\SessionEventUser;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('claroline_cursus_tool')")
 */
class CourseController extends Controller
{
    private $authorization;
    private $cursusManager;
    private $mailManager;
    private $roleManager;

    /**
     * @DI\InjectParams({
     *     "authorization"    = @DI\Inject("security.authorization_checker"),
     *     "cursusManager"    = @DI\Inject("claroline.manager.cursus_manager"),
     *     "mailManager"      = @DI\Inject("claroline.manager.mail_manager"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        CursusManager $cursusManager,
        MailManager $mailManager,
        RoleManager $roleManager
    ) {
        $this->authorization = $authorization;
        $this->cursusManager = $cursusManager;
        $this->mailManager = $mailManager;
        $this->roleManager = $roleManager;
    }

    /**
     * @EXT\Route(
     *     "cursus/session/evnet/unregister/user/{sessionEventUser}",
     *     name="claro_cursus_session_event_unregister_user",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param SessionEventUser $sessionEventUser
     */
    public function sessionEventUserUnregisterAction(SessionEventUser $sessionEventUser)
    {
        $this->cursusManager->unregisterUsersFromSessionEvent([$sessionEventUser]);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/unregister/group/{sessionGroup}",
     *     name="claro_cursus_course_session_unregister_group",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param CourseSessionGroup $sessionGroup
     */
    public function courseSessionGroupUnregisterAction(CourseSessionGroup $sessionGroup)
    {
        $this->cursusManager->unregisterGroupFromSession($sessionGroup);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/{session}/user/{user}/confirmation/mail/send",
     *     name="claro_cursus_course_session_user_confirmation_mail_send",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param User $user
     */
    public function courseSessionUserConfirmationMailSendAction(CourseSession $session, User $user)
    {
        $confirmationEmail = $this->cursusManager->getConfirmationEmail();

        if (!is_null($confirmationEmail)) {
            $course = $session->getCourse();
            $startDate = $session->getStartDate();
            $endDate = $session->getEndDate();
            $title = $confirmationEmail->getTitle();
            $content = $confirmationEmail->getContent();
            $title = str_replace('%course%', $course->getTitle(), $title);
            $content = str_replace('%course%', $course->getTitle(), $content);
            $title = str_replace('%session%', $session->getName(), $title);
            $content = str_replace('%session%', $session->getName(), $content);

            if (!is_null($startDate)) {
                $title = str_replace('%start_date%', $session->getStartDate()->format('d-m-Y'), $title);
                $content = str_replace('%start_date%', $session->getStartDate()->format('d-m-Y'), $content);
            }

            if (!is_null($endDate)) {
                $title = str_replace('%end_date%', $session->getEndDate()->format('d-m-Y'), $title);
                $content = str_replace('%end_date%', $session->getEndDate()->format('d-m-Y'), $content);
            }
            $this->mailManager->send($title, $content, [$user]);
        }

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/courses/export",
     *     name="claro_cursus_courses_export",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User $user
     */
    public function coursesExportAction(User $user)
    {
        if ($this->authorization->isGranted('ROLE_ADMIN')) {
            $courses = $this->cursusManager->getAllCourses('', 'id', 'ASC', false);
        } else {
            $organizations = $user->getAdministratedOrganizations()->toArray();
            $courses = $this->cursusManager->getAllCoursesByOrganizations($organizations, '', 'id');
        }
        $zipName = 'courses.zip';
        $mimeType = 'application/zip';
        $file = $this->cursusManager->zipDatas($courses, 'course');

        $response = new StreamedResponse();
        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.urlencode($zipName));
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Connection', 'close');
        $response->send();

        return new Response();
    }

    /**
     * @EXT\Route(
     *     "/course/workspace/{workspace}/retrieve/roles/translation/keys",
     *     name="course_workspace_roles_translation_keys_retrieve",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function retrieveRolesTranslationKeysFromWorkspaceAction(Workspace $workspace)
    {
        if (!$this->authorization->isGranted('OPEN', $workspace)) {
            throw new AccessDeniedException();
        }
        $results = [];
        $roles = $this->roleManager->getRolesByWorkspace($workspace);

        foreach ($roles as $role) {
            $results[] = $role->getTranslationKey();
        }

        return new JsonResponse($results);
    }
}
