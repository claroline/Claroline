<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Contact;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(
 *     name="claro_contact_category",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="contact_unique_user_category",
 *             columns={"user_id", "category_name"}
 *         )
 *     }
 * )
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Contact\CategoryRepository")
 * @DoctrineAssert\UniqueEntity({"user", "name"})
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(name="category_name")
     */
    protected $name;

    /**
     * @ORM\Column(type="integer", name="category_order")
     */
    protected $order;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }
}
