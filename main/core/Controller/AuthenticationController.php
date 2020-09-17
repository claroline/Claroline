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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Security\Authentication\Authenticator;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Authentication/login controller.
 */
class AuthenticationController
{
    private $request;
    private $userManager;
    private $encoderFactory;
    private $om;
    private $mailManager;
    private $translator;
    private $authenticator;
    private $router;
    private $ch;
    private $dispatcher;

    public function __construct(
        RequestStack $request,
        UserManager $userManager,
        EncoderFactory $encoderFactory,
        ObjectManager $om,
        TranslatorInterface $translator,
        Authenticator $authenticator,
        MailManager $mailManager,
        RouterInterface $router,
        PlatformConfigurationHandler $ch,
        StrictDispatcher $dispatcher
    ) {
        $this->request = $request->getMasterRequest();
        $this->userManager = $userManager;
        $this->encoderFactory = $encoderFactory;
        $this->om = $om;
        $this->translator = $translator;
        $this->authenticator = $authenticator;
        $this->mailManager = $mailManager;
        $this->router = $router;
        $this->ch = $ch;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @Route(
     *     "/sendmail",
     *     name="claro_security_send_token",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     */
    public function sendEmailAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->om->getRepository(User::class)->findOneByEmail($data['email']);

        if ($user) {
            $user->setHashTime(time());
            $password = sha1(rand(1000, 10000).$user->getUsername().$user->getSalt());
            $user->setResetPasswordHash($password);
            $this->om->persist($user);
            $this->om->flush();

            if ($this->mailManager->sendForgotPassword($user)) {
                return new JsonResponse('password_reset_send', 200);
            }

            return new JsonResponse('mail_config_issue', 500);
        }

        $error = [
          'error' => ['email' => 'email_not_exist'],
        ];

        return new JsonResponse($error, 500);
    }

    /**
     * @Route(
     *     "/validatepassword",
     *     name="claro_security_new_password",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     */
    public function newPasswordAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->userManager->getByResetPasswordHash($data['hash']);

        if (!$user) {
            return new JsonResponse('hash_invalid', 500);
        }

        if (null === $data['password'] && '' === trim($data['password'])) {
            $error = [
              'error' => ['password' => 'password_invalid'],
            ];

            return new JsonResponse($error, 400);
        }

        if ($data['password'] !== $data['confirm']) {
            $error = [
              'error' => ['password' => 'password_value_mismatch'],
            ];

            return new JsonResponse($error, 400);
        }

        $user->setPlainPassword($data['password']);
        $this->userManager->activateUser($user);
        $this->om->persist($user);
        $this->om->flush();

        return new JsonResponse(null, 201);
    }

    /**
     * @Route(
     *     "/validate/email/{hash}",
     *     name="claro_security_validate_email",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     */
    public function validateEmailAction($hash)
    {
        $this->userManager->validateEmailHash($hash);

        return new RedirectResponse(
            $this->router->generate('claro_index')
        );
    }

    /**
     * @Route(
     *     "/send/email/validation",
     *     name="claro_security_validate_email_send",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User $currentUser
     *
     * @return JsonResponse
     */
    public function sendEmailValidationAction(User $currentUser)
    {
        $this->mailManager->sendValidateEmail($currentUser->getEmailValidationHash());

        return new JsonResponse();
    }
}
