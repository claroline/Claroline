<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_forum")
 */
class Forum extends AbstractResource
{
    const VALIDATE_NONE = 'NONE';
    const VALIDATE_PRIOR_ONCE = 'PRIOR_ONCE';
    const VALIDATE_PRIOR_ALL = 'PRIOR_ALL';

    const DISPLAY_TABLE_SM = 'table-sm';
    const DISPLAY_TABLE = 'table';
    const DISPLAY_LIST_SM = 'list-sm';
    const DISPLAY_LIST = 'list';
    const DISPLAY_TILES = 'tiles';
    const DISPLAY_TILES_SM = 'tiles-sm';

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ForumBundle\Entity\Subject",
     *     mappedBy="forum"
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $subjects;

    /**
     * @ORM\Column(type="string")
     */
    protected $validationMode = self::VALIDATE_NONE;

    /**
     * @ORM\Column(type="integer")
     */
    protected $maxComment = 10;

    /**
     * @ORM\Column(type="integer")
     */
    protected $displayMessages = 3;

    /**
     * @ORM\Column(type="string")
     */
    protected $dataListOptions = self::DISPLAY_LIST;

    /**
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $lockDate = null;

    /**
     * @ORM\Column(name="show_overview", type="boolean", options={"default" = 1})
     *
     * @var bool
     */
    private $showOverview = true;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(options={"default"="ASC"})
     *
     * @var string
     */
    private $messageOrder = 'ASC';

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     *
     * @var bool
     */
    private $expandComments = false;

    public function __construct()
    {
        parent::__construct();

        $this->subjects = new ArrayCollection();
        $this->validationMode = self::VALIDATE_NONE;
        $this->dataListOptions = self::DISPLAY_LIST;
    }

    public function getSubjects()
    {
        return $this->subjects;
    }

    public function addSubject(Subject $subject)
    {
        $this->subjects->add($subject);
    }

    public function removeSubject(Subject $subject)
    {
        $this->subjects->removeElement($subject);
    }

    public function setValidationMode($mode)
    {
        $this->validationMode = $mode;
    }

    public function getValidationMode()
    {
        return $this->validationMode;
    }

    public function setMaxComment($max)
    {
        $this->maxComment = $max;
    }

    public function getMaxComment()
    {
        return $this->maxComment;
    }

    public function setDataListOptions($options)
    {
        $this->dataListOptions = $options;
    }

    public function getDataListOptions()
    {
        return $this->dataListOptions;
    }

    public function setLockDate(\DateTimeInterface $date = null)
    {
        $this->lockDate = $date;
    }

    public function getLockDate()
    {
        return $this->lockDate;
    }

    public function setDisplayMessage($count)
    {
        $this->displayMessages = $count;
    }

    public function getDisplayMessages()
    {
        return $this->displayMessages;
    }

    /**
     * Set show overview.
     *
     * @param bool $showOverview
     */
    public function setShowOverview($showOverview)
    {
        $this->showOverview = $showOverview;
    }

    /**
     * Is overview shown ?
     *
     * @return bool
     */
    public function getShowOverview()
    {
        return $this->showOverview;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getMessageOrder(): string
    {
        return $this->messageOrder;
    }

    public function setMessageOrder(string $order)
    {
        $this->messageOrder = $order;
    }

    public function getExpandComments(): bool
    {
        return $this->expandComments;
    }

    public function setExpandComments(bool $expand)
    {
        $this->expandComments = $expand;
    }
}
