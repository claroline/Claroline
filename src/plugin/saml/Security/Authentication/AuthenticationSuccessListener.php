<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SamlBundle\Security\Authentication;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Listener\AuthenticationSuccessListener as BaseAuthenticationSuccessListener;
use Claroline\SamlBundle\Manager\IdpManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationSuccessListener extends BaseAuthenticationSuccessListener
{
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var IdpManager */
    private $idpManager;

    public function setObjectManager(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function setIdpManager(IdpManager $idpManager)
    {
        $this->idpManager = $idpManager;
    }

    public function setCrud(Crud $crud)
    {
        $this->crud = $crud;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        /** @var User $user */
        $user = $token->getUser();

        // apply IDP config
        $idpEntityId = $request->getSession()->get('authIdp');
        if ($idpEntityId) {
            // this has been filled by lightSaml AttributeMapper with the SAML response values
            $attributes = $token->getAttributes();
            if ($this->idpManager->checkConditions($idpEntityId, $attributes) && $this->idpManager->checkEmail($idpEntityId, $user->getEmail())) {
                // attach user to the defined organization
                $organization = $this->idpManager->getOrganization($idpEntityId);
                if ($organization && (empty($user->getMainOrganization()) || $organization->getId() !== $user->getMainOrganization()->getId())) {
                    // reset organization if it has changed or has not been set (eg. user has just been created)
                    $this->crud->replace($user, 'mainOrganization', $organization, [Crud::THROW_EXCEPTION, Crud::NO_PERMISSIONS, Options::NO_EMAIL]);
                }

                // attach user to the defined groups
                $groups = $this->idpManager->getGroups($idpEntityId);
                if (!empty($groups)) {
                    $missingGroups = array_filter($groups, function (Group $group) use ($user) {
                        return !$user->hasGroup($group);
                    });

                    if (!empty($missingGroups)) {
                        $this->crud->patch($user, 'group', 'add', $missingGroups, [Crud::THROW_EXCEPTION, Crud::NO_PERMISSIONS, Options::NO_EMAIL]);
                    }
                }
            }
        }

        if (!$user->isEnabled()) {
            $user->enable();
            $this->om->persist($user); // no need to flush user it will be done later
        }

        return parent::onAuthenticationSuccess($request, $token);
    }
}
