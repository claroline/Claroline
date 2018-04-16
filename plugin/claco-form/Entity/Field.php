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

use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\ClacoFormBundle\Repository\FieldRepository")
 * @ORM\Table(
 *     name="claro_clacoformbundle_field",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="field_unique_name", columns={"claco_form_id", "field_name"})
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"clacoForm", "name"})
 */
class Field
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\ClacoForm",
     *     inversedBy="fields"
     * )
     * @ORM\JoinColumn(name="claco_form_id", nullable=false, onDelete="CASCADE")
     */
    protected $clacoForm;

    /**
     * @ORM\Column(name="field_name")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(name="field_type", type="integer")
     */
    protected $type;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacet")
     * @ORM\JoinColumn(name="field_facet_id", onDelete="CASCADE")
     */
    protected $fieldFacet;

    /**
     * @ORM\Column(name="required", type="boolean")
     */
    protected $required = true;

    /**
     * @ORM\Column(name="is_metadata", type="boolean")
     */
    protected $isMetadata = false;

    /**
     * @ORM\Column(name="locked", type="boolean", options={"default" = 0})
     */
    protected $locked = false;

    /**
     * @ORM\Column(name="locked_edition", type="boolean", options={"default" = 0})
     */
    protected $lockedEditionOnly = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\FieldChoiceCategory",
     *     mappedBy="field"
     * )
     */
    protected $fieldChoiceCategories;

    /**
     * @ORM\Column(name="hidden", type="boolean", options={"default" = 0})
     */
    protected $hidden = false;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $details;

    /**
     * @ORM\Column(name="field_order", type="integer", options={"default" = 1000})
     */
    protected $order = 1000;

    public function __construct()
    {
        $this->fieldChoiceCategories = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getClacoForm()
    {
        return $this->clacoForm;
    }

    public function setClacoForm(ClacoForm $clacoForm)
    {
        $this->clacoForm = $clacoForm;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return FieldFacet
     */
    public function getFieldFacet()
    {
        return $this->fieldFacet;
    }

    public function setFieldFacet(FieldFacet $fieldFacet)
    {
        $this->fieldFacet = $fieldFacet;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function setRequired($required)
    {
        $this->required = $required;
    }

    public function getIsMetadata()
    {
        return $this->isMetadata;
    }

    public function setIsMetadata($isMetadata)
    {
        $this->isMetadata = $isMetadata;
    }

    public function isLocked()
    {
        return $this->locked;
    }

    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    public function getLockedEditionOnly()
    {
        return $this->lockedEditionOnly;
    }

    public function setLockedEditionOnly($lockedEditionOnly)
    {
        $this->lockedEditionOnly = $lockedEditionOnly;
    }

    public function getFieldChoiceCategories()
    {
        return $this->fieldChoiceCategories->toArray();
    }

    public function isHidden()
    {
        return $this->hidden;
    }

    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getFileTypes()
    {
        return !is_null($this->details) && isset($this->details['file_types']) ? $this->details['file_types'] : [];
    }

    public function setFileTypes(array $fileTypes = [])
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['file_types'] = $fileTypes;
    }

    public function getNbFilesMax()
    {
        return !is_null($this->details) && isset($this->details['nb_files_max']) ?
            $this->details['nb_files_max'] :
            1;
    }

    public function setNbFilesMax($nbFilesMax)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['nb_files_max'] = $nbFilesMax;
    }
}
