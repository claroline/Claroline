<?php

namespace Icap\BadgeBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Rule\Rulable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Icap\BadgeBundle\Form\Constraints as BadgeAssert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="claro_badge")
 * @ORM\Entity(repositoryClass="Icap\BadgeBundle\Repository\BadgeRepository")
 * @ORM\EntityListeners({"Icap\BadgeBundle\Listener\LocaleSetterListener"})
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @BadgeAssert\AutomaticWithRules
 * @BadgeAssert\HasImage
 * @BadgeAssert\AtLeastOneTranslation
 * @BadgeAssert\CheckExpiringPeriod
 * @ExclusionPolicy("all")
 */
class Badge extends Rulable
{
    use SoftDeleteableEntity;
    use UuidTrait;

    const EXPIRE_PERIOD_DAY = 0;
    const EXPIRE_PERIOD_DAY_LABEL = 'day';
    const EXPIRE_PERIOD_WEEK = 1;
    const EXPIRE_PERIOD_WEEK_LABEL = 'week';
    const EXPIRE_PERIOD_MONTH = 2;
    const EXPIRE_PERIOD_MONTH_LABEL = 'month';
    const EXPIRE_PERIOD_YEAR = 3;
    const EXPIRE_PERIOD_YEAR_LABEL = 'year';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false)
     * @Expose
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value = 0)
     */
    protected $version = 1;

    /**
     * @var bool
     *
     * @ORM\Column(name="automatic_award", type="boolean", nullable=true)
     * @Expose
     */
    protected $automaticAward;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", nullable=false)
     * @Expose
     */
    protected $imagePath;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_expiring", type="boolean", options={"default": 0})
     */
    protected $isExpiring = false;

    /**
     * @var int
     *
     * @ORM\Column(name="expire_duration", type="integer", nullable=true)
     * @Assert\GreaterThan(value = 0)
     */
    protected $expireDuration;

    /**
     * @var int
     *
     * @ORM\Column(name="expire_period", type="smallint", nullable=true)
     */
    protected $expirePeriod;

    /**
     * @var UploadedFile
     *
     * @Assert\Image(
     *     maxSize = "256k",
     *     minWidth = 64,
     *     minHeight = 64
     * )
     */
    protected $file;

    /**
     * @var string
     */
    protected $olfFileName = null;

    /**
     * @var ArrayCollection|UserBadge[]
     *
     * @ORM\OneToMany(targetEntity="Icap\BadgeBundle\Entity\UserBadge", mappedBy="badge", cascade={"all"})
     */
    protected $userBadges;

    /**
     * @var ArrayCollection|BadgeClaim[]
     *
     * @ORM\OneToMany(targetEntity="Icap\BadgeBundle\Entity\BadgeClaim", mappedBy="badge", cascade={"all"})
     */
    protected $badgeClaims;

    /**
     * @var ArrayCollection|BadgeRule[]
     *
     * @ORM\OneToMany(targetEntity="Icap\BadgeBundle\Entity\BadgeRule", mappedBy="associatedBadge", cascade={"persist"})
     * @Expose
     */
    protected $badgeRules;

    /**
     * @var Workspace
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @var ArrayCollection|BadgeTranslation[]
     *
     * @ORM\OneToMany(
     *   targetEntity="Icap\BadgeBundle\Entity\BadgeTranslation",
     *   mappedBy="badge",
     *   cascade={"all"}
     * )
     * @Expose
     */
    protected $translations;

    /**
     * @var null
     */
    protected $locale = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->userBadges = new ArrayCollection();
        $this->badgeRules = new ArrayCollection();
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
     * @param ArrayCollection|BadgeTranslation[] $translations
     *
     * @return Badge
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return BadgeTranslation|null
     */
    public function getTranslationForLocale($locale)
    {
        foreach ($this->getTranslations() as $translation) {
            if ($locale === $translation->getLocale()) {
                return $translation;
            }
        }

        return;
    }

    public function __get($name)
    {
        $translationName = 'Translation';

        if (preg_match(sprintf('/%s$/', $translationName), $name)) {
            $searchedLocale = substr($name, 0, -strlen($translationName));
            $translation = $this->getTranslationForLocale($searchedLocale);

            if (null === $translation) {
                $translation = new BadgeTranslation();
                $translation
                    ->setLocale($searchedLocale)
                    ->setBadge($this);
            }

            return $translation;
        } elseif (preg_match('/Name|Description|Criteria$/', $name, $matches)) {
            //Usefull for badge rule form when wanted frName on a badge
            $searchedLocale = substr($name, 0, -strlen($matches[0]));
            $translation = $this->getTranslationForLocale($searchedLocale);

            if (null !== $translation) {
                return $translation->{'get'.$matches[0]}();
            }

            return;
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): '.$name.
            ' in '.$trace[0]['file'].
            ' on line '.$trace[0]['line'],
            E_USER_NOTICE);

        return;
    }

    public function __set($name, $value)
    {
        $translationName = 'Translation';

        if (preg_match(sprintf('/%s$/', $translationName), $name)) {
            $this->addTranslation($value);

            return $this;
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __set(): '.$name.
            ' in '.$trace[0]['file'].
            ' on line '.$trace[0]['line'],
            E_USER_NOTICE);

        return;
    }

    /**
     * @param BadgeTranslation $translation
     *
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
     * @param BadgeTranslation $translation
     *
     * @return Badge
     */
    public function removeTranslation(BadgeTranslation $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
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
     *
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
     *
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
     *
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
     *
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
     *
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
     * @param bool $automaticAward
     *
     * @return Badge
     */
    public function setAutomaticAward($automaticAward)
    {
        $this->automaticAward = $automaticAward;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAutomaticAward()
    {
        return $this->automaticAward;
    }

    /**
     * @param \Icap\BadgeBundle\Entity\BadgeRule[] $badgeRules
     *
     * @return Badge
     */
    public function setRules($badgeRules)
    {
        foreach ($badgeRules as $rule) {
            $rule->setAssociatedBadge($this);
        }

        $this->badgeRules = $badgeRules;

        return $this;
    }

    /**
     * @return \Icap\BadgeBundle\Entity\BadgeRule[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getRules()
    {
        return $this->badgeRules;
    }

    /**
     * @param Workspace $workspace
     *
     * @return Badge
     */
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;

        return $this;
    }

    /**
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * @param bool $isExpiring
     *
     * @return Badge
     */
    public function setIsExpiring($isExpiring)
    {
        $this->isExpiring = $isExpiring;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsExpiring()
    {
        return $this->isExpiring;
    }

    /**
     * @return bool
     */
    public function isExpiring()
    {
        return $this->getIsExpiring();
    }

    /**
     * @param int $expireDuration
     *
     * @return Badge
     */
    public function setExpireDuration($expireDuration)
    {
        $this->expireDuration = $expireDuration;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpireDuration()
    {
        return $this->expireDuration;
    }

    /**
     * @param int $expirePeriod
     *
     * @return Badge
     */
    public function setExpirePeriod($expirePeriod)
    {
        $this->expirePeriod = $expirePeriod;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpirePeriod()
    {
        return $this->expirePeriod;
    }

    /**
     * @return string
     */
    public function getExpirePeriodLabel()
    {
        return self::getExpirePeriodTypeLabel($this->expirePeriod);
    }

    /**
     * @return array
     */
    public static function getExpirePeriodTypes()
    {
        return [self::EXPIRE_PERIOD_DAY,
                     self::EXPIRE_PERIOD_WEEK,
                     self::EXPIRE_PERIOD_MONTH,
                     self::EXPIRE_PERIOD_YEAR, ];
    }

    /**
     * @return array
     */
    public static function getExpirePeriodLabels()
    {
        return [self::EXPIRE_PERIOD_DAY_LABEL,
                     self::EXPIRE_PERIOD_WEEK_LABEL,
                     self::EXPIRE_PERIOD_MONTH_LABEL,
                     self::EXPIRE_PERIOD_YEAR_LABEL, ];
    }

    /**
     * @param int $expirePeriodType
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function getExpirePeriodTypeLabel($expirePeriodType)
    {
        $expirePeriodLabels = self::getExpirePeriodLabels();

        if (!isset($expirePeriodLabels[$expirePeriodType])) {
            throw new \InvalidArgumentException('Unknown expired period type.');
        }

        return $expirePeriodLabels[$expirePeriodType];
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
            $this->imagePath = null;
        }
        $this->file = $file;

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
    public function getWebPath()
    {
        if ($this->imagePath) {
            //legacy
          if (file_exists(self::getUploadDir().DIRECTORY_SEPARATOR.$this->imagePath)) {
              return self::getUploadDir().DIRECTORY_SEPARATOR.$this->imagePath;
            //new and much better (right ? :))
          } else {
              return $this->imagePath;
          }
        }
    }

    /**
     * @return string
     */
    public static function getUploadDir()
    {
        return sprintf('uploads%sbadges', DIRECTORY_SEPARATOR);
    }

    protected function dealWithAtLeastOneTranslation(ObjectManager $objectManager)
    {
        $translations = $this->getTranslations();
        /** @var \Icap\BadgeBundle\Entity\BadgeTranslation[] $emptyTranslations */
        $emptyTranslations = [];
        /** @var \Icap\BadgeBundle\Entity\BadgeTranslation[] $nonEmptyTranslations */
        $nonEmptyTranslations = [];

        foreach ($translations as $translation) {
            // Have to put all method call in variable because of empty doesn't
            // support result of method as parameter (prior to PHP 5.5)
            $name = $translation->getName();
            $description = $translation->getDescription();
            $criteria = $translation->getCriteria();
            if (empty($name) && empty($description) && empty($criteria)) {
                $emptyTranslations[] = $translation;
            } else {
                $nonEmptyTranslations[] = $translation;
            }
        }

        if (count($translations) === count($emptyTranslations)) {
            throw new \Exception('At least one translation must be defined on the badge');
        }

        $firstNonEmptyTranslation = $nonEmptyTranslations[0];
        foreach ($emptyTranslations as $emptyTranslation) {
            $emptyTranslation
                ->setName($firstNonEmptyTranslation->getName())
                ->setDescription($firstNonEmptyTranslation->getDescription())
                ->setCriteria($firstNonEmptyTranslation->getCriteria());
        }
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $this->dealWithAtLeastOneTranslation($event->getObjectManager());
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate(PreUpdateEventArgs $event)
    {
        $this->dealWithAtLeastOneTranslation($event->getObjectManager());
    }

    /**
     * @return array
     */
    public function getRestriction()
    {
        $restriction = [];
        if (null !== $this->getWorkspace()) {
            $restriction['workspace'] = $this->getWorkspace();
        }

        return $restriction;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;

            $newBagdeRules = new ArrayCollection();
            foreach ($this->badgeRules as $badgeRule) {
                $newBadgeRule = clone $badgeRule;
                $newBadgeRule->setAssociatedBadge($this);
                $newBagdeRules->add($newBadgeRule);
            }
            $this->badgeRules = $newBagdeRules;

            $newTranslations = new ArrayCollection();
            foreach ($this->translations as $translation) {
                $newTranslation = clone $translation;
                $newTranslation->setBadge($this);
                $newTranslations->add($newTranslation);
            }
            $this->translations = $newTranslations;

            $this->userBadges = new ArrayCollection();
            $this->badgeClaims = new ArrayCollection();
        }
    }

    public function __toString()
    {
        return 'badge'.$this->getId();
    }
}
