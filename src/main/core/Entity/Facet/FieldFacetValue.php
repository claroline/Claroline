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

#[ORM\Table(name: 'claro_field_facet_value')]
#[ORM\Entity(repositoryClass: \Claroline\CoreBundle\Repository\Facet\FieldFacetValueRepository::class)]
class FieldFacetValue extends AbstractFacetValue
{
    /**
     * Used by profile to retrieve the values of a user to fill its profile.
     * This should be done in another entity. This is not used by claco-form.
     *
     *
     * @var User
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\User::class, cascade: ['persist'])]
    private $user;

    /**
     *
     * @var FieldFacet
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\Facet\FieldFacet::class, cascade: ['persist'])]
    private $fieldFacet;

    public function getType(): string
    {
        return $this->fieldFacet->getType();
    }

    public function setFieldFacet(FieldFacet $fieldFacet): void
    {
        $this->fieldFacet = $fieldFacet;
    }

    public function getFieldFacet(): ?FieldFacet
    {
        return $this->fieldFacet;
    }

    public function setUser(?User $user = null): void
    {
        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
