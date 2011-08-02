<?php

namespace Claroline\PluginBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\PluginBundle\Repository\PluginRepository")
 * @ORM\Table(name="claro_plugin")
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
     */
    protected $bundleFQCN;

    /**
     * @ORM\Column(name="vendor_name", type="string", length="50")
     */
    protected $vendorName;

    /**
     * @ORM\Column(name="short_name", type="string", length="50")
     */
    protected $bundleName;

    /**
     * @ORM\Column(name="name_translation_key", type="string", length="255")
     */
    protected $nameTranslationKey;

    /**
     * @ORM\Column(name="description", type="string", length="255")
     */
    protected $descriptionTranslationKey;

    public function getId()
    {
        return $this->bundleFQCN;
    }

    public function getBundleFQCN()
    {
        return $this->field;
    }

    public function setBundleFQCN($FQCN)
    {
        $this->bundleFQCN = $FQCN;
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