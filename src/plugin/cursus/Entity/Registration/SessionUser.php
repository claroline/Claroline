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

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Finder\Registration\SessionUserType;
use Claroline\CursusBundle\Repository\Registration\SessionUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_cursusbundle_course_session_user')]
#[ORM\UniqueConstraint(name: 'training_session_unique_user', columns: ['session_id', 'user_id'])]
#[ORM\Entity(repositoryClass: SessionUserRepository::class)]
#[CrudEntity(finderClass: SessionUserType::class)]
class SessionUser extends AbstractUserRegistration
{
    #[ORM\JoinColumn(name: 'course_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Course::class)]
    private ?Course $course = null;

    #[ORM\JoinColumn(name: 'session_id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Session::class)]
    private ?Session $session = null;

    /**
     * @var Collection<int, FieldFacetValue>
     */
    #[ORM\JoinTable(name: 'claro_cursusbundle_session_user_values')]
    #[ORM\JoinColumn(name: 'registration_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'value_id', referencedColumnName: 'id', unique: true, onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: FieldFacetValue::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $facetValues;

    public function __construct()
    {
        parent::__construct();

        $this->facetValues = new ArrayCollection();
    }

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function setCourse(Course $course): void
    {
        $this->course = $course;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): void
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
