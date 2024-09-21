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

use Claroline\CoreBundle\Repository\Facet\FieldFacetValueRepository;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_field_facet_value')]
#[ORM\Entity(repositoryClass: FieldFacetValueRepository::class)]
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
    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    private ?User $user = null;

    /**
     *
     * @var FieldFacet
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE', nullable: false)]
    #[ORM\ManyToOne(targetEntity: FieldFacet::class, cascade: ['persist'])]
    private ?FieldFacet $fieldFacet = null;

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
