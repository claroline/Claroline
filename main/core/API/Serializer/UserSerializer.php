<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.serializer.user")
 * @DI\Tag("claroline.serializer")
 */
class UserSerializer
{
    private $om;
    private $facetManager;
    private $tokenStorage;

    /**
     * ResourceNodeManager constructor.
     *
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "facetManager" = @DI\Inject("claroline.manager.facet_manager"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param ObjectManager         $om
     * @param FacetManager          $facetManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        ObjectManager $om,
        FacetManager $facetManager,
        TokenStorageInterface $tokenStorage)
    {
        $this->om = $om;
        $this->facetManager = $facetManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Serializes a Workspace entity for the JSON api.
     *
     * @param User $user   - the user to serialize
     * @param bool $public
     *
     * @return array - the serialized representation of the workspace
     */
    public function serialize(User $user, $options = [])
    {
        $isPublic = isset($options['public']) ? $options['public'] : false;

        if ($isPublic) {
            return $this->serializePublic($user);
        }

        return [];
    }

    public function serializePublic(User $user)
    {
        $settingsProfile = $this->facetManager->getVisiblePublicPreference();
        $publicUser = [];

        foreach ($settingsProfile as $property => $isViewable) {
            if ($isViewable || $user === $this->tokenStorage->getToken()->getUser()) {
                switch ($property) {
                  case 'baseData':
                      $publicUser['lastName'] = $user->getLastName();
                      $publicUser['firstName'] = $user->getFirstName();
                      $publicUser['fullName'] = $user->getFirstName().' '.$user->getLastName();
                      $publicUser['username'] = $user->getUsername();
                      $publicUser['picture'] = $user->getPicture();
                      $publicUser['description'] = $user->getAdministrativeCode();
                      break;
                  case 'email':
                      $publicUser['mail'] = $user->getMail();
                      break;
                  case 'phone':
                      $publicUser['phone'] = $user->getPhone();
                      break;
                  case 'sendMail':
                      $publicUser['mail'] = $user->getMail();
                      $publicUser['allowSendMail'] = true;
                      break;
                  case 'sendMessage':
                      $publicUser['allowSendMessage'] = true;
                      $publicUser['id'] = $user->getId();
                      break;
              }
            }
        }

        $publicUser['groups'] = [];
        //this should be protected by the visiblePublicPreference but it's not yet the case
        foreach ($user->getGroups() as $group) {
            $publicUser['groups'][] = ['name' => $group->getName(), 'id' => $group->getId()];
        }

        return $publicUser;
    }
}
