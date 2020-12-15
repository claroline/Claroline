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

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\MessageBundle\Entity\Contact\Contact;
use Claroline\MessageBundle\Manager\ContactManager;
use Claroline\MessageBundle\Serializer\Contact\ContactSerializer;
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
    /** @var ContactSerializer */
    private $contactSerializer;
    /** @var FinderProvider */
    protected $finder;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var UserSerializer */
    private $userSerializer;

    /**
     * ContactController constructor.
     *
     * @param ContactManager        $contactManager
     * @param ContactSerializer     $contactSerializer
     * @param FinderProvider        $finder
     * @param TokenStorageInterface $tokenStorage
     * @param UserSerializer        $userSerializer
     */
    public function __construct(
        ContactManager $contactManager,
        ContactSerializer $contactSerializer,
        FinderProvider $finder,
        TokenStorageInterface $tokenStorage,
        UserSerializer $userSerializer
    ) {
        $this->contactManager = $contactManager;
        $this->contactSerializer = $contactSerializer;
        $this->finder = $finder;
        $this->tokenStorage = $tokenStorage;
        $this->userSerializer = $userSerializer;
    }

    public function getName()
    {
        return 'contact';
    }

    protected function getDefaultHiddenFilters()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return [
            'user' => 'anon.' !== $user ? $user->getId() : null,
        ];
    }

    /**
     * @Route(
     *     "/contacts/create",
     *     name="apiv2_contacts_create"
     * )
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $currentUser
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function contactsCreateAction(User $currentUser, Request $request)
    {
        $serializedContacts = [];
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');
        $contacts = $this->contactManager->createContacts($currentUser, $users);

        foreach ($contacts as $contact) {
            $serializedContacts[] = $this->contactSerializer->serialize($contact);
        }

        return new JsonResponse($serializedContacts);
    }

    public function getClass()
    {
        return Contact::class;
    }
}
