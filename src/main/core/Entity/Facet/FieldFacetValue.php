<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Facet;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Facet\FieldFacetValueRepository")
 * @ORM\Table(name="claro_field_facet_value")
 */
class FieldFacetValue extends AbstractFacetValue
{
    /**
     * Used by profile to retrieve the values of a user to fill its profile.
     * This should be done in another entity. This is not used by claco-form.
     *
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacet",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @var FieldFacet
     */
    private $fieldFacet;

    public function getType(): string
    {
        return $this->fieldFacet->getType();
    }

    public function setFieldFacet(FieldFacet $fieldFacet)
    {
        $this->fieldFacet = $fieldFacet;
    }

    public function getFieldFacet(): ?FieldFacet
    {
        return $this->fieldFacet;
    }

    public function setUser(?User $user = null)
    {
        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
