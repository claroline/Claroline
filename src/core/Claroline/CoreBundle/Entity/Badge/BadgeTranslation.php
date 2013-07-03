<?php

namespace Claroline\CoreBundle\Entity\Badge;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_badge_translation",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="lookup_unique_idx", columns={
 *         "locale", "badge_id", "field"
 *     })}
 * )
 */
class BadgeTranslation extends AbstractPersonalTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Badge", inversedBy="translations")
     * @ORM\JoinColumn(name="badge_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $badge;
}
