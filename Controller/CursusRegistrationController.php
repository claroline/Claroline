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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusDisplayedWord;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CursusRegistrationController extends Controller
{
    private $cursusManager;
    private $securityContext;
    private $toolManager;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "cursusManager"   = @DI\Inject("claroline.manager.cursus_manager"),
     *     "securityContext" = @DI\Inject("security.context"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        CursusManager $cursusManager,
        SecurityContextInterface $securityContext,
        ToolManager $toolManager,
        Translator $translator
    )
    {
        $this->cursusManager = $cursusManager;
        $this->securityContext = $securityContext;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
    }

    /**
     * @EXT\Route(
     *     "/tool/registration/index",
     *     name="claro_cursus_tool_registration_index"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function cursusToolRegistrationIndexAction()
    {
        $this->checkToolAccess();
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }
        $allRootCursus = $this->cursusManager->getAllRootCursus();

        return array(
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords,
            'type' => 'cursus',
            'allRootCursus' => $allRootCursus
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/register/user/{user}",
     *     name="claro_cursus_register_user",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function cursusUserRegisterAction(Cursus $cursus, User $user)
    {
        $this->checkToolAccess();
        $this->cursusManager->registerUserToCursus($cursus, $user);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/register/users",
     *     name="claro_cursus_register_users",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "userIds"}
     * )
     *
     * @param Cursus $cursus
     * @param User[] $users
     */
    public function cursusUsersRegisterAction(Cursus $cursus, array $users)
    {
        $this->checkToolAccess();
        $this->cursusManager->registerUsersToCursus($cursus, $users);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/unregister/user/{user}",
     *     name="claro_cursus_unregister_user",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function cursusUserUnregisterAction(Cursus $cursus, User $user)
    {
        $this->checkToolAccess();
        $this->cursusManager->unregisterUserFromCursus($cursus, $user);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/unregister/users",
     *     name="claro_cursus_unregister_users",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "userIds"}
     * )
     *
     * @param Cursus $cursus
     * @param User[] $users
     */
    public function cursusUsersUnregisterAction(Cursus $cursus, array $users)
    {
        $this->checkToolAccess();
        $this->cursusManager->unregisterUsersFromCursus($cursus, $users);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/register/group/{group}",
     *     name="claro_cursus_register_group",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function cursusGroupRegisterAction(Cursus $cursus, Group $group)
    {
        $this->checkToolAccess();
        $this->cursusManager->registerGroupToCursus($cursus, $group);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/unregister/group/{group}",
     *     name="claro_cursus_unregister_group",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function cursusGroupUnregisterAction(Cursus $cursus, Group $group)
    {
        $this->checkToolAccess();
        $this->cursusManager->unregisterGroupFromCursus($cursus, $group);

        return new JsonResponse('success', 200);
    }

    private function checkToolAccess()
    {
        $cursusTool = $this->toolManager->getAdminToolByName('claroline_cursus_tool_registration');

        if (is_null($cursusTool) ||
            !$this->securityContext->isGranted('OPEN', $cursusTool)) {

            throw new AccessDeniedException();
        }
    }
}
