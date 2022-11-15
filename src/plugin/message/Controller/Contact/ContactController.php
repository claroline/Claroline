<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Controller\Contact;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\MessageBundle\Entity\Contact\Contact;
use Claroline\MessageBundle\Manager\ContactManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/contact")
 */
class ContactController extends AbstractCrudController
{
    /** @var ContactManager */
    private $contactManager;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        ContactManager $contactManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->contactManager = $contactManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function getClass(): string
    {
        return Contact::class;
    }

    public function getName(): string
    {
        return 'contact';
    }

    protected function getDefaultHiddenFilters(): array
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return [
            'user' => $user instanceof User ? $user->getId() : null,
        ];
    }

    /**
     * @Route("/contacts/create", name="apiv2_contacts_create")
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=false})
     */
    public function contactsCreateAction(User $currentUser, Request $request): JsonResponse
    {
        $serializedContacts = [];
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');
        $contacts = $this->contactManager->createContacts($currentUser, $users);

        foreach ($contacts as $contact) {
            $serializedContacts[] = $this->serializer->serialize($contact);
        }

        return new JsonResponse($serializedContacts);
    }
}
