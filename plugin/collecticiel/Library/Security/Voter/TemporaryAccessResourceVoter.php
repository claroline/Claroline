<?php

namespace Innova\CollecticielBundle\Library\Security\Voter;

use Claroline\CoreBundle\Entity\User;
use Innova\CollecticielBundle\Manager\TemporaryAccessResourceManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * This voter is involved in access decisions for AbstractResource instances based one temporary access.
 *
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class TemporaryAccessResourceVoter implements VoterInterface
{
    /**
     * @var TemporaryAccessResourceManager
     */
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("innova.temporary_access_resource_manager")
     * })
     */
    public function __construct(TemporaryAccessResourceManager $manager)
    {
        $this->manager = $manager;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($object instanceof ResourceCollection) {
            $granted = false;

            $user = null;
            if ($token->getUser() instanceof User) {
                $user = $token->getUser();
            }

            if ($this->manager->hasTemporaryAccessOnSomeResources($user)) {
                foreach ($attributes as $attribute) {
                    if ($this->supportsAttribute($attribute) && $this->supportsClass($object)) {
                        foreach ($object->getResources() as $resource) {
                            if ($this->manager->hasTemporaryAccess($resource, $user)) {
                                $granted = true;
                                break;
                            }
                        }
                    }
                }
            }

            if ($granted) {
                return VoterInterface::ACCESS_GRANTED;
            } else {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function supportsAttribute($attribute)
    {
        return 'open' === strtolower($attribute) || 'export' === strtolower($attribute);
    }

    public function supportsClass($class)
    {
        return $class instanceof ResourceCollection;
    }
}
