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

use Doctrine\DBAL\Types\Types;
use DateTimeInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\HasHomePage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_forum')]
#[ORM\Entity]
class Forum extends AbstractResource
{
    use HasHomePage;

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
     *
     * @var ArrayCollection|Subject[]
     */
    #[ORM\OneToMany(targetEntity: Subject::class, mappedBy: 'forum')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected $subjects;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::STRING)]
    protected $validationMode = self::VALIDATE_NONE;

    /**
     * @var int
     */
    #[ORM\Column(type: Types::INTEGER)]
    protected $displayMessages = 3;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::STRING)]
    protected $dataListOptions = self::DISPLAY_LIST;

    /**
     * @var DateTimeInterface
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected $lockDate = null;

    /**
     * @var string
     */
    #[ORM\Column(options: ['default' => 'ASC'])]
    private $messageOrder = 'ASC';

    /**
     * @var bool
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private $expandComments = false;

    public function __construct()
    {
        parent::__construct();

        $this->subjects = new ArrayCollection();
    }

    public function getSubjects()
    {
        return $this->subjects;
    }

    public function addSubject(Subject $subject): void
    {
        $this->subjects->add($subject);
    }

    public function removeSubject(Subject $subject): void
    {
        $this->subjects->removeElement($subject);
    }

    public function setValidationMode($mode): void
    {
        $this->validationMode = $mode;
    }

    public function getValidationMode(): string
    {
        return $this->validationMode;
    }

    public function setDataListOptions($options): void
    {
        $this->dataListOptions = $options;
    }

    public function getDataListOptions(): string
    {
        return $this->dataListOptions;
    }

    public function setLockDate(DateTimeInterface $date = null): void
    {
        $this->lockDate = $date;
    }

    public function getLockDate(): ?DateTimeInterface
    {
        return $this->lockDate;
    }

    public function setDisplayMessage(int $count): void
    {
        $this->displayMessages = $count;
    }

    public function getDisplayMessages(): ?int
    {
        return $this->displayMessages;
    }

    public function getMessageOrder(): string
    {
        return $this->messageOrder;
    }

    public function setMessageOrder(string $order): void
    {
        $this->messageOrder = $order;
    }

    public function getExpandComments(): bool
    {
        return $this->expandComments;
    }

    public function setExpandComments(bool $expand): void
    {
        $this->expandComments = $expand;
    }
}
