<?php

namespace Claroline\BadgeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_badge_translation",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="badge_translation_unique_idx", columns={
 *         "locale", "badge_id"
 *     })}
 * )
 */
class BadgeTranslation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Badge", inversedBy="translations")
     * @ORM\JoinColumn(name="badge_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $badge;

    /**
     * @var string $locale
     *
     * @ORM\Column(type="string", length=8, nullable=false)
     */
    protected $locale;

    /**
     * @var string $name
     *
     * @ORM\Column(type="string", length=128, unique=true, nullable=false)
     */
    protected $name;

    /**
     * @var string $description
     *
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    protected $description;

    /**
     * @var string $slug
     *
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(type="string", length=128, unique=true, nullable=false)
     */
    protected $slug;

    /**
     * @var string $criteria
     *
     * @ORM\Column(type="text", nullable=false)
     */
    protected $criteria;

    /**
     * @param int $id
     *
     * @return BagdeTranslation
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $badge
     *
     * @return BadgeTranslation
     */
    public function setBadge($badge)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * @return Badge
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * @param string $locale
     *
     * @return BadgeTranslation
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $criteria
     *
     * @return Badge
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * @return string
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param string $description
     *
     * @return Badge
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $name
     *
     * @return Badge
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $slug
     *
     * @return Badge
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
}
