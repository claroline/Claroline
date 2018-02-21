<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Contact;

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\API\Serializer\Contact\ContactSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\ContactManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @ApiMeta(class="Claroline\CoreBundle\Entity\Contact\Contact")
 * @EXT\Route("/contact")
 */
class ContactController extends AbstractCrudController
{
    /* var ApiManager */
    private $apiManager;
    /* var ContactManager */
    private $contactManager;
    /* var ContactSerializer */
    private $contactSerializer;
    /* var FinderProvider */
    protected $finder;
    /* var TokenStorageInterface */
    private $tokenStorage;
    /* var UserSerializer */
    private $userSerializer;

    /**
     * ContactController constructor.
     *
     * @DI\InjectParams({
     *     "apiManager"        = @DI\Inject("claroline.manager.api_manager"),
     *     "contactManager"    = @DI\Inject("claroline.manager.contact_manager"),
     *     "contactSerializer" = @DI\Inject("claroline.serializer.contact"),
     *     "finder"            = @DI\Inject("claroline.api.finder"),
     *     "tokenStorage"      = @DI\Inject("security.token_storage"),
     *     "userSerializer"    = @DI\Inject("claroline.serializer.user")
     * })
     *
     * @param ApiManager            $apiManager
     * @param ContactManager        $contactManager
     * @param ContactSerializer     $contactSerializer
     * @param FinderProvider        $finder
     * @param TokenStorageInterface $tokenStorage
     * @param UserSerializer        $userSerializer
     */
    public function __construct(
        ApiManager $apiManager,
        ContactManager $contactManager,
        ContactSerializer $contactSerializer,
        FinderProvider $finder,
        TokenStorageInterface $tokenStorage,
        UserSerializer $userSerializer
    ) {
        $this->apiManager = $apiManager;
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

    public function getDefaultHiddenFilters()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return [
            'user' => 'anon.' !== $user ? $user->getId() : null,
        ];
    }

    /**
     * @EXT\Route(
     *     "/contacts/create",
     *     name="apiv2_contacts_create"
     * )
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User $currentUser
     *
     * @return JsonResponse
     */
    public function contactsCreateAction(User $currentUser)
    {
        $serializedContacts = [];
        $users = $this->apiManager->getParametersByUuid('ids', 'Claroline\CoreBundle\Entity\User');
        $contacts = $this->contactManager->createContacts($currentUser, $users);

        foreach ($contacts as $contact) {
            $serializedContacts[] = $this->contactSerializer->serialize($contact);
        }

        return new JsonResponse($serializedContacts);
    }

    /**
     * @EXT\Route(
     *     "/visible/users/list/{picker}",
     *     name="apiv2_visible_users_list"
     * )
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $currentUser
     * @param Request $request
     * @param int     $picker
     *
     * @return JsonResponse
     */
    public function visibleUsersListAction(User $currentUser, Request $request, $picker = 0)
    {
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['contactable'] = true;

        if (intval($picker)) {
            $params['hiddenFilters']['blacklist'] = array_map(function (User $user) {
                return $user->getUuid();
            }, $this->contactManager->getContactsUser($currentUser));
        }
        $data = $this->finder->search('Claroline\CoreBundle\Entity\User', $params);

        return new JsonResponse($data, 200);
    }
}
