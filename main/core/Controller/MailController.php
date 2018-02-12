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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\SendMailType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\MailManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ROLE_USER')")
 *
 * Controller of the user's desktop.
 */
class MailController extends Controller
{
    private $formFactory;
    private $request;
    private $mailManager;
    private $router;
    private $tokenStorage;
    private $ch;

    /**
     * @DI\InjectParams({
     *     "formFactory"  = @DI\Inject("form.factory"),
     *     "request"      = @DI\Inject("request"),
     *     "mailManager"  = @DI\Inject("claroline.manager.mail_manager"),
     *     "router"       = @DI\Inject("router"),
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "ch"           = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        Request $request,
        MailManager $mailManager,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        PlatformConfigurationHandler $ch
    ) {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->mailManager = $mailManager;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->ch = $ch;
    }

    /**
     * @EXT\Route(
     *     "/form/{userId}",
     *     name="claro_mail_form"
     * )
     * @EXT\ParamConverter(
     *      "user",
     *      class="ClarolineCoreBundle:User",
     *      options={"id" = "userId", "strictId" = true}
     * )
     * @EXT\Template()
     *
     * Displays the email form.
     *
     * @param User $user
     *
     * @return array
     */
    public function formAction(User $user)
    {
        $mailType = new SendMailType();
        $form = $this->formFactory->create($mailType);

        return ['form' => $form->createView(), 'userId' => $user->getId()];
    }

    /**
     * @EXT\Route(
     *     "/send/{userId}",
     *     name="claro_mail_send"
     * )
     * @EXT\ParamConverter(
     *      "user",
     *      class="ClarolineCoreBundle:User",
     *      options={"id" = "userId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Mail:form.html.twig")
     *
     * Handles the email form submission (sends a email).
     *
     * @param User $user
     *
     * @return Response
     */
    public function sendAction(User $user)
    {
        $mailType = new SendMailType();
        $form = $this->formFactory->create($mailType);
        $form->handleRequest($this->request);
        $sender = $this->tokenStorage->getToken()->getUser();

        if ($form->isValid()) {
            $data = $form->getData();
            $body = $data['content'];
            $this->mailManager->send(
                $data['object'],
                $body,
                [$user],
                $sender
            );
        }

        return new RedirectResponse(
            $this->router->generate(
                'claro_user_profile',
                ['publicUrl' => $user->getPublicUrl()]
           )
        );
    }
}
