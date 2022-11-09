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

use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Security\Authentication\Authenticator;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\ValidateEmailEvent;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AuthenticationController
{
    use RequestDecoderTrait;

    private $userManager;
    private $om;
    private $mailManager;
    private $routingHelper;
    private $authenticator;
    private $eventDispatcher;

    public function __construct(
        UserManager $userManager,
        ObjectManager $om,
        MailManager $mailManager,
        RoutingHelper $routingHelper,
        Authenticator $authenticator,
        StrictDispatcher $eventDispatcher
    ) {
        $this->userManager = $userManager;
        $this->om = $om;
        $this->mailManager = $mailManager;
        $this->routingHelper = $routingHelper;
        $this->authenticator = $authenticator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Activate and log in a user using the validation hash sent to him.
     * ATTENTION : This is used to generate the validation URL sent by email. The URL must not change overtime.
     *
     * @Route("/user/registration/activate/{hash}", name="claro_security_activate_user")
     */
    public function activateUserAction(string $hash, Request $request)
    {
        if (!empty($hash)) {
            $user = $this->userManager->getByResetPasswordHash($hash);
            if ($user) {
                $this->userManager->activateUser($user);

                return $this->authenticator->login($user, $request);
            }
        }

        return new RedirectResponse(
            $this->routingHelper->indexPath()
        );
    }

    /**
     * Resets a user password and send an email to the user to let him choose a new one.
     *
     * @Route("/sendmail", name="claro_security_send_token", methods={"POST"})
     */
    public function sendForgotPasswordAction(Request $request): JsonResponse
    {
        $data = $this->decodeRequest($request);
        if (empty($data['email'])) {
            return new JsonResponse([
                'error' => ['email' => 'value_not_blank'],
            ], 400);
        }

        $user = $this->om->getRepository(User::class)->findOneBy(['email' => $data['email']]);

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

        return new JsonResponse([
            'error' => ['email' => 'email_not_exist'],
        ], 500);
    }

    /**
     * @Route("/validatepassword", name="claro_security_new_password", methods={"POST"})
     */
    public function newPasswordAction(Request $request): JsonResponse
    {
        $data = $this->decodeRequest($request);
        if (empty($data['hash'])) {
            return new JsonResponse('hash_invalid', 500);
        }

        $user = $this->userManager->getByResetPasswordHash($data['hash']);
        if (!$user) {
            return new JsonResponse('hash_invalid', 500);
        }

        if (null === $data['password'] && '' === trim($data['password'])) {
            return new JsonResponse([
                'error' => ['password' => 'password_invalid'],
            ], 400);
        }

        if ($data['password'] !== $data['confirm']) {
            return new JsonResponse([
                'error' => ['password' => 'password_value_mismatch'],
            ], 400);
        }

        $user->setPlainPassword($data['password']);
        $this->userManager->activateUser($user);

        $this->om->persist($user);
        $this->om->flush();

        return new JsonResponse(null, 201);
    }

    /**
     * @Route("/validate/email/{hash}", name="claro_security_validate_email", methods={"GET"})
     */
    public function validateEmailAction(string $hash): RedirectResponse
    {
        if (!empty($hash)) {
            $foundAndValidated = $this->userManager->validateEmailHash($hash);
            if (!$foundAndValidated) {
                throw new NotFoundHttpException('User not found.');
            }

            $this->eventDispatcher->dispatch(SecurityEvents::VALIDATE_EMAIL, ValidateEmailEvent::class, [$this->userManager->getByEmailValidationHash($hash)]);
        }

        return new RedirectResponse(
            $this->routingHelper->indexPath()
        );
    }

    /**
     * @Route("/send/email/validation", name="claro_security_validate_email_send", options={"expose"=true})
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=false})
     */
    public function sendEmailValidationAction(User $currentUser): JsonResponse
    {
        $this->mailManager->sendValidateEmail($currentUser);

        return new JsonResponse(null, 204);
    }
}
