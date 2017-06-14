<?php

/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/7/17
 */

namespace Claroline\CasBundle\Manager;

use Claroline\CasBundle\Entity\CasUser;
use Claroline\CasBundle\Form\CasServerConfigurationType;
use Claroline\CasBundle\Library\Configuration\CasServerConfiguration;
use Claroline\CasBundle\Library\Configuration\CasServerConfigurationFactory;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Authenticator;
use Claroline\CoreBundle\Manager\RegistrationManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CasManager.
 *
 * @DI\Service("claroline.manager.cas_manager")
 */
class CasManager
{
    /** @var ObjectManager */
    private $om;

    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;

    /** @var CasServerConfigurationFactory */
    private $casConfigFactory;

    /** @var UserManager */
    private $userManager;

    /** @var RegistrationManager */
    private $registrationManager;

    /** @var TranslatorInterface */
    private $translator;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var Authenticator */
    private $authenticator;

    /**
     * @DI\InjectParams({
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "platformConfigHandler"  = @DI\Inject("claroline.config.platform_config_handler"),
     *     "casConfigFactory"       = @DI\Inject("claroline.factory.cas_configuration"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "registrationManager"    = @DI\Inject("claroline.manager.registration_manager"),
     *     "translator"             = @DI\Inject("translator"),
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "authenticator"          = @DI\Inject("claroline.authenticator")
     * })
     *
     * @param ObjectManager                 $om
     * @param PlatformConfigurationHandler  $platformConfigHandler
     * @param CasServerConfigurationFactory $casConfigFactory
     * @param UserManager                   $userManager
     * @param RegistrationManager           $registrationManager
     * @param TranslatorInterface           $translator
     * @param FormFactoryInterface          $formFactory
     * @param Authenticator                 $authenticator
     */
    public function __construct(
        ObjectManager $om,
        PlatformConfigurationHandler $platformConfigHandler,
        CasServerConfigurationFactory $casConfigFactory,
        UserManager $userManager,
        RegistrationManager $registrationManager,
        TranslatorInterface $translator,
        FormFactoryInterface $formFactory,
        Authenticator $authenticator
    ) {
        $this->om = $om;
        $this->casConfigFactory = $casConfigFactory;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->userManager = $userManager;
        $this->registrationManager = $registrationManager;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->authenticator = $authenticator;
    }

    public function getConfigurationForm()
    {
        $form = $this->formFactory->create(
            new CasServerConfigurationType(),
            $this->casConfigFactory->getCasConfiguration()
        );

        return $form;
    }

    /**
     * @param $config
     *
     * @return CasServerConfiguration
     */
    public function updateCasParameters(CasServerConfiguration $config)
    {
        return $this->casConfigFactory->setCasConfiguration($config);
    }

    /**
     * @param $user
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getRegistrationForm($user)
    {
        return $this->registrationManager->getRegistrationForm($user);
    }

    /**
     * @param Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createNewAccount(Request $request, $casLogin)
    {
        $user = new User();
        $form = $this->getRegistrationForm($user);
        $form->handleRequest($request);
        $session = $request->getSession();
        if ($form->isValid()) {
            $user = $this->userManager->createUser($user);

            $casUser = new CasUser($casLogin, $user);
            $this->om->persist($casUser);
            $this->om->flush();

            $msg = $this->translator->trans('account_created', [], 'platform');
            $session->getFlashBag()->add('success', $msg);

            if ($this->platformConfigHandler->getParameter('registration_mail_validation')) {
                $msg = $this->translator->trans('please_validate_your_account', [], 'platform');
                $session->getFlashBag()->add('success', $msg);
            }

            return $this->registrationManager->loginUser($user, $request);
        }

        return ['form' => $form->createView()];
    }

    /**
     * @param Request $request
     * @param $casId
     * @param null $username
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function linkAccount(Request $request, $casId, $username = null)
    {
        $verifyPassword = false;
        $password = null;
        if ($username === null) {
            $verifyPassword = true;
            $username = $request->get('_username');
            $password = $request->get('_password');
        }
        $isAuthenticated = $this->authenticator->authenticate($username, $password, $verifyPassword);
        if ($isAuthenticated) {
            $user = $this->userManager->getUserByUsername($username);
            $casUser = new CasUser($casId, $user);
            $this->om->persist($casUser);
            $this->om->flush();

            return $this->registrationManager->loginUser($user, $request);
        } else {
            return ['error' => 'login_error'];
        }
    }

    /**
     * @param $casLogin
     * @param User $user
     *
     * @return CasUser
     */
    public function createCasUser($casLogin, User $user)
    {
        $casUser = new CasUser($casLogin, $user);
        $this->om->persist($casUser);
        $this->om->flush();

        return $casUser;
    }

    public function getCasUsersByCasIds($casIds)
    {
        return $this->om->getRepository('ClarolineCasBundle:CasUser')->findCasUsersByCasIds($casIds);
    }

    public function getCasUsersByCasIdsOrUserIds($casIds, $userIds)
    {
        return $this->om->getRepository('ClarolineCasBundle:CasUser')->findCasUsersByCasIdsOrUserIds($casIds, $userIds);
    }

    /**
     * @param $userId
     */
    public function unlinkAccount($userId)
    {
        $this->om->getRepository("Claroline\CasBundle\Entity\CasUser")->unlinkCasUser($userId);
    }
}
