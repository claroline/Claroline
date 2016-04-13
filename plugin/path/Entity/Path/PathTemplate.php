<?php

namespace Innova\PathBundle\Entity\Path;

use Doctrine\ORM\Mapping as ORM;

/**
 * PathTemplate.
 *
 * @ORM\Table(name="innova_pathtemplate")
 * @ORM\Entity
 */
class PathTemplate extends AbstractPath implements \JsonSerializable
{
    /**
     * Unique identifier of the template.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'structure' => json_decode($this->structure),
        );
    }
}
