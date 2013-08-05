<?php

namespace Claroline\AnnouncementBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_announcement_aggregate")
 */
class AnnouncementAggregate extends AbstractResource
{
}