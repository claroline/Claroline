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

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\ClacoFormBundle\Repository\CategoryRepository")
 * @ORM\Table(name="claro_clacoformbundle_category")
 */
class Category
{
    use Uuid;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="category_name")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\ClacoForm",
     *     inversedBy="categories"
     * )
     * @ORM\JoinColumn(name="claco_form_id", nullable=false, onDelete="CASCADE")
     */
    protected $clacoForm;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinTable(name="claro_clacoformbundle_category_manager")
     */
    protected $managers;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    protected $details;

    public function __construct()
    {
        $this->refreshUuid();
        $this->managers = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getClacoForm()
    {
        return $this->clacoForm;
    }

    public function setClacoForm(ClacoForm $clacoForm)
    {
        $this->clacoForm = $clacoForm;
    }

    /**
     * @return User[]
     */
    public function getManagers()
    {
        return $this->managers->toArray();
    }

    public function addManager(User $manager)
    {
        if (!$this->managers->contains($manager)) {
            $this->managers->add($manager);
        }

        return $this;
    }

    public function removeManager(User $manager)
    {
        if ($this->managers->contains($manager)) {
            $this->managers->removeElement($manager);
        }

        return $this;
    }

    public function emptyManagers()
    {
        $this->managers->clear();
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function getColor()
    {
        return !is_null($this->details) && isset($this->details['color']) ? $this->details['color'] : null;
    }

    public function setColor($color)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['color'] = $color;
    }

    public function getNotifyAddition()
    {
        return !is_null($this->details) && isset($this->details['notify_addition']) ? $this->details['notify_addition'] : true;
    }

    public function setNotifyAddition($notifyAddition)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['notify_addition'] = $notifyAddition;
    }

    public function getNotifyEdition()
    {
        return !is_null($this->details) && isset($this->details['notify_edition']) ? $this->details['notify_edition'] : true;
    }

    public function setNotifyEdition($notifyEdition)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['notify_edition'] = $notifyEdition;
    }

    public function getNotifyRemoval()
    {
        return !is_null($this->details) && isset($this->details['notify_removal']) ? $this->details['notify_removal'] : true;
    }

    public function setNotifyRemoval($notifyRemoval)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['notify_removal'] = $notifyRemoval;
    }

    public function getNotifyPendingComment()
    {
        return !is_null($this->details) && isset($this->details['notify_pending_comment']) ?
            $this->details['notify_pending_comment'] :
            true;
    }

    public function setNotifyPendingComment($notifyPendingComment)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['notify_pending_comment'] = $notifyPendingComment;
    }
}
