<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_bundle")
 */
class Bundle
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api"})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(length=100)
     * @Groups({"api"})
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(length=50)
     * @Groups({"api"})
     */
    private $version;

    /**
     * @var string
     * @ORM\Column(length=50)
     */
    private $type;

    /**
     * @ORM\Column(type="json_array")
     */
    private $authors;

    /**
     * @ORM\Column(type="text", nullable = true)
     */
    private $description;

    /**
     * @ORM\Column(type="text")
     */
    private $targetDir;

    /**
     * @ORM\Column(type="text")
     */
    private $basePath;

    /**
     * @ORM\Column(type="json_array")
     */
    private $license;

    /**
     * @ORM\Column(type="boolean", nullable = false)
     */
    protected $isInstalled = false;

    /**
     * Unmapped field if "stuff" need to be added to the description
     */
    private $extra;

    /**
     * Unmapped field. Is the plugin configurable ? Fetched from claro_plugin table
     * @todo remove claro_plugin table. It's useless now. claro_bundle can replace it and contains more datas
     */
    private $isConfigurable = false;

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setLicense($license)
    {
        $this->license = $license;
    }

    public function getLicense()
    {
        return $this->license;
    }

    public function setAuthors(array $authors)
    {
        $this->authors = $authors;
    }

    public function getAuthors()
    {
        return $this->authors;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setTargetDir($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    public function getTargetDir()
    {
        return $this->targetDir;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function setIsInstalled($boolean)
    {
        $this->isInstalled = $boolean;
    }

    public function isInstalled()
    {
        return $this->isInstalled;
    }

    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function isConfigurable()
    {
        return $this->isConfigurable;
    }

    public function setIsConfigurable($boolean)
    {
        $this->isConfigurable = true;
    }
}
