<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\ClacoFormBundle\Repository\FieldRepository")
 * @ORM\Table(
 *     name="claro_clacoformbundle_field",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="field_unique_name", columns={"claco_form_id", "field_facet_id"})
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"clacoForm", "fieldFacet"})
 */
class Field
{
    use Id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\ClacoForm",
     *     inversedBy="fields"
     * )
     * @ORM\JoinColumn(name="claco_form_id", nullable=false, onDelete="CASCADE")
     *
     * @var ClacoForm
     */
    protected $clacoForm;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacet", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="field_facet_id", onDelete="CASCADE")
     *
     * @var FieldFacet
     */
    protected $fieldFacet;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\FieldChoiceCategory",
     *     mappedBy="field"
     * )
     *
     * @var FieldChoiceCategory[]
     */
    protected $fieldChoiceCategories;

    public function __construct()
    {
        $this->fieldFacet = new FieldFacet();
        $this->fieldChoiceCategories = new ArrayCollection();
    }

    /**
     * For retro-compatibility (props were duplicated from FieldFacet).
     */
    public function __call(string $method, array $arguments = [])
    {
        if (method_exists($this->fieldFacet, $method)) {
            return call_user_func_array([$this->fieldFacet, $method], $arguments);
        }

        throw new \BadMethodCallException(sprintf('Undefined method "%s".', $method));
    }

    public function getClacoForm()
    {
        return $this->clacoForm;
    }

    public function setClacoForm(ClacoForm $clacoForm)
    {
        $this->clacoForm = $clacoForm;
    }

    public function getFieldFacet(): FieldFacet
    {
        return $this->fieldFacet;
    }

    public function setFieldFacet(FieldFacet $fieldFacet)
    {
        $this->fieldFacet = $fieldFacet;
    }

    public function getFieldChoiceCategories()
    {
        return $this->fieldChoiceCategories->toArray();
    }
}
