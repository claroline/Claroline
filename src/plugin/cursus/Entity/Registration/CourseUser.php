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
use Claroline\CursusBundle\Entity\Course;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_cursusbundle_course_course_user",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="training_session_unique_user", columns={"course_id", "user_id"})
 *     }
 * )
 */
class CourseUser extends AbstractUserRegistration
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\Course")
     * @ORM\JoinColumn(name="course_id", nullable=false, onDelete="CASCADE")
     */
    private ?Course $course = null;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetValue", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinTable(name="claro_cursusbundle_course_user_values",
     *      joinColumns={@ORM\JoinColumn(name="registration_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", unique=true, onDelete="CASCADE")}
     * )
     */
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

    public function setCourse(Course $course)
    {
        $this->course = $course;
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
