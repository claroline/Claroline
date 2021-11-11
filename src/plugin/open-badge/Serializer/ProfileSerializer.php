<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class ProfileSerializer
{
    public function __construct(RouterInterface $router, ObjectManager $om)
    {
        $this->router = $router;
        $this->om = $om;
    }

    public function getName()
    {
        return 'open_badge_profile';
    }

    public function serialize($el)
    {
        if (is_string($el)) {
            $profile = $this->om->getRepository(User::class)->findOneByUuid($el);

            if (!$profile) {
                $profile = $this->om->getRepository(Organization::class)->findOneByUuid($el);
            }
        } else {
            $profile = $el;
        }

        $data = [
          'type' => 'Profile',
          'id' => $this->router->generate('apiv2_open_badge__profile', ['profile' => $profile->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL),
          'email' => $profile->getEmail(),
        ];

        if ($profile instanceof Organization) {
            $data['name'] = $profile->getName();
            //for mozilla backpack
            $data['url'] = $data['origin'] = $this->router->generate(
              'claro_index',
              [],
              UrlGeneratorInterface::ABSOLUTE_URL
            );
        } else {
            $data['name'] = $profile->getUsername();
            $data['url'] = $this->router->generate(
              'claro_user_profile',
              ['user' => $profile->getUsername()],
              UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $data;
    }
}
