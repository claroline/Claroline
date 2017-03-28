<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/8/17
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Form\BaseProfileType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Listener\AuthenticationSuccessListener;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class RegistrationManager.
 *
 * @DI\Service("claroline.manager.registration_manager")
 */
class RegistrationManager
{
    /** @var ObjectManager */
    private $om;

    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;

    /** @var LocaleManager */
    private $localeManager;

    /** @var TranslatorInterface */
    private $translator;

    /** @var TermsOfServiceManager */
    private $termsManager;

    /** @var FacetManager */
    private $facetManager;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var TokenStorage */
    private $tokenStorage;

    /** @var UserManager */
    private $userManager;

    /** @var RoleManager */
    private $roleManager;

    /** @var AuthenticationSuccessListener */
    private $authenticationHandler;

    /**
     * @DI\InjectParams({
     *      "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *      "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *      "localeManager"         = @DI\Inject("claroline.manager.locale_manager"),
     *      "translator"            = @DI\Inject("translator"),
     *      "termsManager"          = @DI\Inject("claroline.common.terms_of_service_manager"),
     *      "facetManager"          = @DI\Inject("claroline.manager.facet_manager"),
     *      "formFactory"           = @DI\Inject("form.factory"),
     *      "tokenStorage"          = @DI\Inject("security.token_storage"),
     *      "userManager"           = @DI\Inject("claroline.manager.user_manager"),
     *      "roleManager"           = @DI\Inject("claroline.manager.role_manager"),
     *      "authenticationHandler" = @DI\Inject("claroline.authentication_handler")
     * })
     *
     * @param ObjectManager                 $om
     * @param PlatformConfigurationHandler  $platformConfigHandler
     * @param LocaleManager                 $localeManager
     * @param TranslatorInterface           $translator
     * @param TermsOfServiceManager         $termsManager
     * @param FacetManager                  $facetManager
     * @param FormFactoryInterface          $formFactory
     * @param TokenStorageInterface         $tokenStorage
     * @param UserManager                   $userManager
     * @param RoleManager                   $roleManager
     * @param AuthenticationSuccessListener $authenticationHandler
     */
    public function __construct(
        ObjectManager $om,
        PlatformConfigurationHandler $platformConfigHandler,
        LocaleManager $localeManager,
        TranslatorInterface $translator,
        TermsOfServiceManager $termsManager,
        FacetManager $facetManager,
        FormFactoryInterface $formFactory,
        TokenStorageInterface $tokenStorage,
        UserManager $userManager,
        RoleManager $roleManager,
        AuthenticationSuccessListener $authenticationHandler
    ) {
        $this->om = $om;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->localeManager = $localeManager;
        $this->termsManager = $termsManager;
        $this->facetManager = $facetManager;
        $this->formFactory = $formFactory;
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->authenticationHandler = $authenticationHandler;
        $this->translator = $translator;
    }

    public function getRegistrationForm($user)
    {
        $facets = $this->facetManager->findForcedRegistrationFacet();
        $form = $this->formFactory->create(
            new BaseProfileType($this->localeManager, $this->termsManager, $this->translator, $facets),
            $user
        );

        return $form;
    }

    public function registerNewUser($user, $form)
    {
        $facets = $this->facetManager->findForcedRegistrationFacet();
        $user = $this->userManager->createUser(
            $user,
            true,
            [PlatformRoles::USER]
        );
        $this->roleManager->setRoleToRoleSubject($user, $this->platformConfigHandler->getParameter('default_role'));
        //then we add the different values for facets.
        foreach ($facets as $facet) {
            foreach ($facet->getPanelFacets() as $panel) {
                foreach ($panel->getFieldsFacet() as $field) {
                    $this->facetManager->setFieldValue($user, $field, $form->get($field->getPrettyName())->getData(), true);
                }
            }
        }
    }

    public function loginUser($user, Request $request)
    {
        //this is bad but I don't know any other way (yet)
        $providerKey = 'main';
        $token = new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
        $this->tokenStorage->setToken($token);
        //a bit hacky I know ~
        return $this->authenticationHandler->onAuthenticationSuccess($request, $token);
    }
}
