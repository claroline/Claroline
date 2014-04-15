<?php

namespace Icap\PortfolioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="icap__portfolio",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="portfolio_slug_unique_idx", columns={"slug"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="Icap\PortfolioBundle\Repository\PortfolioRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Portfolio
{
    const VISIBILITY_NOBODY              = 0;
    const VISIBILITY_NOBODY_LABEL        = 'visibile_to_me';
    const VISIBILITY_USER                = 1;
    const VISIBILITY_USER_LABEL          = 'visible_for_some_users';
    const VISIBILITY_PLATFORM_USER       = 2;
    const VISIBILITY_PLATFORM_USER_LABEL = 'visible_for_platform_user';
    const VISIBILITY_EVERYBODY           = 3;
    const VISIBILITY_EVERYBODY_LABEL     = 'visible_for_everybody';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $name
     *
     * @ORM\Column(type="string", length=128, nullable=false)
     * @Assert\Length(max = "128")
     */
    protected $name;

    /**
     * @var string $slug
     *
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(type="string", length=128, unique=true, nullable=false)
     */
    protected $slug;

    /**
     * @var bool
     *
     * @ORM\Column(type="integer", name="visibility", nullable=false)
     */
    protected $visibility = 0;

    /**
     * @var \Claroline\CoreBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="PortfolioUser", mappedBy="portfolio", cascade={"all"})
     */
    protected $portfolioUsers;

    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var datetime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @param int $id
     *
     * @return Portfolio
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
     * @param string $name
     *
     * @return Portfolio
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
     * @param boolean $visibility
     *
     * @return Portfolio
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @return array
     */
    public static function getVisibilityLabels()
    {
        return array(
            self::VISIBILITY_NOBODY        => self::VISIBILITY_NOBODY_LABEL,
            self::VISIBILITY_USER          => self::VISIBILITY_USER_LABEL,
            self::VISIBILITY_PLATFORM_USER => self::VISIBILITY_PLATFORM_USER_LABEL,
            self::VISIBILITY_EVERYBODY     => self::VISIBILITY_EVERYBODY_LABEL
        );
    }

    /**
     * @return mixed
     */
    public function getVisibilityLabel()
    {
        $visibilityLabels = self::getVisibilityLabels();
        return $visibilityLabels[$this->getVisibility()];
    }

    /**
     * @param PortfolioUser[] $portfolioUsers
     *
     * @return Portfolio
     */
    public function setPortfolioUsers($portfolioUsers)
    {
        foreach ($portfolioUsers as $portfolioUser) {
            $portfolioUser->setPortfolio($this);
        }

        $this->portfolioUsers = $portfolioUsers;

        return $this;
    }

    /**
     * @return PortfolioUser[]
     */
    public function getPortfolioUsers()
    {
        return $this->portfolioUsers;
    }

    /**
     * @param string $slug
     *
     * @return Portfolio
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

    /**
     * @return \Icap\PortfolioBundle\Entity\datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \Icap\PortfolioBundle\Entity\datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return Portfolio
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}