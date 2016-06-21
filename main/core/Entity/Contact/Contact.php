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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Table(
 *     name="claro_contact",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="contact_unique_user_contact",
 *             columns={"user_id", "contact_id"}
 *         )
 *     }
 * )
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Contact\ContactRepository")
 * @DoctrineAssert\UniqueEntity({"user", "contact"})
 */
class Contact
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
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="contact_id", onDelete="CASCADE")
     */
    protected $contact;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Contact\Category"
     * )
     * @ORM\JoinTable(name="claro_contact_categories")
     */
    protected $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

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

    public function getContact()
    {
        return $this->contact;
    }

    public function setContact(User $contact)
    {
        $this->contact = $contact;
    }

    public function getCategories()
    {
        return $this->categories->toArray();
    }

    public function getCategoriesCollection()
    {
        return $this->categories;
    }

    public function addCategory(Category $category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category)
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }
}
