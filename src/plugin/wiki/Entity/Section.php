<?php

namespace Icap\WikiBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="icap__wiki_section")
 * @ORM\Entity(repositoryClass="Icap\WikiBundle\Repository\SectionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Section
{
    use Uuid;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $visible = true;

    /**
     * @ORM\Column(type="datetime", name="creation_date")
     * @Gedmo\Timestampable(on="create")
     */
    protected $creationDate;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @ORM\OneToOne(targetEntity="Icap\WikiBundle\Entity\Contribution", cascade={"all"})
     * @ORM\JoinColumn(name="active_contribution_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $activeContribution;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\WikiBundle\Entity\Wiki")
     * @ORM\JoinColumn(name="wiki_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $wiki;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $deleted = false;

    /**
     * @ORM\Column(type="datetime", name="deletion_date", nullable=true)
     */
    protected $deletionDate;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $left;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $level;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $right;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(type="integer", nullable=true)
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Icap\WikiBundle\Entity\Section")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * Variable used to define if section has moved during update, in order to invalidate client data.
     *
     * @var bool
     */
    private $moved = false;

    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @param mixed $visible
     *
     * @return $this
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Returns the resource creation date.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param $creationDate
     *
     * @return $this
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Set author.
     *
     * @param User $author
     *
     * @return $this
     */
    public function setAuthor(User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set contribution.
     *
     * @param \Icap\WikiBundle\Entity\Contribution $contribution
     *
     * @return section
     */
    public function setActiveContribution(Contribution $contribution)
    {
        $this->activeContribution = $contribution;

        return $this;
    }

    /**
     * Get contribution.
     *
     * @return \Icap\WikiBundle\Entity\Contribution
     */
    public function getActiveContribution()
    {
        return $this->activeContribution;
    }

    /**
     * Set wiki.
     *
     * @param \Icap\WikiBundle\Entity\Wiki $wiki
     *
     * @return section
     */
    public function setWiki(Wiki $wiki)
    {
        $this->wiki = $wiki;

        return $this;
    }

    /**
     * Get wiki.
     *
     * @return \Icap\WikiBundle\Entity\Wiki
     */
    public function getWiki()
    {
        return $this->wiki;
    }

    /**
     * @return bool
     */
    public function getDeleted()
    {
        return (null === $this->deleted) ? false : $this->deleted;
    }

    /**
     * @param bool $deleted
     *
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Returns the resource creation date.
     *
     * @return \DateTime
     */
    public function getDeletionDate()
    {
        return $this->deletionDate;
    }

    /**
     * Returns the resource creation date.
     *
     * @param $deletionDate
     *
     * @return $this
     */
    public function setDeletionDate($deletionDate)
    {
        $this->deletionDate = $deletionDate;

        return $this;
    }

    /**
     * @param mixed
     *
     * @return $this
     */
    public function setLeft($left)
    {
        $this->left = $left;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @param mixed $level
     *
     * @return $this
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $right
     *
     * @return $this
     */
    public function setRight($right)
    {
        $this->right = $right;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @param mixed $root
     *
     * @return $this
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param int $position
     *
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = intval($position);

        return $this;
    }

    /**
     * Set parent.
     *
     * @param \Icap\WikiBundle\Entity\Section $section
     *
     * @return section
     */
    public function setParent(Section $section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return \Icap\WikiBundle\Entity\Section
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return bool
     */
    public function hasMoved()
    {
        return $this->moved;
    }

    /**
     * @param bool $moved
     *
     * @return $this
     */
    public function setMoved($moved)
    {
        $this->moved = $moved;

        return $this;
    }

    /**
     * Test if section is rootsection.
     *
     * @return bool
     */
    public function isRoot()
    {
        return 0 === $this->getLevel();
    }

    /**
     * Test if section has children.
     *
     * @return bool
     */
    public function hasChildren()
    {
        $difference = $this->getRight() - $this->getLeft();

        return $difference > 1;
    }

    /**
     * Test if section has to be moved.
     *
     * @return bool
     */
    public function checkMoveSection()
    {
        return !$this->isRoot() && $this->getPosition() !== $this->getId();
    }

    /**
     * Creates a new non persisted contribution and sets it as section's active contribution.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function setNewActiveContributionToSection(User $user = null)
    {
        $oldActiveContribution = $this->getActiveContribution();
        $newActiveContribution = new Contribution();
        $newActiveContribution->setSection($this);
        if (null === $oldActiveContribution) {
            if (null === $user) {
                $user = $this->getAuthor();
            }
        } else {
            if (null === $user) {
                $user = $oldActiveContribution->getContributor();
            }
            if (null === $user) {
                $user = $this->getAuthor();
            }
            $newActiveContribution->setTitle($oldActiveContribution->getTitle());
            $newActiveContribution->setText($oldActiveContribution->getText());
        }
        $newActiveContribution->setContributor($user);
        $this->setActiveContribution($newActiveContribution);
    }

    /**
     * Returns the changeSet data when a section has been moved.
     *
     * @param int $oldLeft
     *
     * @return array $changeSet
     */
    public function getMoveEventChangeSet(Section $oldParent, $oldLeft, Section $newParent)
    {
        /* Create change set for move log event
         * If section's parent has changed, return old and new parent
         * Otherwise return old and new left to mark move up or down in the same parent
         */
        $newLeft = $this->getLeft();
        $changeSet = [
            'parentId' => [$oldParent->getId(), $newParent->getId()],
            'parentName' => [$oldParent->getActiveContribution()->getTitle(), $newParent->getActiveContribution()->getTitle()],
            'isParentRoot' => [$oldParent->isRoot(), $newParent->isRoot()],
            'left' => [$oldLeft, $newLeft],
        ];

        return $changeSet;
    }

    /**
     * Returns the changeSet data when a section has been moved.
     *
     * @param Contribution $oldActiveContribution
     */
    public function isActiveContributionChanged($oldActiveContribution)
    {
        $activeContribution = $this->getActiveContribution();

        $oldText = trim($oldActiveContribution->getText());
        $oldTitle = trim($oldActiveContribution->getTitle());

        $newTitle = trim($activeContribution->getTitle());
        $newText = trim($activeContribution->getText());

        if ($oldText === $newText && $oldTitle === $newTitle) {
            unset($activeContribution);
            $this->setActiveContribution($oldActiveContribution);
            $this->setHasChangedActiveContribution(false);
        }
    }

    /**
     * @ORM\PostPersist
     */
    public function createActiveContribution(LifecycleEventArgs $event)
    {
        if (null === $this->getActiveContribution()) {
            $em = $event->getEntityManager();
            $activeContribution = new Contribution();
            $activeContribution->setSection($this);
            $activeContribution->setContributor($this->getAuthor());
            $this->setActiveContribution($activeContribution);

            $em->persist($activeContribution);
            $em->flush();
        }
    }
}
