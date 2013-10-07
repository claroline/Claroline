<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * This voter is involved in access decisions for WidgetInstances
 *
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class WidgetVoter implements VoterInterface
{
    private $em;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "em"           = @DI\Inject("doctrine.orm.entity_manager"),
     *     "translator"   = @DI\Inject("translator"),
     * })
     */
    public function __construct(EntityManager $em, Translator $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($object instanceof WidgetInstance) {
            return $this->canUpdate($token, $object, $attributes);
        }

        return VoterInterface::ACCESS_ABSTAIN;

    }

    private function canUpdate(TokenInterface $token, $object, $attributes) {
        $roles = $token->getRoles();

        foreach ($roles as $role) {
            $roleStrings[] = $role->getRole();
        }

        if ($object->isAdmin()) {
            if (!in_array('ROLE_ADMIN', $roleStrings)) {
                return VoterInterface::ACCESS_DENIED;
            }
        } else {
            if ($workspace = $object->getWorkspace()) {
                $tools = $this->em
                    ->getRepository('ClarolineCoreBundle:Tool\Tool')
                    ->findDisplayedByRolesAndWorkspace($roleStrings, $workspace);

                foreach ($tools as $tool) {
                    if ($tool->getName() === 'parameters') {

                        return VoterInterface::ACCESS_GRANTED;
                    }
                }
                return VoterInterface::ACCESS_DENIED;
            }

            if ($user = $object->getUser()) {
                if ($user->getId() === $token->getUser()->getId()) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }

            return VoterInterface::ACCESS_DENIED;
        }
    }

    public function supportsAttribute($attribute)
    {
        return true;
    }

    public function supportsClass($class)
    {
        return true;
    }
}