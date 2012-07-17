<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_plugin")
 * @UniqueEntity("bundleFQCN")
 */
class Plugin
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="bundle_fqcn", type="string", length="255")
     * @Assert\NotBlank()
     * @Assert\MaxLength(255)
     */
    protected $bundleFQCN;

    /**
     * @ORM\Column(name="vendor_name", type="string", length="50")
     * @Assert\NotBlank()
     * @Assert\MaxLength(50)
     */
    protected $vendorName;

    /**
     * @ORM\Column(name="short_name", type="string", length="50")
     * @Assert\NotBlank()
     * @Assert\MaxLength(50)
     */
    protected $bundleName;

    /**
     * @ORM\Column(name="name_translation_key", type="string", length="255")
     * @Assert\NotBlank()
     * @Assert\MaxLength(255)
     */
    protected $nameTranslationKey;

    /**
     * @ORM\Column(name="description", type="string", length="255")
     * @Assert\NotBlank()
     * @Assert\MaxLength(255)
     */
    protected $descriptionTranslationKey;

    public function getId()
    {
        return $this->bundleFQCN;
    }

    public function getGeneratedId()
    {
        return $this->id;
    }

    public function getBundleFQCN()
    {
        return $this->bundleFQCN;
    }

    public function setBundleFQCN($fqcn)
    {
        $this->bundleFQCN = $fqcn;
    }

    public function getVendorName()
    {
        return $this->vendorName;
    }

    public function setVendorName($name)
    {
        $this->vendorName = $name;
    }

    public function getBundleName()
    {
        return $this->bundleName;
    }

    public function setBundleName($name)
    {
        $this->bundleName = $name;
    }

    public function getNameTranslationKey()
    {
        return $this->nameTranslationKey;
    }

    public function setNameTranslationKey($key)
    {
        return $this->nameTranslationKey = $key;
    }

    public function getDescriptionTranslationKey()
    {
        return $this->descriptionTranslationKey;
    }

    public function setDescriptionTranslationKey($key)
    {
        $this->descriptionTranslationKey = $key;
    }
}