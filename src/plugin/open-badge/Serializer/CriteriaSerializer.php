<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class CriteriaSerializer
{
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getName()
    {
        return 'open_badge_criteria';
    }

    public function serialize(BadgeClass $badge)
    {
        return [
            'type' => 'Criteria',
            'narrative' => $badge->getCriteria(),
            'id' => $this->router->generate('apiv2_open_badge__criteria', ['badge' => $badge->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }
}
