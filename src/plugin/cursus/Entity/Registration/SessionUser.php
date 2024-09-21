<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity\Registration;

use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CursusBundle\Entity\Session;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_cursusbundle_course_session_user')]
#[ORM\UniqueConstraint(name: 'training_session_unique_user', columns: ['session_id', 'user_id'])]
#[ORM\Entity]
class SessionUser extends AbstractUserRegistration
{
    #[ORM\JoinColumn(name: 'session_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CursusBundle\Entity\Session::class)]
    private ?Session $session = null;

    #[ORM\JoinTable(name: 'claro_cursusbundle_session_user_values')]
    #[ORM\JoinColumn(name: 'registration_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'value_id', referencedColumnName: 'id', unique: true, onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: \Claroline\CoreBundle\Entity\Facet\FieldFacetValue::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $facetValues;

    public function __construct()
    {
        parent::__construct();

        $this->facetValues = new ArrayCollection();
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    public function getFacetValue(string $fieldId): ?FieldFacetValue
    {
        $found = null;
        foreach ($this->facetValues as $facetValue) {
            if ($facetValue->getFieldFacet()->getUuid() === $fieldId) {
                $found = $facetValue;
                break;
            }
        }

        return $found;
    }

    public function getFacetValues(): Collection
    {
        return $this->facetValues;
    }

    public function addFacetValue(FieldFacetValue $fieldFacetValue): void
    {
        if (!$this->facetValues->contains($fieldFacetValue)) {
            $this->facetValues->add($fieldFacetValue);
        }
    }

    public function removeFacetValue(FieldFacetValue $fieldFacetValue): void
    {
        if ($this->facetValues->contains($fieldFacetValue)) {
            $this->facetValues->removeElement($fieldFacetValue);
        }
    }
}
