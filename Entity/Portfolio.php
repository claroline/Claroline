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
    const SHARE_POLICY_NOBODY              = 0;
    const SHARE_POLICY_NOBODY_LABEL        = 'shared_to_nobody';
    const SHARE_POLICY_USER                = 1;
    const SHARE_POLICY_USER_LABEL          = 'shared_with_user';
    const SHARE_POLICY_PLATFORM_USER       = 2;
    const SHARE_POLICY_PLATFORM_USER_LABEL = 'shared_with_platform_user';
    const SHARE_POLICY_EVERYBODY           = 3;
    const SHARE_POLICY_EVERYBODY_LABEL     = 'shared_with_everybody';

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
     * @ORM\Column(type="integer", name="share_policy", nullable=false)
     */
    protected $sharePolicy = 0;

    /**
     * @var \Claroline\CoreBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinTable(name="icap__portfolio_shared_users")
     */
    protected $sharedUsers;

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
     * @param boolean $sharePolicy
     *
     * @return Portfolio
     */
    public function setSharePolicy($sharePolicy)
    {
        $this->sharePolicy = $sharePolicy;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getSharePolicy()
    {
        return $this->sharePolicy;
    }

    /**
     * @return array
     */
    public static function getSharePolicyLabels()
    {
        return array(
            self::SHARE_POLICY_NOBODY        => self::SHARE_POLICY_NOBODY_LABEL,
            self::SHARE_POLICY_USER          => self::SHARE_POLICY_USER_LABEL,
            self::SHARE_POLICY_PLATFORM_USER => self::SHARE_POLICY_PLATFORM_USER_LABEL,
            self::SHARE_POLICY_EVERYBODY     => self::SHARE_POLICY_EVERYBODY_LABEL
        );
    }

    /**
     * @return mixed
     */
    public function getSharePolicyLabel()
    {
        $sharePolicyLabels = self::getSharePolicyLabels();
        return $sharePolicyLabels[$this->getSharePolicy()];
    }

    /**
     * @param mixed $sharedUsers
     *
     * @return Portfolio
     */
    public function setSharedUsers($sharedUsers)
    {
        $this->sharedUsers = $sharedUsers;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSharedUsers()
    {
        return $this->sharedUsers;
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