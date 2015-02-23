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
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(length=100)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(length=50)
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
     * @ORM\Column(type="json_array")
     */
    private $license;

    /**
     * Unmapped field if "stuff" need to be added to the description
     */
    private $extra;

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

    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    public function getExtra()
    {
        return $this->extra;
    }
}
