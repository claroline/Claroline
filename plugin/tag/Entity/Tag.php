<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\TagBundle\Repository\TagRepository")
 * @ORM\Table(name="claro_tagbundle_tag", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique", columns={"tag_name", "user_id"})
 * })
 */
class Tag
{
    use Id;
    use Uuid;

    // meta
    use Description;

    /**
     * The name of the tag.
     *
     * @ORM\Column(name="tag_name")
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $name;

    /**
     * The display color of the tag.
     *
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $color;

    /**
     * The user who created the tag.
     *
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="CASCADE")
     *
     * @var User
     */
    private $user;

    /**
     * Tag constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get color.
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set color.
     *
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param User|null $user
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    }
}
