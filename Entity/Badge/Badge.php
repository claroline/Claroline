<?php

namespace Claroline\CoreBundle\Entity\Badge;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Badge
 *
 * @ORM\Table(name="claro_badge")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Badge\BadgeRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Badge
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
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    protected $version;

    /**
     * @var boolean
     *
     * @ORM\Column(name="automatic_award", type="boolean", nullable=true)
     */
    protected $automaticAward;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", nullable=false)
     */
    protected $imagePath;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expired_at", type="datetime", nullable=true)
     */
    protected $expiredAt;

    /**
     * @var UploadedFile
     *
     * @Assert\File
     */
    protected $file;

    /**
     * @var string
     */
    protected $olfFileName;

    /**
     * @var ArrayCollection|UserBadge[]
     *
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Badge\UserBadge", mappedBy="badge", cascade={"all"})
     */
    protected $userBadges;

    /**
     * @var ArrayCollection|BadgeClaim[]
     *
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Badge\BadgeClaim", mappedBy="badge", cascade={"all"})
     */
    protected $badgeClaims;

    /**
     * @var ArrayCollection|BadgeRule[]
     *
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Badge\BadgeRule", mappedBy="badge", cascade={"persist"})
     */
    protected $badgeRules;

    /**
     * @var ArrayCollection|BadgeTranslation[]
     *
     * @ORM\OneToMany(
     *   targetEntity="Claroline\CoreBundle\Entity\Badge\BadgeTranslation",
     *   mappedBy="badge",
     *   cascade={"all"}
     * )
     */
    protected $translations;

    /**
     * @var null
     */
    protected $locale = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->userBadges   = new ArrayCollection();
        $this->badgeRules   = new ArrayCollection();
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getUsers()
    {
        $users = new ArrayCollection();

        foreach ($this->userBadges as $userBadge) {
            $users[] = $userBadge->getUser();
        }

        return $users;
    }

    /**
     * @param User[]|ArrayCollection $users
     *
     * @return $this
     */
    public function setUsers($users)
    {
        $this->userBadges->clear();

        foreach ($users as $user) {
            $userBagde = new UserBadge();

            $userBagde
                ->setBadge($this)
                ->setUser($user);

            $this->addUserBadge($userBagde);
        }

        return $this;
    }

    /**
     * @return UserBadge[]|ArrayCollection
     */
    public function getUserBadges()
    {
        return $this->userBadges;
    }

    /**
     * @param UserBadge $userBadge
     *
     * @return Badge
     */
    public function addUserBadge(UserBadge $userBadge)
    {
        if (!$this->userBadges->contains($userBadge)) {
            $this->userBadges[] = $userBadge;
        }

        return $this;
    }

    /**
     * @param UserBadge $userBadge
     *
     * @return bool
     */
    public function removeUserBadge(UserBadge $userBadge)
    {
        return $this->userBadges->removeElement($userBadge);
    }

    /**
     * @return BadgeTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $locale
     *
     * @throws \InvalidArgumentException
     * @return BadgeTranslation|null
     */
    public function getTranslationForLocale($locale)
    {
        foreach ($this->getTranslations() as $translation) {
            if ($locale === $translation->getLocale()) {
                return $translation;
            }
        }

        throw new \InvalidArgumentException(sprintf('Unknown translation for locale %s.', $locale));
    }

    /**
     * @return BadgeTranslation|null
     */
    public function getFrTranslation()
    {
        return $this->getTranslationForLocale('fr');
    }

    /**
     * @return BadgeTranslation|null
     */
    public function getEnTranslation()
    {
        return $this->getTranslationForLocale('en');
    }

    /**
     * @param  BadgeTranslation $translation
     * @return Badge
     */
    public function addTranslation(BadgeTranslation $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setBadge($this);
        }

        return $this;
    }

    /**
     * @param  BadgeTranslation $translation
     * @return Badge
     */
    public function removeTranslation(BadgeTranslation $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * @param \DateTime $expiredAt
     *
     * @return Badge
     */
    public function setExpiredAt($expiredAt)
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiredAt()
    {
        return $this->expiredAt;
    }

    /**
     * @param int $id
     *
     * @return Badge
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
     * @param null|string $locale
     *
     * @return Badge
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     * @return null|string
     */
    public function getLocale()
    {
        if (null === $this->locale) {
            throw new \InvalidArgumentException('No locale setted for badge translation.');
        }

        return $this->locale;
    }

    /**
     * @param string $imagePath
     *
     * @return Badge
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * @param int $version
     *
     * @return Badge
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $locale
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getName($locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        return $this->getTranslationForLocale($locale)->getName();
    }

    /**
     * @param string $locale
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getDescription($locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        return $this->getTranslationForLocale($locale)->getDescription();
    }

    /**
     * @param string $locale
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getSlug($locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        return $this->getTranslationForLocale($locale)->getSlug();
    }

    /**
     * @param string $locale
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getCriteria($locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        return $this->getTranslationForLocale($locale)->getCriteria();
    }

    /**
     * @param boolean $automaticAward
     *
     * @return Badge
     */
    public function setAutomaticAward($automaticAward)
    {
        $this->automaticAward = $automaticAward;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAutomaticAward()
    {
        return $this->automaticAward;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Badge\BadgeRule[]|\Doctrine\Common\Collections\ArrayCollection $badgeRules
     *
     * @return Badge
     */
    public function setBadgeRules($badgeRules)
    {
        foreach ($badgeRules as $rule) {
            $rule->setBadge($this);
        }

        $this->badgeRules = $badgeRules;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Badge\BadgeRule[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getBadgeRules()
    {
        return $this->badgeRules;
    }

    /**
     * @param UploadedFile $file
     *
     * @return Badge
     */
    public function setFile(UploadedFile $file)
    {
        $newFileName = $file->getClientOriginalName();

        if ($this->imagePath !== $newFileName) {
            $this->olfFileName = $this->imagePath;
            $this->imagePath   = null;
        }
        $this->file = $file;

        return $this;
    }

    /**
     * @return Badge
     */
    public function resetFile()
    {
        $this->imagePath = $this->olfFileName;
        $this->file = null;

        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }
    /**
     * @return null|string
     */
    public function getAbsolutePath()
    {
        return (null === $this->imagePath) ? null : $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->imagePath;
    }

    /**
     * @return null|string
     */
    public function getWebPath()
    {
        return (null === $this->imagePath) ? null : $this->getUploadDir() . DIRECTORY_SEPARATOR . $this->imagePath;
    }

    /**
     * @throws \Exception
     * @return string
     */
    protected function getUploadRootDir()
    {
        $ds = DIRECTORY_SEPARATOR;

        $uploadRootDir         = sprintf('%s%s..%s..%s..%s..%s..%s..%s..%sweb%s%s', __DIR__, $ds, $ds, $ds, $ds, $ds, $ds, $ds, $ds, $ds, $this->getUploadDir());
        $realpathUploadRootDir = realpath($uploadRootDir);

        if (false === $realpathUploadRootDir) {
            throw new \Exception(sprintf("Invalid upload root dir '%s'for uploading badge images.", $uploadRootDir));
        }

        return $realpathUploadRootDir;
    }

    /**
     * @return string
     */
    protected function getUploadDir()
    {
        return sprintf("uploads%sbadges", DIRECTORY_SEPARATOR);
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        if (null !== $this->file) {
            $this->imagePath = $this->file->getClientOriginalName();
        }
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        if (null !== $this->file) {
            $this->imagePath = $this->file->getClientOriginalName();
        }
    }

    /**
     * @ORM\PostUpdate()
     */
    public function postUpdate()
    {
        if (null === $this->file) {
            return;
        }

        $this->file->move($this->getUploadRootDir(), $this->imagePath);

        if (null !== $this->olfFileName) {
            unlink($this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->olfFileName);
            $this->olfFileName = null;
        }

        $this->file = null;
    }

    /**
     * @ORM\PostPersist()
     */
    public function postPersist()
    {
        if (null === $this->file) {
            return;
        }

        $this->file->move($this->getUploadRootDir(), $this->imagePath);

        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        $filePath = $this->getAbsolutePath();
        if (null !== $filePath) {
            unlink($filePath);
        }
    }
}
